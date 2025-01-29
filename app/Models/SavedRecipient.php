<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedRecipient extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'name', 'phone', 'bank_account_details'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
