<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientApplication extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'downloader_id',
        'direct_link',
        'compatible_devices',
        'activation_code',
        'login_instructions',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
