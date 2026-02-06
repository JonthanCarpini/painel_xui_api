<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketExtra extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['ticket_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }
    
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
