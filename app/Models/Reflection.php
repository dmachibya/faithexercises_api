<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reflection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'content',
        'media_url',
        'author',
        'reference',
        'scheduled_date',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];
}
