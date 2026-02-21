<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    // protected $connection = 'xui';
    protected $table = 'streaming_servers';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'server_type',
        'xui_version',
        'server_name',
        'domain_name',
        'server_ip',
        'private_ip',
        'is_main',
        'enabled',
        'parent_id',
        'http_broadcast_port',
        'https_broadcast_port',
        'http_ports_add',
        'https_ports_add',
        'total_clients',
        'network_interface',
        'status',
        'enable_geoip',
        'geoip_countries',
        'last_check_ago',
        'server_hardware',
        'total_services',
        'persistent_connections',
        'rtmp_port',
        'geoip_type',
        'isp_names',
    ];

    protected $casts = [
        'server_type' => 'integer',
        'is_main' => 'integer',
        'enabled' => 'integer',
        'http_broadcast_port' => 'integer',
        'https_broadcast_port' => 'integer',
        'total_clients' => 'integer',
        'status' => 'integer',
        'enable_geoip' => 'integer',
        'last_check_ago' => 'integer',
        'total_services' => 'integer',
        'persistent_connections' => 'boolean',
        'rtmp_port' => 'integer',
    ];

    public function scopeLoadBalancers($query)
    {
        return $query->whereIn('server_type', [0, 1]);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', 1);
    }

    public function isOnline(): bool
    {
        return $this->status == 1;
    }
}
