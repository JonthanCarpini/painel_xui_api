<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type', // info, warning, danger, success
        'color', // Admin defined color (hex or class)
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function reads()
    {
        return $this->hasMany(NoticeRead::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
