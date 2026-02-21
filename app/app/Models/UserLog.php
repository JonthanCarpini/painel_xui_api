<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    // protected $connection = 'xui';
    protected $table = 'user_logs';
    public $timestamps = false;

    protected $fillable = [
        'owner',
        'type',
        'action',
        'log_id',
        'package_id',
        'cost',
        'credits_after',
        'date',
        'deleted_info'
    ];

    protected $casts = [
        'owner' => 'integer',
        'log_id' => 'integer',
        'package_id' => 'integer',
        'cost' => 'integer', // Note: Database defines as int(16), so it might not support decimals perfectly if amount is float
        'credits_after' => 'integer',
        'date' => 'integer',
    ];
}
