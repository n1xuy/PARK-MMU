<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'is_active'];

    // Add this scope method
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}