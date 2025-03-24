<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class SavedRecipientFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter.
     *
     * @var array
     */
    public $relations = [];

    /**
     * Filter by customer_id.
     */
    public function customerId($customerId)
    {
        return $this->where('customer_id', $customerId);
    }

    /**
     * Filter by first name.
     */
    public function firstName($firstName)
    {
        return $this->where('first_name', 'LIKE', "%$firstName%");
    }

    /**
     * Filter by last name.
     */
    public function lastName($lastName)
    {
        return $this->where('last_name', 'LIKE', "%$lastName%");
    }

    /**
     * Filter by full name (first name and last name).
     */
    public function fullName($name)
    {
        return $this->where(function ($query) use ($name) {
            $query->where('first_name', 'LIKE', "%$name%")
                  ->orWhere('last_name', 'LIKE', "%$name%");
        });
    }

    /**
     * Filter by phone number.
     */
    public function phoneNumber($phoneNumber)
    {
        return $this->where('phone_number', 'LIKE', "%$phoneNumber%");
    }

    /**
     * Filter by email.
     */
    public function email($email)
    {
        return $this->where('email', 'LIKE', "%$email%");
    }

    /**
     * Filter by bank name.
     */
    public function bankName($bankName)
    {
        return $this->where('bank_name', 'LIKE', "%$bankName%");
    }

    /**
     * Filter by account name.
     */
    public function accountName($accountName)
    {
        return $this->where('account_name', 'LIKE', "%$accountName%");
    }

    /**
     * Filter by account number.
     */
    public function accountNumber($accountNumber)
    {
        return $this->where('account_number', $accountNumber);
    }

    public function search($value)
    {
        return $this->where(function ($query) use ($value) {
            $query->where('first_name', 'LIKE', "%$value%")
                  ->orWhere('last_name', 'LIKE', "%$value%")
                  ->orWhere('phone_number', 'LIKE', "%$value%")
                  ->orWhere('email', 'LIKE', "%$value%")
                  ->orWhere('bank_name', 'LIKE', "%$value%")
                  ->orWhere('account_name', 'LIKE', "%$value%")
                  ->orWhere('account_number', 'LIKE', "%$value%");
        });
    }
}
