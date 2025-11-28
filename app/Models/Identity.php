<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'statement',
        'category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
