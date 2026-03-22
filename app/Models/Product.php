<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
        'images',
        'genre'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public const STATUSES = [
        'Draft',
        'Downloaded',
        'In Progress',
        'Complete',
        'Sample Ordered',
        'Ready for Catalog',
        'Active',
        'Discontinued'
    ];
}