<?php

namespace App\Console\Commands;

use App\Models\ClientDetail;
use App\Models\Line;
use App\Models\NotificationLog;
use App\Models\WhatsappSetting;
use App\Services\EvolutionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendExpiryNotifications extends Command
{
    protected $signature = 'notifications:send-expiry';
    protected $description = 'Envia notificações de vencimento via WhatsApp para clientes oficiais (3 dias, 1 dia, hoje)';

    public function handle(): int
    {
        $nowTime = Carbon::now()->format('H:i');

        $settings = WhatsappSetting::where('notifications_enabled', true)
            ->where('connection_status', 'connected')
            ->get();

        if ($settings->isEmpty()) {
            $this->info('Nenhum revendedor com WhatsApp ativo e conectado.');
            return 0;
        }

        $evo = new EvolutionService();
        $todayStart = strtotime('today');
        $todayEnd = strtotime('tomorrow') - 1;
        $in1dStart = strtotime('+1 day 00:00:00');
        $in1dEnd = strtotime('+1 day 23:59:59');
        $in3dStart = strtotime('+3 days 00:00:00');
        $in3dEnd = strtotime('+3 days 23:59:59');

        $totalSent = 0;

        foreach ($settings as $setting) {
            $startTime = $setting->send_start_time ?? '09:00';

            if ($nowTime < $startTime) {
                $this->line("[{$setting->instance_name}] Ainda não é hora de enviar (início: {$startTime}, agora: {$nowTime}). Pulando.");
                continue;
            }

            $panelUser = $setting->panelUser;
            if (!$panelUser) continue;

            $memberId = $panelUser->xui_id;
            $intervalSeconds = $setting->send_interval_seconds ?? 30;

            $lines = Line::where('member_id', $memberId)
                ->where('is_trial', 0)
                ->where('admin_enabled', 1)
                ->where('enabled', 1)
                ->where('exp_date', '>=', $todayStart)
                ->where('exp_date', '<=', $in3dEnd)
                ->get();

            $this->info("[{$setting->instance_name}] Encontrados {$lines->count()} clientes para notificar (intervalo: {$intervalSeconds}s).");

            $today = Carbon::today()->toDateString();

            foreach ($lines as $line) {
                $expDate = (int) $line->exp_date;
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
                    ->where('xui_client_id', $line->id)
                    ->where('notification_type', $notifType)
                    ->where('sent_date', $today)
                    ->where('success', true)
                    ->exists();

                if ($alreadySent) continue;

                $detail = ClientDetail::where('xui_client_id', $line->id)->first();
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
                        'xui_client_id' => $line->id,
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
