<?php

namespace App\Services;

use App\Enums\Methods;
use App\Models\SavedRecipient;
use App\Models\Transaction;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TransactionService implements TransactionServiceInterface
{
    protected $strategies = [];

    public function __construct()
    {
        foreach (['store','received','completed'] as $type) {
            $this->strategies[$type] = App::make("App\\Services\\TransactionStrategies\\" . Str::studly($type) . "Transaction");
        }
    }

    public function handleTransaction($type, $data)
    {
        if (!isset($this->strategies[$type])) {
            throw new InvalidArgumentException("Invalid transaction type: $type");
        }

        return $this->strategies[$type]->process($data);
    }

    public function createTransaction(array $validated, bool $saveRecipient): Transaction
    {
        DB::beginTransaction();
        try {
            $recipientId = null;
            if ($saveRecipient) {
                $recipient = $this->handleRecipient($validated);
                $recipientId = $recipient->id;
            }

            $validated['recipient_id'] = $recipientId;
            $validated['recipients'] = $recipientId ? null : $validated['recipient'];
            $validated['reference'] = Util::generateReferenceCode();

            $transaction = Transaction::create($validated);
            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function handleRecipient(array $validated)
    {
        $recipientData = [
            'customer_id' => $validated['customer_id'],
            'method'      => $validated['method'],
        ];

        if ($validated['method'] === Methods::MOBILE_MONEY) {
            $recipientData += [
                'first_name'   => $validated['recipient']['first_name'],
                'last_name'    => $validated['recipient']['last_name'],
                'phone_number' => $validated['recipient']['phone_number'],
            ];
        } elseif ($validated['method'] === Methods::BANK_DEPOSIT) {
            $recipientData += [
                'bank_name'     => $validated['recipient']['bank_name'],
                'account_name'  => $validated['recipient']['account_name'],
                'account_number'=> $validated['recipient']['account_number'],
            ];
        } elseif ($validated['method'] === Methods::CASH_PICK_UP) {
            $recipientData += [
                'first_name' => $validated['recipient']['first_name'],
                'last_name'  => $validated['recipient']['last_name'],
                'email'      => $validated['recipient']['email'] ?? null,
                'phone_number' => $validated['recipient']['phone_number'],
            ];
        }

        $recipientData = array_filter($recipientData, fn($value) => !is_null($value));

        $conditions = [
            'customer_id' => $validated['customer_id'],
            'method'      => $validated['method'],
        ];

        if ($validated['method'] === Methods::MOBILE_MONEY || $validated['method'] === Methods::CASH_PICK_UP) {
            $conditions['phone_number'] = $validated['recipient']['phone_number'];
        } elseif ($validated['method'] === Methods::BANK_DEPOSIT) {
            $conditions['account_number'] = $validated['recipient']['account_number'];
        }

        return SavedRecipient::updateOrCreate($conditions, $recipientData);
    }
}


// $transactionService = new TransactionService();
// $result = $transactionService->handleTransaction('credit', ['amount' => 100]);
