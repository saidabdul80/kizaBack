<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'amount', 'currency_id_from', 'currency_id_to',
        'recipient_id', 'recipients', 'type', 'rate', 'fees', 'status', 'method',
        'receipt_path', 'processed_by', 'reference', 'total_amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'rate' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'recipients' => 'array', // Cast as array for easy access
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function currencyFrom()
    {
        return $this->belongsTo(Currency::class, 'currency_id_from');
    }

    public function currencyTo()
    {
        return $this->belongsTo(Currency::class, 'currency_id_to');
    }

    public function recipient()
    {
        return $this->belongsTo(SavedRecipient::class, 'recipient_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getRecipientDetailsAttribute()
    {
        return $this->recipients ?? $this->recipient;
    }
}
