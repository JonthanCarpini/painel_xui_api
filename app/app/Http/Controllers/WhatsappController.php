<?php

namespace App\Http\Controllers;

use App\Models\ClientDetail;
use App\Models\NotificationLog;
use App\Models\PanelUser;
use App\Models\WhatsappSetting;
use App\Services\EvolutionService;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function __construct(private XuiApiService $api) {}
    // ─── Página: Conexão ───────────────────────────────────────

    public function connection()
    {
        $setting = $this->getWhatsappSetting();

        return view('whatsapp.connection', compact('setting'));
    }

    public function createInstance()
    {
        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->firstOrFail();
        $instanceName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $xuiUser->username));

        $existing = WhatsappSetting::where('panel_user_id', $panelUser->id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Você já possui uma instância WhatsApp.'], 422);
        }

        $evo = new EvolutionService();
        $result = $evo->createInstance($instanceName);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar instância: ' . ($result['error'] ?? 'Desconhecido')], 500);
        }

        WhatsappSetting::create([
            'panel_user_id' => $panelUser->id,
            'instance_name' => $instanceName,
        ]);

        return response()->json(['success' => true, 'instance_name' => $instanceName]);
    }

    public function qrcode()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $result = $evo->getQrCode($setting->instance_name);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'] ?? 'Erro ao obter QR Code']);
        }

        $data = $result['data'] ?? [];

        return response()->json([
            'success' => true,
            'qrcode' => $data['base64'] ?? null,
            'code' => $data['code'] ?? null,
            'pairingCode' => $data['pairingCode'] ?? null,
        ]);
    }

    public function status()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'status' => 'none']);
        }

        $evo = new EvolutionService();
        $result = $evo->getInstanceStatus($setting->instance_name);

        $state = $result['data']['instance']['state'] ?? null;

        if (!$state && $result['success']) {
            $state = $result['data']['state'] ?? 'close';
        }
        if (!$state) {
            $state = 'close';
        }

        $isConnected = $state === 'open';

        if ($isConnected && $setting->connection_status !== 'connected') {
            $setting->update(['connection_status' => 'connected', 'connected_at' => now()]);
        } elseif (!$isConnected && $setting->connection_status === 'connected') {
            $setting->update(['connection_status' => 'disconnected', 'connected_at' => null]);
        }

        return response()->json([
            'success' => true,
            'status' => $isConnected ? 'connected' : $state,
            'instance_name' => $setting->instance_name,
        ]);
    }

    public function confirmScan()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $evo->restartInstance($setting->instance_name);
        sleep(3);

        $result = $evo->getInstanceStatus($setting->instance_name);
        $state = $result['data']['instance']['state'] ?? 'close';

        if ($state === 'open') {
            $setting->update(['connection_status' => 'connected', 'connected_at' => now()]);
            return response()->json(['success' => true, 'status' => 'connected']);
        }

        return response()->json([
            'success' => true,
            'status' => $state,
            'message' => 'Instância reiniciada. Aguarde alguns segundos...',
        ]);
    }

    public function disconnect()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $evo->logout($setting->instance_name);
        $setting->update(['connection_status' => 'disconnected', 'connected_at' => null]);

        return response()->json(['success' => true]);
    }

    public function deleteInstance()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $evo->deleteInstance($setting->instance_name);
        $setting->delete();

        return response()->json(['success' => true]);
    }

    // ─── Página: Configurações ─────────────────────────────────

    public function settings()
    {
        $setting = $this->getWhatsappSetting();

        if (!$setting) {
            return redirect()->route('whatsapp.connection')
                ->with('warning', 'Conecte seu WhatsApp primeiro antes de configurar notificações.');
        }

        return view('whatsapp.settings', compact('setting'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'notifications_enabled' => 'boolean',
            'expiry_message_3d' => 'nullable|string|max:1000',
            'expiry_message_1d' => 'nullable|string|max:1000',
            'expiry_message_today' => 'nullable|string|max:1000',
            'send_start_time' => 'required|date_format:H:i',
            'send_interval_seconds' => 'required|integer|min:5|max:300',
        ]);

        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return redirect()->route('whatsapp.connection');
        }

        $setting->update([
            'notifications_enabled' => $request->boolean('notifications_enabled'),
            'expiry_message_3d' => $request->input('expiry_message_3d'),
            'expiry_message_1d' => $request->input('expiry_message_1d'),
            'expiry_message_today' => $request->input('expiry_message_today'),
            'send_start_time' => $request->input('send_start_time'),
            'send_interval_seconds' => $request->input('send_interval_seconds'),
        ]);

        return redirect()->route('whatsapp.settings')->with('success', 'Configurações atualizadas!');
    }

    // ─── Página: Notificações ──────────────────────────────────

    public function notifications()
    {
        $setting = $this->getWhatsappSetting();

        if (!$setting) {
            return redirect()->route('whatsapp.connection')
                ->with('warning', 'Conecte seu WhatsApp primeiro.');
        }

        $panelUser = $setting->panelUser;
        if (!$panelUser) {
            return redirect()->route('whatsapp.connection');
        }

        $memberId        = $panelUser->xui_id;
        $intervalSeconds = $setting->send_interval_seconds ?? 30;

        $tz          = 'America/Sao_Paulo';
        $todayCarbon = Carbon::today($tz);
        $todayStart  = $todayCarbon->timestamp;
        $todayEnd    = $todayCarbon->copy()->endOfDay()->timestamp;
        $in1dStart   = $todayCarbon->copy()->addDay()->startOfDay()->timestamp;
        $in1dEnd     = $todayCarbon->copy()->addDay()->endOfDay()->timestamp;
        $in3dStart   = $todayCarbon->copy()->addDays(3)->startOfDay()->timestamp;
        $in3dEnd     = $todayCarbon->copy()->addDays(3)->endOfDay()->timestamp;

        // Buscar linhas via API e filtrar em memória
        $linesResp = $this->api->getLines();
        $allLines  = $linesResp['data'] ?? [];

        $lines = array_filter($allLines, function ($l) use ($memberId, $todayStart, $in3dEnd) {
            if ((int)($l['member_id'] ?? 0) !== (int)$memberId) return false;
            if ((int)($l['is_trial'] ?? 0) !== 0) return false;
            if ((int)($l['admin_enabled'] ?? 1) !== 1) return false;
            if ((int)($l['enabled'] ?? 1) !== 1) return false;
            $exp = (int)($l['exp_date'] ?? 0);
            return $exp >= $todayStart && $exp <= $in3dEnd;
        });

        $today = $todayCarbon->toDateString();

        $groups = [
            'today' => ['label' => 'Vence Hoje',      'color' => 'red',    'icon' => 'bi-exclamation-triangle-fill', 'clients' => []],
            '1d'    => ['label' => 'Vence Amanhã',    'color' => 'yellow', 'icon' => 'bi-clock-fill',                'clients' => []],
            '3d'    => ['label' => 'Vence em 3 Dias', 'color' => 'blue',   'icon' => 'bi-calendar-event',            'clients' => []],
        ];

        $sentLogs = NotificationLog::where('whatsapp_setting_id', $setting->id)
            ->where('sent_date', $today)
            ->get()
            ->keyBy(fn($log) => $log->xui_client_id . '_' . $log->notification_type);

        // Pré-carregar detalhes locais
        $lineIds      = array_column(array_values($lines), 'id');
        $localDetails = ClientDetail::whereIn('xui_client_id', $lineIds)->get()->keyBy('xui_client_id');

        $queuePosition = 0;

        foreach ($lines as $line) {
            $expDate   = (int)($line['exp_date'] ?? 0);
            $lineId    = (int)($line['id'] ?? 0);
            $notifType = null;

            if ($expDate >= $todayStart && $expDate <= $todayEnd) {
                $notifType = 'today';
            } elseif ($expDate >= $in1dStart && $expDate <= $in1dEnd) {
                $notifType = '1d';
            } elseif ($expDate >= $in3dStart && $expDate <= $in3dEnd) {
                $notifType = '3d';
            }

            if (!$notifType) continue;

            $detail     = $localDetails->get($lineId);
            $phone      = $detail ? $detail->phone : null;
            $phoneClean = $phone ? preg_replace('/\D/', '', $phone) : null;

            $logKey = $lineId . '_' . $notifType;
            $log    = $sentLogs->get($logKey);

            if ($log && $log->success) {
                $status = 'sent'; $statusLabel = 'Enviado'; $sentAt = $log->created_at;
            } elseif ($log && !$log->success) {
                $status = 'failed'; $statusLabel = 'Falhou'; $sentAt = $log->created_at;
            } elseif (!$phoneClean || strlen($phoneClean) < 10) {
                $status = 'no_phone'; $statusLabel = 'Sem Telefone'; $sentAt = null;
            } else {
                $status = 'pending'; $statusLabel = 'Aguardando';
                $queuePosition++;
                $sentAt = Carbon::now('America/Sao_Paulo')->addSeconds($queuePosition * $intervalSeconds);
            }

            $groups[$notifType]['clients'][] = [
                'username'     => $line['username'] ?? '',
                'phone'        => $phoneClean,
                'status'       => $status,
                'status_label' => $statusLabel,
                'sent_at'      => $sentAt,
                'exp_date'     => date('d/m/Y', $expDate),
            ];
        }

        return view('whatsapp.notifications', compact('setting', 'groups', 'today'));
    }

    // ─── Helper ────────────────────────────────────────────────

    private function getWhatsappSetting(): ?WhatsappSetting
    {
        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->first();
        if (!$panelUser) return null;

        return WhatsappSetting::where('panel_user_id', $panelUser->id)->first();
    }
}
