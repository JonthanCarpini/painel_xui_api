<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VodRequest extends Model
{
    protected $connection = 'mysql';
    protected $table = 'vod_requests';

    protected $fillable = [
        'user_id',
        'type',
        'tmdb_id',
        'title',
        'original_title',
        'poster_path',
        'backdrop_path',
        'overview',
        'release_date',
        'vote_average',
        'status',
        'admin_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'tmdb_id' => 'integer',
        'user_id' => 'integer',
        'vote_average' => 'float',
        'resolved_by' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getPosterUrlAttribute(): string
    {
        if ($this->poster_path) {
            return 'https://image.tmdb.org/t/p/w500' . $this->poster_path;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->title) . '&background=random&size=300';
    }

    public function getBackdropUrlAttribute(): string
    {
        if ($this->backdrop_path) {
            return 'https://image.tmdb.org/t/p/w1280' . $this->backdrop_path;
        }
        return '';
    }
}
