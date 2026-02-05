<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $connection = 'xui';
    protected $table = 'lines';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'username',
        'password',
        'last_ip',
        'exp_date',
        'admin_enabled',
        'enabled',
        'admin_notes',
        'reseller_notes',
        'bouquet',
        'allowed_outputs',
        'max_connections',
        'is_restreamer',
        'is_trial',
        'is_mag',
        'is_e2',
        'is_stalker',
        'is_isplock',
        'allowed_ips',
        'allowed_ua',
        'created_at',
        'pair_id',
        'force_server_id',
        'as_number',
        'isp_desc',
        'forced_country',
        'bypass_ua',
        'play_token',
        'last_expiration_video',
        'package_id',
        'access_token',
        'contact',
        'last_activity',
        'last_activity_array',
    ];

    protected $casts = [
        'member_id' => 'integer',
        'exp_date' => 'integer',
        'admin_enabled' => 'integer',
        'enabled' => 'integer',
        'max_connections' => 'integer',
        'is_restreamer' => 'boolean',
        'is_trial' => 'boolean',
        'is_mag' => 'boolean',
        'is_e2' => 'boolean',
        'is_stalker' => 'boolean',
        'is_isplock' => 'boolean',
        'created_at' => 'integer',
        'package_id' => 'integer',
        'last_activity' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(XuiUser::class, 'member_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function liveConnections()
    {
        return $this->hasMany(LineLive::class, 'user_id');
    }

    public function getBouquetIdsAttribute()
    {
        if (empty($this->bouquet)) {
            return [];
        }
        
        $decoded = json_decode($this->bouquet, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setBouquetIdsAttribute($value)
    {
        $this->attributes['bouquet'] = json_encode(array_values($value));
    }

    public function isActive(): bool
    {
        return $this->enabled == 1 && $this->exp_date > time();
    }

    public function isExpired(): bool
    {
        return $this->exp_date < time();
    }

    public function scopeActive($query)
    {
        return $query->where('enabled', 1)->where('exp_date', '>', time());
    }

    public function scopeExpired($query)
    {
        return $query->where('exp_date', '<', time());
    }

    public function scopeExpiringToday($query)
    {
        $startOfDay = strtotime('today');
        $endOfDay = strtotime('tomorrow') - 1;
        
        return $query->whereBetween('exp_date', [$startOfDay, $endOfDay]);
    }

    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeOfficial($query)
    {
        return $query->where('is_trial', 0);
    }

    public function scopeTrial($query)
    {
        return $query->where('is_trial', 1);
    }
}
