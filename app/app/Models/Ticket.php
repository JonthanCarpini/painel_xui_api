<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // protected $connection = 'xui'; // Removido para evitar conexão direta
    protected $table = 'tickets';
    public $timestamps = false; // Tabela não tem created_at/updated_at padrão Laravel

    protected $fillable = [
        'member_id',
        'title',
        'status',
        'admin_read',
        'user_read',
    ];

    // Status constants (suposição baseada em padrões comuns, ajustável)
    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 0;
    const STATUS_ANSWERED = 2; // Exemplo

    public function user()
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id', 'id');
    }

    public function extra()
    {
        return $this->hasOne(TicketExtra::class, 'ticket_id', 'id');
    }

    public function getCategoryAttribute()
    {
        return $this->extra ? $this->extra->category : null;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            1 => 'Aberto',
            0 => 'Fechado',
            2 => 'Respondido',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            1 => 'green',
            0 => 'gray',
            2 => 'blue',
            default => 'gray',
        };
    }
}
