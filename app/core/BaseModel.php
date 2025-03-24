<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

abstract class BaseModel extends Model
{
    protected static array $secured = [];

    public function setAttribute($key, $value)
    {
        if (in_array($key, static::$secured) && !is_null($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, static::$secured) && !is_null($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception) {
                return $value; // Return encrypted value if decryption fails
            }
        }

        return $value;
    }
}
