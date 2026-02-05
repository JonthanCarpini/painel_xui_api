<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DnsServer extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
