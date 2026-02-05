<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $connection = 'mysql'; // Banco do Painel (painel_plus)
    protected $table = 'user_preferences';

    protected $fillable = [
        'panel_user_id',
        'key',
        'value',
        'type',
    ];

    public function panelUser()
    {
        return $this->belongsTo(PanelUser::class, 'panel_user_id');
    }

    // Helper para converter valor baseado no tipo
    public function getValueAttribute($value)
    {
        if ($this->type === 'json') {
            return json_decode($value, true);
        } elseif ($this->type === 'boolean') {
            return (bool) $value;
        } elseif ($this->type === 'integer') {
            return (int) $value;
        }

        return $value;
    }

    // Helper para setar valor e tipo automaticamente
    public function setValueAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'json';
        } elseif (is_bool($value)) {
            $this->attributes['value'] = $value ? 1 : 0;
            $this->attributes['type'] = 'boolean';
        } elseif (is_int($value)) {
            $this->attributes['value'] = $value;
            $this->attributes['type'] = 'integer';
        } else {
            $this->attributes['value'] = $value;
            $this->attributes['type'] = 'string';
        }
    }
}
