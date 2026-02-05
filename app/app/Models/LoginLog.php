<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $connection = 'xui';
    protected $table = 'login_logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'access_code',
        'user_id',
        'status',
        'login_ip',
        'date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'date' => 'integer',
        'access_code' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(XuiUser::class, 'user_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'Success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', '!=', 'Success');
    }
}
