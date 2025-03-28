<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
class User  extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password'];

    protected $hidden = ['password'];

    public function processedTransactions()
    {
        return $this->hasMany(Transaction::class, 'processed_by');
    }


}
