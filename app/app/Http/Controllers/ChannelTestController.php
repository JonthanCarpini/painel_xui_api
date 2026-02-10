<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DnsServer;
use App\Models\TestChannel;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketExtra;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChannelTestController extends Controller
{
    public function index()
    {
        $categoriesByType = TestChannel::select('group_title', 'type')
            ->whereNotNull('group_title')
            ->distinct()
            ->orderBy('group_title')
            ->get()
            ->groupBy('type');

        $hasDns = DnsServer::where('is_active', true)->exists();
        
        return view('channel-test.index', [
            'categoriesByType' => $categoriesByType,
            'hasDns' => $hasDns,
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

        $hasDns = $this->getDnsBase() !== null;
        $proxyUrl = route('channel-test.proxy-image');

        $result = $channels->map(function ($channel) use ($hasDns, $proxyUrl) {
            $icon = $this->ensureHttps($channel->logo_url);
            $streamUrl = $this->ensureHttps($channel->stream_url);

            // Se não tem DNS e a imagem ainda é HTTP, usar proxy
            if (!$hasDns && $icon && str_starts_with($icon, 'http://')) {
                $icon = $proxyUrl . '?url=' . urlencode($icon);
            }

            return [
                'id' => $channel->id,
                'name' => $channel->name,
                'icon' => $icon,
                'stream_url' => $streamUrl,
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
            // 1. Dados Básicos (Tabela streams)
            $stream = DB::connection('xui')->table('streams')
                ->where('id', $channel->stream_id)
                ->select('stream_display_name', 'stream_source')
                ->first();

            if (!$stream) {
                return response()->json(['error' => 'Stream não encontrada no banco XUI'], 404);
            }

            // 2. Status Técnico (Tabela streams_servers)
            // Busca o servidor onde o canal está rodando (preferência para onde tem PID)
            $streamServer = DB::connection('xui')->table('streams_servers')
                ->where('stream_id', $channel->stream_id)
                ->orderBy('pid', 'desc') // Tenta pegar um com PID primeiro
                ->first();

            $status = 'Offline';
            $uptime = 'Indisponível';
            $bitrate = 0;
            $serverId = null;

            if ($streamServer) {
                $serverId = $streamServer->server_id;
                
                // Lógica de Status baseada no PID e stream_status
                if ($streamServer->pid > 0) {
                    $status = 'Online';
                    
                    // Calcular Uptime
                    if ($streamServer->stream_started) {
                        $now = time();
                        $started = $streamServer->stream_started;
                        $duration = $now - $started;
                        
                        // Formatar uptime
                        $dtF = new \DateTime('@0');
                        $dtT = new \DateTime("@$duration");
                        $uptime = $dtF->diff($dtT)->format('%a dias, %h horas, %i min');
                    }
                }
            }

            // 3. Audiência (Tabela lines_live)
            $onlineClients = DB::connection('xui')->table('lines_live')
                ->where('stream_id', $channel->stream_id)
                ->count();

            return response()->json([
                'status' => $status,
                'uptime' => $uptime,
                'on_demand' => 0, // Streams geralmente são Live
                'clients' => $onlineClients,
                'server_id' => $serverId,
                'stream_name' => $stream->stream_display_name,
                'source' => $stream->stream_source // Útil para debug/report
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
            return DB::connection('xui')->transaction(function () use ($channel) {
                $streamId = $channel->stream_id;
                
                // Buscar onde o canal está rodando
                $streamServer = DB::connection('xui')->table('streams_servers')
                    ->where('stream_id', $streamId)
                    ->orderBy('pid', 'desc') // Pega o ativo se houver
                    ->first();

                if (!$streamServer) {
                    return response()->json(['error' => 'Servidor do canal não encontrado.'], 404);
                }

                $serverId = $streamServer->server_id;

                // 1. Se o canal estiver rodando (tiver PID), mandar sinal de parada
                if ($streamServer->pid > 0) {
                    DB::connection('xui')->table('signals')->insert([
                        'pid'       => $streamServer->pid,
                        'server_id' => $serverId,
                        'time'      => time(),
                        'rtmp'      => 0
                    ]);
                }

                // 2. Resetar o status para "0" e marcar para análise imediata
                DB::connection('xui')->table('streams_servers')
                    ->where('stream_id', $streamId)
                    ->where('server_id', $serverId)
                    ->update([
                        'pid'           => null,
                        'stream_status' => 0, // 0 = Parado / Tentando Iniciar
                        'to_analyze'    => 1, // Sinaliza para o Watchdog analisar o canal
                        'stream_started' => null
                    ]);

                return response()->json(['result' => true, 'message' => 'Comando de reinicialização enviado com sucesso.']);
            });

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao reiniciar: ' . $e->getMessage()], 500);
        }
    }

    public function proxyImage(Request $request)
    {
        $url = $request->input('url');
        if (empty($url) || !str_starts_with($url, 'http://')) {
            abort(400);
        }

        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                $contentType = $response->header('Content-Type') ?: 'image/jpeg';
                return response($response->body(), 200)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=86400');
            }
        } catch (\Exception $e) {
            // silently fail
        }

        abort(404);
    }

    private function ensureHttps(?string $url): ?string
    {
        if (empty($url)) return $url;

        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        $serverIp = '109.205.178.143';
        if (str_contains($url, $serverIp)) {
            $dnsBase = $this->getDnsBase();

            if ($dnsBase) {
                return preg_replace('#https?://' . preg_quote($serverIp, '#') . '(:\d+)?#', $dnsBase, $url);
            }
        }

        return $url;
    }

    private function getDnsBase(): ?string
    {
        static $cache = null;
        static $loaded = false;
        if (!$loaded) {
            $dns = DnsServer::where('is_active', true)->first();
            if ($dns && !empty($dns->url)) {
                $cache = rtrim($dns->url, '/');
                if (!str_starts_with($cache, 'http')) {
                    $cache = 'https://' . $cache;
                }
            }
            $loaded = true;
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
            DB::beginTransaction();

            // Buscar dados reais da stream se possível
            $streamData = "Dados não encontrados no banco.";
            if ($request->stream_id) {
                $stream = DB::connection('xui')->table('streams')->where('id', $request->stream_id)->first();
                if ($stream) {
                    $streamData = "Nome XUI: {$stream->stream_display_name}\nSource: {$stream->stream_source}\nID: {$stream->id}";
                }
            }

            // 1. Buscar ou criar categoria "Reportes de Canais"
            $category = TicketCategory::firstOrCreate(
                ['name' => 'Reportes de Canais'],
                ['responsible' => 'Sistema', 'phone' => '']
            );

            // 2. Criar Ticket no XUI
            $ticketTitle = "Problema no canal: " . $request->channel_name;
            
            $ticket = Ticket::create([
                'member_id' => $user->id,
                'title' => substr($ticketTitle, 0, 255),
                'status' => 1, // Aberto
                'admin_read' => 0,
                'user_read' => 1,
            ]);

            // 3. Vincular Categoria (Local)
            TicketExtra::create([
                'ticket_id' => $ticket->id,
                'category_id' => $category->id,
            ]);

            // 4. Adicionar Mensagem
            $message = "O usuário relatou um problema no canal **{$request->channel_name}**.\n\n" .
                       "**Descrição do Problema:**\n" .
                       $request->problem_description . "\n\n" .
                       "**Dados Técnicos do Relatório:**\n" .
                       "ID Stream (XUI): " . ($request->stream_id ?? 'N/A') . "\n" .
                       "URL Reprodução: " . ($request->stream_url ?? 'Não informada') . "\n\n" .
                       "**Dados do Banco de Dados (Streams):**\n" .
                       $streamData;

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'admin_reply' => 0,
                'message' => $message,
                'date' => time(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Problema reportado com sucesso! Um ticket foi aberto.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao reportar problema: ' . $e->getMessage()], 500);
        }
    }
}
