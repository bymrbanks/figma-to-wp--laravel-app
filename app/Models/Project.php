<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'variables', // JSON column
        'patterns', // JSON column
        'templates', // JSON column
        'elements', // JSON column
        'parts', // JSON column
        'user_id', // Assuming you want to make this fillable as well
        'cover',
        'images'
    ];

    protected $casts = [
        'variables' => 'array',
        'patterns' => 'array',
        'templates' => 'array',
        'elements' => 'array',
        'parts' => 'array',
        'images' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function apiKey()
    {
        return $this->hasOne(ProjectApiKey::class);
    }

    // Add any additional methods or relationships here
}
