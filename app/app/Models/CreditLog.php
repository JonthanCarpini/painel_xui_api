<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    protected $connection = 'xui';
    protected $table = 'users_credits_logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'target_id',
        'admin_id',
        'amount',
        'date',
        'reason',
    ];

    protected $casts = [
        'target_id' => 'integer',
        'admin_id' => 'integer',
        'amount' => 'float',
        'date' => 'integer',
    ];

    public function target()
    {
        return $this->belongsTo(XuiUser::class, 'target_id');
    }

    public function admin()
    {
        return $this->belongsTo(XuiUser::class, 'admin_id');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('target_id', $userId)
              ->orWhere('admin_id', $userId);
        });
    }

    public function scopeReceived($query, $userId)
    {
        return $query->where('target_id', $userId)->where('amount', '>', 0);
    }

    public function scopeSent($query, $userId)
    {
        return $query->where('admin_id', $userId)->where('amount', '<', 0);
    }
}
