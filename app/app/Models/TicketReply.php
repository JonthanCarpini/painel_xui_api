<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    // protected $connection = 'xui'; // Removido para evitar conexão direta
    protected $table = 'tickets_replies';
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'admin_reply', // 1 = Admin, 0 = User
        'message',
        'date',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
