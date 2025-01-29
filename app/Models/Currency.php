<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'symbol'];

    public function exchangeRatesFrom()
    {
        return $this->hasMany(ExchangeRate::class, 'currency_id_from');
    }

    public function exchangeRatesTo()
    {
        return $this->hasMany(ExchangeRate::class, 'currency_id_to');
    }
}
