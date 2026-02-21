<?php

namespace App\Console\Commands;

use App\Models\ClientDetail;
use App\Models\NotificationLog;
use App\Models\WhatsappSetting;
use App\Services\EvolutionService;
use App\Services\XuiApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpiryNotifications extends Command
{
    protected $signature = 'notifications:send-expiry';
    protected $description = 'Envia notificações de vencimento via WhatsApp para clientes oficiais (3 dias, 1 dia, hoje)';

    public function handle(): int
    {
        $tz = 'America/Sao_Paulo';
        $now = Carbon::now($tz);
        $nowTime = $now->format('H:i');

        $this->info("Iniciando envio de notificações — {$now->format('d/m/Y H:i:s')} (Fuso: {$tz})");

        $settings = WhatsappSetting::where('notifications_enabled', true)
            ->where('connection_status', 'connected')
            ->get();

        if ($settings->isEmpty()) {
            $this->info('Nenhum revendedor com WhatsApp ativo e conectado.');
            return 0;
        }

        $evo = new EvolutionService();
        $api = app(XuiApiService::class);

        $todayCarbon = Carbon::today($tz);
        $todayStart = $todayCarbon->timestamp;
        $todayEnd = $todayCarbon->copy()->endOfDay()->timestamp;
        $in1dStart = $todayCarbon->copy()->addDay()->startOfDay()->timestamp;
        $in1dEnd = $todayCarbon->copy()->addDay()->endOfDay()->timestamp;
        $in3dStart = $todayCarbon->copy()->addDays(3)->startOfDay()->timestamp;
        $in3dEnd = $todayCarbon->copy()->addDays(3)->endOfDay()->timestamp;

        $totalSent = 0;

        // Buscar todas as linhas via API uma única vez (evita N chamadas)
        $allLinesResp = $api->getLines();
        $allLines = collect($allLinesResp['data'] ?? []);

        foreach ($settings as $setting) {
            $startTime = $setting->send_start_time ?? '09:00';

            if ($nowTime < $startTime) {
                $this->line("[{$setting->instance_name}] Ainda não é hora de enviar (início: {$startTime}, agora: {$nowTime}). Pulando.");
                continue;
            }

            $panelUser = $setting->panelUser;
            if (!$panelUser) continue;

            $memberId = (string) $panelUser->xui_id;
            $intervalSeconds = $setting->send_interval_seconds ?? 30;

            // Filtrar em memória — substitui Line::where() com DB direto
            $lines = $allLines->filter(function ($line) use ($memberId, $todayStart, $in3dEnd) {
                return (string)($line['member_id'] ?? '') === $memberId
                    && (int)($line['is_trial'] ?? 0) === 0
                    && (int)($line['admin_enabled'] ?? 1) === 1
                    && (int)($line['enabled'] ?? 1) === 1
                    && (int)($line['exp_date'] ?? 0) >= $todayStart
                    && (int)($line['exp_date'] ?? 0) <= $in3dEnd;
            })->map(fn($l) => (object) $l);

            $this->info("[{$setting->instance_name}] Encontrados {$lines->count()} clientes para notificar (intervalo: {$intervalSeconds}s).");

            $today = $todayCarbon->toDateString();

            foreach ($lines as $line) {
                $expDate = (int) $line->exp_date;
                $lineId  = (int) $line->id;
                $notifType = null;
                $template = null;

                if ($expDate >= $todayStart && $expDate <= $todayEnd) {
                    $notifType = 'today';
                    $template = $setting->getEffectiveMessageToday();
                } elseif ($expDate >= $in1dStart && $expDate <= $in1dEnd) {
                    $notifType = '1d';
                    $template = $setting->getEffectiveMessage1d();
                } elseif ($expDate >= $in3dStart && $expDate <= $in3dEnd) {
                    $notifType = '3d';
                    $template = $setting->getEffectiveMessage3d();
                }

                if (!$template || !$notifType) continue;

                $alreadySent = NotificationLog::where('whatsapp_setting_id', $setting->id)
                    ->where('xui_client_id', $lineId)
                    ->where('notification_type', $notifType)
                    ->where('sent_date', $today)
                    ->where('success', true)
                    ->exists();

                if ($alreadySent) continue;

                $detail = ClientDetail::where('xui_client_id', $lineId)->first();
                $phone = $detail->phone ?? null;

                if (!$phone) continue;

                $phone = preg_replace('/\D/', '', $phone);

                if (strlen($phone) < 10) continue;

                if (strlen($phone) <= 11) {
                    $phone = '55' . $phone;
                }

                $message = $setting->parseMessage($template, [
                    'cliente' => $line->username,
                    'vencimento' => date('d/m/Y', $expDate),
                    'usuario' => $line->username,
                    'senha' => $line->password,
                ]);

                $result = $evo->sendText($setting->instance_name, $phone, $message);

                NotificationLog::updateOrCreate(
                    [
                        'whatsapp_setting_id' => $setting->id,
                        'xui_client_id' => $lineId,
                        'notification_type' => $notifType,
                        'sent_date' => $today,
                    ],
                    ['success' => $result['success']]
                );

                if ($result['success']) {
                    $totalSent++;
                    $this->line("  ✓ Enviado para {$line->username} ({$phone})");
                } else {
                    Log::warning('Falha ao enviar notificação de vencimento', [
                        'instance' => $setting->instance_name,
                        'line' => $line->username,
                        'phone' => $phone,
                        'error' => $result['error'] ?? 'unknown',
                    ]);
                    $this->warn("  ✗ Falha para {$line->username}: " . ($result['error'] ?? 'Erro desconhecido'));
                }

                sleep($intervalSeconds);
            }
        }

        $this->info("Concluído. {$totalSent} notificações enviadas.");
        return 0;
    }
}
