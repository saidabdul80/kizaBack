<?php 
namespace App\ModelFilters;

use App\Enums\PaymentStatus;
use Carbon\Carbon;
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

    /**
     * Filter by today's transactions
     */
    public function today($value)
    {
        if ($value) {
            return $this->whereDate('created_at', Carbon::today());
        }
    }

    /**
     * Filter by this week's transactions
     */
    public function thisWeek($value)
    {
        if ($value) {
            return $this->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        }
    }

    /**
     * Filter by this month's transactions
     */
    public function thisMonth($value)
    {
        if ($value) {
            return $this->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ]);
        }
    }

    /**
     * Filter by last month's transactions
     */
    public function lastMonth($value)
    {
        if ($value) {
            return $this->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth()
            ]);
        }
    }

    /**
     * Filter by this year's transactions
     */
    public function thisYear($value)
    {
        if ($value) {
            return $this->whereBetween('created_at', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear()
            ]);
        }
    }

    /**
     * Filter by minimum amount
     */
    public function minAmount($amount)
    {
        if (!is_null($amount)) {
            return $this->where('amount', '>=', $amount);
        }
    }

    /**
     * Filter by maximum amount
     */
    public function maxAmount($amount)
    {
        if (!is_null($amount)) {
            return $this->where('amount', '<=', $amount);
        }
    }



}
