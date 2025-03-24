<?php
namespace App\Events;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered
{
    use Dispatchable, SerializesModels;

    public $customer;
    public $type;

    public function __construct(Customer $customer, $type = null)
    {
        $this->customer = $customer;
        $this->type = $type;
    }
}
