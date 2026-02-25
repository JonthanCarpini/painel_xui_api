<?php

namespace App\Http\Controllers;

use App\Services\XuiApiService;
use Illuminate\Http\Request;
use App\Models\DnsServer;
use App\Models\TestChannel;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketExtra;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChannelTestController extends Controller
{
    public function __construct(private XuiApiService $api) {}
    public function index()
    {
        $categoriesByType = TestChannel::select('group_title', 'type')
            ->whereNotNull('group_title')
            ->distinct()
            ->orderBy('group_title')
            ->get()
            ->groupBy('type');

        return view('channel-test.index', [
            'categoriesByType' => $categoriesByType,
            'xuiIp' => $this->getXuiIp(),
            'xuiProxyBase' => $this->getXuiProxyBase(),
        ]);
    }

    public function getStreams(Request $request)
    {
        $category = $request->input('category');
        $type = $request->input('type', 'live'); // Default 'live'
        
        if (!$category) {
            return response()->json(['error' => 'Categoria não informada'], 400);
        }

        // Buscar canais do banco local filtrando por categoria e tipo
        $channels = TestChannel::where('group_title', $category)
            ->where('type', $type)
            ->select('id', 'name', 'logo_url', 'stream_url', 'stream_id')
            ->orderBy('name')
            ->get();

        if ($channels->isEmpty()) {
            return response()->json(['error' => 'Nenhum item encontrado nesta categoria'], 404);
        }

        $xuiProxyBase = $this->getXuiProxyBase();

        $result = $channels->map(function ($channel) use ($type, $xuiProxyBase) {
            return [
                'id' => $channel->id,
                'name' => $channel->name,
                'icon' => $this->proxyIcon($channel->logo_url),
                'stream_url' => $this->buildOpaqueStreamUrl($channel, $type, $xuiProxyBase),
                'stream_id' => $channel->stream_id,
            ];
        });

        return response()->json($result); 
    }

    public function getChannelDetails($id)
    {
        $channel = TestChannel::find($id);

        if (!$channel || !$channel->stream_id) {
            return response()->json(['error' => 'Canal não encontrado ou sem ID vinculado'], 404);
        }

        try {
            $streamId = (int) $channel->stream_id;

            // 1. Buscar em get_streams (lista com dados de runtime: pid, monitor_pid, stream_status, stream_started)
            $streamsResp = $this->api->getStreams();
            $streamData  = collect($streamsResp['data'] ?? [])
                ->first(fn($s) => (int)($s['id'] ?? 0) === $streamId);

            // Fallback: get_stream individual (retorna config, sem runtime completo)
            if (!$streamData) {
                $streamResp = $this->api->getStream($streamId);
                if (($streamResp['status'] ?? '') === 'STATUS_SUCCESS') {
                    $streamData = $streamResp['data'] ?? null;
                }
            }

            if (!$streamData) {
                return response()->json(['error' => 'Stream não encontrada via API XUI'], 404);
            }

            $serverId = $streamData['server_id'] ?? null;

            // 2. Status via dados de runtime
            $status = 'Offline';
            $uptime = 'Indisponível';
            $onDemand = (int)($streamData['on_demand'] ?? 0);

            $monitorPid    = (int)($streamData['monitor_pid'] ?? 0);
            $pid           = (int)($streamData['pid'] ?? 0);
            $streamStatus  = (int)($streamData['stream_status'] ?? -1);
            $streamStarted = (int)($streamData['stream_started'] ?? 0);

            if ($monitorPid > 0 && $pid > 0 && $streamStatus === 0) {
                $status = 'Online';
                if ($streamStarted > 0) {
                    $uptimeSec = time() - $streamStarted;
                    if ($uptimeSec > 0) {
                        $h = floor($uptimeSec / 3600);
                        $m = floor(($uptimeSec % 3600) / 60);
                        $s = $uptimeSec % 60;
                        $uptime = ($h > 0 ? "{$h}h " : '') . "{$m}m {$s}s";
                    }
                }
            } elseif ($monitorPid > 0 && $pid <= 0 && $streamStatus === 1) {
                $status = 'Com Erros';
            } elseif ($onDemand) {
                $status = 'Sob Demanda';
            }

            // 3. Audiência via live_connections — contar conexões no stream_id
            $connectionsResp = $this->api->getLiveConnections();
            $onlineClients   = collect($connectionsResp['data'] ?? [])
                ->filter(fn($c) => (int)($c['stream_id'] ?? 0) === $streamId)
                ->count();

            return response()->json([
                'status'      => $status,
                'uptime'      => $uptime,
                'on_demand'   => $onDemand,
                'clients'     => $onlineClients,
                'server_id'   => $serverId,
                'stream_name' => $streamData['stream_display_name'] ?? $streamData['name'] ?? '',
                'source'      => $streamData['stream_source'] ?? '',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar detalhes: ' . $e->getMessage()], 500);
        }
    }

    public function resolveStreamUrl(Request $request)
    {
        $channelId = $request->input('channel_id');
        $type = $request->input('type', 'live');

        $channel = TestChannel::find($channelId);
        if (!$channel || !$channel->stream_id) {
            return response()->json(['error' => 'Canal não encontrado'], 404);
        }

        try {
            $xuiIp = $this->getXuiIp();
            $creds = $this->getGhostCredentials();
            $streamId = $channel->stream_id;

            // Montar URL real do XUI com credenciais ghost
            $ext = 'm3u8';
            if (preg_match('/\.(\w+)(\?|$)/', $channel->stream_url ?? '', $m)) {
                $ext = $m[1];
            } elseif ($type === 'movie') {
                $ext = 'mp4';
            }

            if ($type === 'live') {
                $xuiUrl = "http://{$xuiIp}/{$creds['user']}/{$creds['pass']}/{$streamId}.{$ext}";
            } else {
                $xuiUrl = "http://{$xuiIp}/{$type}/{$creds['user']}/{$creds['pass']}/{$streamId}.{$ext}";
            }

            // Seguir redirects via GET e capturar effective URL (XUI redireciona para /auth/{token})
            // Nota: XUI só faz redirect no GET, HEAD retorna 200 direto
            $ch = curl_init($xuiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
            ]);
            curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
            curl_close($ch);

            $xuiProxyBase = $this->getXuiProxyBase();

            // Se houve redirect, extrair path da effective URL e montar URL no proxy
            if ($redirectCount > 0 && $effectiveUrl) {
                $path = parse_url($effectiveUrl, PHP_URL_PATH);
                if ($path) {
                    return response()->json([
                        'resolved_url' => $xuiProxyBase . $path,
                        'type' => $type,
                    ]);
                }
            }

            // Se não houve redirect, retornar URL do proxy padrão
            return response()->json([
                'resolved_url' => "{$xuiProxyBase}/stream/{$type}/{$streamId}.{$ext}",
                'type' => $type,
            ]);

        } catch (\Exception $e) {
            Log::error('resolveStreamUrl error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao resolver URL: ' . $e->getMessage()], 500);
        }
    }

    public function restartChannel($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }

        $channel = TestChannel::find($id);

        if (!$channel || !$channel->stream_id) {
            return response()->json(['error' => 'Canal inválido'], 404);
        }

        try {
            $streamId = (int) $channel->stream_id;

            // Parar o stream via API
            $this->api->stopStream($streamId);

            // Aguardar brevemente e iniciar novamente
            sleep(1);
            $startResult = $this->api->startStream($streamId);

            return response()->json(['result' => true, 'message' => 'Comando de reinicialização enviado com sucesso.']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao reiniciar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gera URL opaca sem credenciais do fantasma.
     * O Nginx proxy injeta as credenciais internamente.
     *
     * Formato XUI:
     *   Live:   http://IP/{user}/{pass}/{id}.m3u8       -> proxy: /stream/live/{id}.m3u8
     *   Movie:  http://IP/movie/{user}/{pass}/{id}.mp4  -> proxy: /stream/movie/{id}.mp4
     *   Series: http://IP/series/{user}/{pass}/{id}.mp4 -> proxy: /stream/series/{id}.mp4
     */
    private function buildOpaqueStreamUrl($channel, string $type, string $xuiProxyBase): string
    {
        $streamUrl = $channel->stream_url ?? '';

        // Extrair extensão da URL original
        $ext = 'm3u8'; // default para live
        if (preg_match('/\.(\w+)(\?|$)/', $streamUrl, $m)) {
            $ext = $m[1];
        } elseif ($type === 'movie') {
            $ext = 'mp4';
        }

        $streamId = $channel->stream_id;
        if (empty($streamId)) {
            // Fallback: usar URL original convertida
            return $this->ensureHttps($streamUrl);
        }

        return "{$xuiProxyBase}/stream/{$type}/{$streamId}.{$ext}";
    }

    private function proxyIcon(?string $url): ?string
    {
        if (empty($url)) return null;

        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        return route('img.proxy') . '?url=' . urlencode($url);
    }

    private function ensureHttps(?string $url): ?string
    {
        if (empty($url)) return $url;

        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        $serverIp = $this->getXuiIp();
        if (str_contains($url, $serverIp)) {
            $xuiBase = $this->getXuiProxyBase();
            return preg_replace('#https?://' . preg_quote($serverIp, '#') . '(:\d+)?#', $xuiBase, $url);
        }

        return $url;
    }

    private function getGhostCredentials(): array
    {
        $user = DB::table('app_settings')
            ->where('key', 'ghost_reseller_username')
            ->value('value');
        $pass = DB::table('app_settings')
            ->where('key', 'ghost_reseller_password')
            ->value('value');

        return [
            'user' => $user ?: 'fantasma',
            'pass' => $pass ?: 'fantasma123',
        ];
    }

    private function getXuiIp(): string
    {
        return env('XUI_DB_HOST', '109.205.178.143');
    }

    private function getXuiProxyBase(): string
    {
        static $cache = null;
        if ($cache === null) {
            // 1. Tentar DNS configurado pelo admin
            $dns = DnsServer::where('is_active', true)->first();
            if ($dns && !empty($dns->url)) {
                $cache = rtrim($dns->url, '/');
                if (!str_starts_with($cache, 'http')) {
                    $cache = 'https://' . $cache;
                }
                return $cache;
            }

            // 2. Usar subdomínio xui.{domain} (proxy Nginx com SSL via Traefik)
            $appUrl = config('app.url', '');
            $host = parse_url($appUrl, PHP_URL_HOST) ?: request()->getHost();
            $cache = 'https://xui.' . $host;
        }
        return $cache;
    }

    public function report(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
            'problem_description' => 'required|string',
        ]);

        $user = Auth::user();
        
        try {
            $ticketTitle = "Problema no canal: " . $request->channel_name;
            
            $content = $request->problem_description . "\n\n" .
                       "**Dados Técnicos do Relatório:**\n" .
                       "ID Stream (XUI): " . ($request->stream_id ?? 'N/A') . "\n" .
                       "URL Reprodução: " . ($request->stream_url ?? 'Não informada');

            $ticket = Ticket::create([
                'member_id'  => (int)$user->xui_id,
                'title'      => $ticketTitle,
                'status'     => Ticket::STATUS_OPEN,
                'admin_read' => false,
                'user_read'  => false,
            ]);

            TicketReply::create([
                'ticket_id'   => $ticket->id,
                'admin_reply' => false,
                'message'     => $content,
                'date'        => time(),
            ]);

            $reportCategory = TicketCategory::where('name', 'LIKE', '%report%')
                ->orWhere('name', 'LIKE', '%canal%')
                ->first();

            if ($reportCategory) {
                TicketExtra::create([
                    'ticket_id'   => $ticket->id,
                    'category_id' => $reportCategory->id,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Problema reportado com sucesso! Um ticket foi aberto.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao reportar problema: ' . $e->getMessage()], 500);
        }
    }
}
