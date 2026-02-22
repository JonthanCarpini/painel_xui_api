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
                'icon' => $this->ensureHttps($channel->logo_url),
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

            // 1. Dados Básicos via API get_stream (individual)
            $streamResp = $this->api->getStream($streamId);
            $streamData = null;

            if (($streamResp['status'] ?? '') === 'STATUS_SUCCESS') {
                $streamData = $streamResp['data'] ?? null;
            }

            // Fallback: buscar em get_streams e filtrar por id
            if (!$streamData) {
                $streamsResp = $this->api->getStreams();
                $streamData  = collect($streamsResp['data'] ?? [])
                    ->first(fn($s) => (int)($s['id'] ?? 0) === $streamId);
            }

            if (!$streamData) {
                return response()->json(['error' => 'Stream não encontrada via API XUI'], 404);
            }

            $serverId = $streamData['server_id'] ?? null;

            // 2. Status real via mysql_query na tabela streams_servers
            $status = 'Offline';
            $uptime = 'Indisponível';
            $onDemand = 0;

            $ssResp = $this->api->runQuery(
                "SELECT monitor_pid, pid, stream_status, on_demand, stream_started, "
                . "UNIX_TIMESTAMP() - stream_started AS uptime_seconds "
                . "FROM streams_servers WHERE stream_id = {$streamId} LIMIT 1"
            );
            $ssData = $ssResp['data'][0] ?? null;

            if ($ssData) {
                $monitorPid   = (int)($ssData['monitor_pid'] ?? 0);
                $pid          = (int)($ssData['pid'] ?? 0);
                $streamStatus = (int)($ssData['stream_status'] ?? -1);
                $onDemand     = (int)($ssData['on_demand'] ?? 0);

                if ($monitorPid > 0 && $pid > 0 && $streamStatus === 0) {
                    $status = 'Online';
                    $uptimeSec = (int)($ssData['uptime_seconds'] ?? 0);
                    if ($uptimeSec > 0) {
                        $h = floor($uptimeSec / 3600);
                        $m = floor(($uptimeSec % 3600) / 60);
                        $uptime = ($h > 0 ? "{$h}h " : '') . "{$m}m";
                    }
                } elseif ($monitorPid > 0 && $pid <= 0 && $streamStatus === 1) {
                    $status = 'Com Erros';
                } elseif ($onDemand) {
                    $status = 'Sob Demanda';
                }
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
     * Ex: https://xui.domain/stream/live/6.m3u8
     * O Nginx proxy injeta as credenciais internamente.
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
            // 1. Criar Ticket no XUI via API
            $ticketTitle = "Problema no canal: " . $request->channel_name;
            
            // Montar conteúdo do ticket com detalhes
            $content = $request->problem_description . "\n\n" .
                       "**Dados Técnicos do Relatório:**\n" .
                       "ID Stream (XUI): " . ($request->stream_id ?? 'N/A') . "\n" .
                       "URL Reprodução: " . ($request->stream_url ?? 'Não informada');

            // Enviar para API
            $this->api->createTicket($ticketTitle, $content, (int)$user->xui_id);

            return response()->json(['success' => true, 'message' => 'Problema reportado com sucesso! Um ticket foi aberto.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao reportar problema: ' . $e->getMessage()], 500);
        }
    }
}
