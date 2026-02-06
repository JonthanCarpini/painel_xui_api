<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['name', 'responsible', 'phone'];

    public function extras()
    {
        return $this->hasMany(TicketExtra::class, 'category_id');
    }
}
