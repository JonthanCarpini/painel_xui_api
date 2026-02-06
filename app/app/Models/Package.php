<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $connection = 'xui';
    protected $table = 'users_packages';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'package_name',
        'is_addon',
        'bouquets',
        'official_duration',
        'official_credits',
        'trial_duration',
        'trial_credits',
        'can_gen_mag',
        'can_gen_e2',
        'output_formats',
        'is_official',
        'is_trial',
        'is_mag',
        'is_e2',
        'force_server_id',
        'lock_device',
        'max_registered_devices',
        'allowed_ips',
        'allowed_ua',
    ];

    protected $casts = [
        'is_addon' => 'boolean',
        'official_duration' => 'integer',
        'official_credits' => 'float',
        'trial_duration' => 'integer',
        'trial_credits' => 'float',
        'can_gen_mag' => 'boolean',
        'can_gen_e2' => 'boolean',
        'is_official' => 'boolean',
        'is_trial' => 'boolean',
        'is_mag' => 'boolean',
        'is_e2' => 'boolean',
        'force_server_id' => 'integer',
        'lock_device' => 'boolean',
        'max_registered_devices' => 'integer',
        'bouquets' => 'array',
    ];

    public function lines()
    {
        return $this->hasMany(Line::class, 'package_id');
    }

    public function getBouquetIdsAttribute()
    {
        if (empty($this->attributes['bouquets'])) {
            return [];
        }
        
        $decoded = json_decode($this->attributes['bouquets'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_official', 1);
    }
}
