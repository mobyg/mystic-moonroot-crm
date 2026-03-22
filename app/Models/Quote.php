<?php
// app/Models/Quote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote',
        'author',
        'category'
    ];

    public static function getDailyQuote()
    {
        $dayOfYear = date('z') + 1; // 1-366
        $totalQuotes = static::count();
        
        if ($totalQuotes === 0) {
            return null;
        }
        
        $index = ($dayOfYear - 1) % $totalQuotes;
        return static::skip($index)->first();
    }
}