<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGateway extends Model
{
    protected $connection = 'mysql';
    protected $table = 'payment_gateways';

    protected $fillable = [
        'reseller_id',
        'provider',
        'credentials',
        'active',
        'webhook_secret',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    const PROVIDER_ASAAS = 'asaas';
    const PROVIDER_MERCADOPAGO = 'mercadopago';
    const PROVIDER_FASTDEPIX = 'fastdepix';

    const PROVIDERS = [
        self::PROVIDER_ASAAS => 'Asaas',
        self::PROVIDER_MERCADOPAGO => 'Mercado Pago',
        self::PROVIDER_FASTDEPIX => 'FastDePix',
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

    public function scopeForReseller($query, int $resellerId)
    {
        return $query->where('reseller_id', $resellerId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
}
