<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'amount', 'currency_id', 'type', 'rate', 'fees', 'status',
        'receipt_path', 'processed_by', 'reference'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'rate' => 'decimal:4',
        'total_amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
