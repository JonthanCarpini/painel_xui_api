<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDetail extends Model
{
    protected $fillable = [
        'xui_client_id',
        'phone',
        'notes'
    ];
}
