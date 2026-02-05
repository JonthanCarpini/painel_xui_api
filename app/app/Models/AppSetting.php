<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $connection = 'mysql'; // Banco do Painel (painel_plus)
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    // Cache simples de configurações em memória durante a requisição
    protected static $cache = [];

    public static function get($key, $default = null)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            self::$cache[$key] = $setting->value;
            return $setting->value;
        }

        return $default;
    }

    public static function set($key, $value, $description = null)
    {
        $data = ['value' => $value];
        if ($description) {
            $data['description'] = $description;
        }

        self::updateOrCreate(['key' => $key], $data);
        self::$cache[$key] = $value;
    }
}
