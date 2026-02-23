<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerDomain extends Model
{
    protected $connection = 'mysql';
    protected $table = 'reseller_domains';

    protected $fillable = [
        'reseller_id',
        'domain',
        'type',
        'is_active',
        'namecheap_order_id',
        'paid_amount_brl',
        'years',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'paid_amount_brl' => 'float',
        'expires_at' => 'datetime',
    ];

    const TYPE_PURCHASED = 'purchased';
    const TYPE_CUSTOM = 'custom';

    public function scopeForReseller($query, int $resellerId)
    {
        return $query->where('reseller_id', $resellerId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePurchased($query)
    {
        return $query->where('type', self::TYPE_PURCHASED);
    }

    public function scopeCustom($query)
    {
        return $query->where('type', self::TYPE_CUSTOM);
    }
}
