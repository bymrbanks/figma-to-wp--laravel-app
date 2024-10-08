<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'schema',
        'fields',
        'author',
        'slug',
        'description',
        'image',    
    ];

    protected $casts = [
        'schema' => 'array',
        'fields' => 'array',
    ];
}