<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineLive extends Model
{
    // protected $connection = 'xui';
    protected $table = 'user_active';
    protected $primaryKey = 'activity_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'stream_id',
        'server_id',
        'proxy_id',
        'user_agent',
        'user_ip',
        'container',
        'date_start',
        'date_end',
        'geoip_country_code',
        'isp',
        'external_device',
        'divergence',
        'hmac_id',
        'hmac_identifier',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'stream_id' => 'integer',
        'server_id' => 'integer',
        'proxy_id' => 'integer',
        'date_start' => 'integer',
        'date_end' => 'integer',
        'divergence' => 'float',
    ];

    public function line()
    {
        return $this->belongsTo(Line::class, 'user_id');
    }

    public function scopeOnline($query)
    {
        return $query->whereNull('date_end');
    }
}
