<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $connection = 'mysql'; // Banco do Painel (painel_plus)
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'message',
        'type',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
                     ->where(function($q) use ($now) {
                         $q->whereNull('starts_at')
                           ->orWhere('starts_at', '<=', $now);
                     })
                     ->where(function($q) use ($now) {
                         $q->whereNull('ends_at')
                           ->orWhere('ends_at', '>=', $now);
                     });
    }
}
