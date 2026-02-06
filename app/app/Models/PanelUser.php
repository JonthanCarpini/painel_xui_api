<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelUser extends Model
{
    protected $connection = 'mysql'; // Banco do Painel (painel_plus)
    protected $table = 'panel_users';

    protected $fillable = [
        'xui_id',
        'username',
        'group_id',
        'phone',
    ];

    public function preferences()
    {
        return $this->hasMany(UserPreference::class, 'panel_user_id');
    }

    /**
     * Obtém o valor de uma preferência.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getPreference($key, $default = null)
    {
        $pref = $this->preferences()->where('key', $key)->first();
        return $pref ? $pref->value : $default;
    }

    /**
     * Define o valor de uma preferência.
     *
     * @param string $key
     * @param mixed $value
     * @return UserPreference
     */
    public function setPreference($key, $value)
    {
        // Determinar tipo automaticamente se possível
        $type = 'string';
        if (is_array($value) || is_object($value)) $type = 'json';
        elseif (is_bool($value)) $type = 'boolean';
        elseif (is_int($value)) $type = 'integer';

        $pref = $this->preferences()->updateOrCreate(
            ['key' => $key],
            ['type' => $type] // Atualiza o tipo se mudar
        );
        
        // Usamos o mutator do UserPreference para setar o valor corretamente
        $pref->value = $value;
        $pref->save();

        return $pref;
    }
}
