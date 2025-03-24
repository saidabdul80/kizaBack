<?php

namespace App\Services\TransactionStrategies;

use App\Enums\PaymentStatus;
use App\Models\Transaction;

class CompletedTransaction
{

    public function process( Transaction $transaction)
    {
        $transaction->status = PaymentStatus::COMPLETED;
        $transaction->save();
    }

}