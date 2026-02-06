<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestChannel extends Model
{
    protected $fillable = [
        'name',
        'group_title',
        'type', // live, movie, series
        'logo_url',
        'stream_url',
        'stream_id'
    ];
}
