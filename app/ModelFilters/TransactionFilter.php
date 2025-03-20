<?php 
namespace App\ModelFilters;

use App\Enums\PaymentStatus;
use EloquentFilter\ModelFilter;

class TransactionFilter extends ModelFilter
{
    public function createdAt()
    {
        // Default ordering
        $this->orderBy('created_at', 'desc');
    }

    public function customer($id)
    {
        return $this->where('customer_id', $id);
    }

    public function status($status)
    {
        return $this->where('status', PaymentStatus::getKey($status));
    }

    public function type($type)
    {
        return $this->where('type', $type);
    }

    public function reference($reference)
    {
        return $this->where('reference', 'like', "%{$reference}%");
    }

    public function amountBetween($min, $max)
    {
        return $this->whereBetween('amount', [$min, $max]);
    }

    public function currencyFrom($currencyId)
    {
        return $this->where('currency_id_from', $currencyId);
    }

    public function currencyTo($currencyId)
    {
        return $this->where('currency_id_to', $currencyId);
    }

    public function dateRange($date)
    {
        return $this->whereBetween('created_at', $date);
    }

    public function search($query)
    {
        return $this->where(function ($q) use ($query) {
            $q->whereJsonContains('recipients->account_name', $query)
            ->orWhereJsonContains('recipients->phone_number', $query)
            ->orWhereJsonContains('recipients->email', $query)
            ->orWhereJsonContains('recipients->bank_name', $query)
            ->orWhereHas('savedRecipient', function ($subQuery) use ($query) {
                $subQuery->where('account_name', 'like', "%{$query}%")
                        ->orWhere('phone_number', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('bank_name', 'like', "%{$query}%");
            });
        });
    }


    public function recipientName($name)
    {
        return $this->where(function ($query) use ($name) {
            $query->whereJsonContains('recipients->account_name', $name)
                ->orWhereHas('savedRecipient', function ($q) use ($name) {
                    $q->where('account_name', $name);
                });
        });
    }

    public function recipientPhone($phone)
    {
        return $this->where(function ($query) use ($phone) {
            $query->whereJsonContains('recipients->phone_number', $phone)
                ->orWhereHas('savedRecipient', function ($q) use ($phone) {
                    $q->where('phone_number', $phone);
                });
        });
    }

    public function recipientEmail($email)
    {
        return $this->where(function ($query) use ($email) {
            $query->whereJsonContains('recipients->email', $email)
                ->orWhereHas('savedRecipient', function ($q) use ($email) {
                    $q->where('email', $email);
                });
        });
    }

    public function recipientBank($bank)
    {
        return $this->where(function ($query) use ($bank) {
            $query->whereJsonContains('recipients->bank_name', $bank)
                ->orWhereHas('savedRecipient', function ($q) use ($bank) {
                    $q->where('bank_name', $bank);
                });
        });
    }

}
