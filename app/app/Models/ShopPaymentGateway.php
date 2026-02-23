<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ShopPaymentGateway extends Model
{
    protected $connection = 'mysql';
    protected $table = 'shop_payment_gateways';

    protected $fillable = [
        'provider',
        'credentials',
        'active',
        'webhook_secret',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    const PROVIDERS = [
        'asaas' => 'Asaas',
        'mercadopago' => 'Mercado Pago',
        'fastdepix' => 'FastDePix',
    ];

    public static function getProviderLabel(string $provider): string
    {
        return self::PROVIDERS[$provider] ?? $provider;
    }

    public function setCredentialsAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->attributes['credentials'] = Crypt::encryptString($value);
    }

    public function getCredentialsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getCredential(string $key, $default = null)
    {
        $creds = $this->credentials;
        return $creds[$key] ?? $default;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public static function getActiveGateway(): ?self
    {
        return static::active()->first();
    }
}
