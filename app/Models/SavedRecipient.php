<?php
namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedRecipient extends Model
{
    use HasFactory, Filterable;

    protected $fillable = ['customer_id','method', 'first_name','last_name', 'phone_number','email', 'bank_name','account_name', 'account_number'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
