<?php

namespace App\Services\TransactionStrategies;

use App\Enums\PaymentStatus;
use App\Models\Transaction;

class ReceivedTransaction
{
    public function process( Transaction $transaction)
    {
        $transaction->status = PaymentStatus::RECIEVED;
        $transaction->save();
    }

}