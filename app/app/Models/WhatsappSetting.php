<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    protected $connection = 'mysql';
    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'panel_user_id',
        'instance_name',
        'notifications_enabled',
        'expiry_message_3d',
        'expiry_message_1d',
        'expiry_message_today',
        'connection_status',
        'connected_at',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function panelUser()
    {
        return $this->belongsTo(PanelUser::class, 'panel_user_id');
    }

    public function getDefaultMessage3d(): string
    {
        return "Olá {cliente}! 👋\n\nSua assinatura vence em *3 dias* ({vencimento}).\n\nRenove agora para não ficar sem acesso!\n\nQualquer dúvida, estamos à disposição. 😊";
    }

    public function getDefaultMessage1d(): string
    {
        return "Olá {cliente}! ⚠️\n\nSua assinatura vence *amanhã* ({vencimento}).\n\nRenove agora para continuar aproveitando!\n\nEstamos à disposição. 🙏";
    }

    public function getDefaultMessageToday(): string
    {
        return "Olá {cliente}! 🚨\n\nSua assinatura vence *hoje* ({vencimento}).\n\nRenove agora para não perder o acesso!\n\nEstamos aqui para ajudar. 💬";
    }

    public function getEffectiveMessage3d(): string
    {
        return $this->expiry_message_3d ?: $this->getDefaultMessage3d();
    }

    public function getEffectiveMessage1d(): string
    {
        return $this->expiry_message_1d ?: $this->getDefaultMessage1d();
    }

    public function getEffectiveMessageToday(): string
    {
        return $this->expiry_message_today ?: $this->getDefaultMessageToday();
    }

    public function parseMessage(string $template, array $vars): string
    {
        return str_replace(
            ['{cliente}', '{vencimento}', '{usuario}', '{senha}'],
            [$vars['cliente'] ?? '', $vars['vencimento'] ?? '', $vars['usuario'] ?? '', $vars['senha'] ?? ''],
            $template
        );
    }
}
