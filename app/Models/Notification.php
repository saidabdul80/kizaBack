<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'message', 'is_read', 'type', 'links'];

    protected $casts = [
        'is_read' => 'boolean',
        'links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
