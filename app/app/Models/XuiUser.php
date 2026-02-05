<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class XuiUser extends Authenticatable
{
    protected $connection = 'xui';
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'email',
        'ip',
        'date_registered',
        'last_login',
        'member_group_id',
        'credits',
        'notes',
        'status',
        'reseller_dns',
        'owner_id',
        'override_packages',
        'hue',
        'theme',
        'timezone',
        'api_key',
    ];

    protected $casts = [
        'credits' => 'float',
        'status' => 'integer',
        'member_group_id' => 'integer',
        'owner_id' => 'integer',
        'date_registered' => 'integer',
        'last_login' => 'integer',
    ];

    protected $hidden = [
        'password',
        'api_key',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function isAdmin(): bool
    {
        return $this->member_group_id == 1;
    }

    public function isReseller(): bool
    {
        return $this->member_group_id == 2;
    }

    public function getCredits(): float
    {
        return (float) $this->credits;
    }

    public function lines()
    {
        return $this->hasMany(Line::class, 'member_id');
    }

    public function subResellers()
    {
        return $this->hasMany(XuiUser::class, 'owner_id');
    }

    public function owner()
    {
        return $this->belongsTo(XuiUser::class, 'owner_id');
    }

    public function creditLogs()
    {
        return $this->hasMany(CreditLog::class, 'target_id');
    }

    public function creditLogsSent()
    {
        return $this->hasMany(CreditLog::class, 'admin_id');
    }

    public function getAllSubResellerIds(): array
    {
        $ids = [$this->id];
        
        $subResellers = $this->subResellers()->get();
        
        foreach ($subResellers as $sub) {
            $ids = array_merge($ids, $sub->getAllSubResellerIds());
        }
        
        return array_unique($ids);
    }
}
