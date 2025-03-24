<?php

namespace App\Services\Contracts;

use App\Models\Transaction;

interface TransactionServiceInterface
{
    public function createTransaction(array $validated, bool $saveRecipient): Transaction;
    public function handleTransaction($type, Transaction $transaction);

}
