<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainOrder extends Model
{
    protected $connection = 'mysql';
    protected $table = 'domain_orders';

    protected $fillable = [
        'order_ref',
        'reseller_id',
        'domain',
        'years',
        'price_usd',
        'price_brl',
        'exchange_rate',
        'status',
        'pix_qr_code_id',
        'pix_payload',
        'pix_encoded_image',
        'payment_id',
        'namecheap_order_id',
        'paid_at',
        'registered_at',
        'expires_at',
    ];

    protected $casts = [
        'price_usd' => 'float',
        'price_brl' => 'float',
        'exchange_rate' => 'float',
        'paid_at' => 'datetime',
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_REGISTERED = 'registered';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    public function scopeForReseller($query, int $resellerId)
    {
        return $query->where('reseller_id', $resellerId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }
}
