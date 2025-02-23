<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends  Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'phone_number', 'email', 'bank_account_details',
        'picture_url', 'nin_slip_url', 'international_passport_url', 'utility_bills_url',
        'drivers_license_url', 'permanent_residence_card_url', 'proof_of_address_url',
        'email_otp', 'email_verified_at', 'phone_number_otp', 'phone_number_verified_at','password'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_number_verified_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function savedRecipients()
    {
        return $this->hasMany(SavedRecipient::class);
    }
}
