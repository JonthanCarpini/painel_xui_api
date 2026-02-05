<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bouquet extends Model
{
    protected $connection = 'xui';
    protected $table = 'bouquets';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'bouquet_name',
        'bouquet_channels',
        'bouquet_movies',
        'bouquet_radios',
        'bouquet_series',
        'bouquet_order',
    ];

    protected $casts = [
        'bouquet_order' => 'integer',
    ];

    public function getChannelIdsAttribute()
    {
        if (empty($this->attributes['bouquet_channels'])) {
            return [];
        }
        
        $decoded = json_decode($this->attributes['bouquet_channels'], true);
        return is_array($decoded) ? $decoded : [];
    }
}
