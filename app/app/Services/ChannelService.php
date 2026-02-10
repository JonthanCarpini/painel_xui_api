<?php

namespace App\Services;

use App\Models\DnsServer;
use App\Models\TestChannel;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChannelService
{
    /**
     * Baixa e processa a lista M3U do cliente fantasma
     */
    public function syncChannels($username, $password)
    {
        try {
            $serverIp = $this->getServerIp();
            $dnsBase = $this->getDnsBase();

            $url = "http://{$serverIp}:80/playlist/{$username}/{$password}/m3u_plus";
            
            $response = Http::timeout(120)->get($url); // Aumentei timeout para 120s
            
            if ($response->failed()) {
                return ['success' => false, 'message' => 'Não foi possível acessar a lista M3U. Verifique as credenciais ou a conexão.'];
            }

            $content = $response->body();
            $lines = explode("\n", $content);
            $channels = [];
            $currentChannel = [];

            // Limpar tabela atual
            TestChannel::truncate();

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (str_starts_with($line, '#EXTINF:')) {
                    // Extrair metadados
                    // Exemplo: #EXTINF:-1 tvg-id="" tvg-name="Nome" tvg-logo="url" group-title="Grupo",Nome Exibido
                    
                    // Group Title
                    preg_match('/group-title="([^"]*)"/', $line, $groupMatch);
                    $group = $groupMatch[1] ?? 'Sem Categoria';

                    // Logo
                    preg_match('/tvg-logo="([^"]*)"/', $line, $logoMatch);
                    $logo = $logoMatch[1] ?? null;

                    // Nome (tudo após a última vírgula)
                    $nameParts = explode(',', $line);
                    $name = end($nameParts);
                    $name = trim($name);

                    $currentChannel = [
                        'name' => $name,
                        'group_title' => $group,
                        'logo_url' => $logo,
                    ];
                } elseif (str_starts_with($line, 'http')) {
                    if (!empty($currentChannel)) {
                        // URL do Stream
                        $streamUrl = $line;
                        
                        // Converter para m3u8 para compatibilidade com web player
                        if (str_ends_with($streamUrl, '.ts')) {
                            $streamUrl = substr($streamUrl, 0, -3) . '.m3u8';
                        }
                        
                        // Extrair ID se possível
                        preg_match('/\/([0-9]+)\.(ts|m3u8|mp4|mkv|avi)$/', $streamUrl, $idMatch);
                        $streamId = $idMatch[1] ?? null;

                        // Detectar tipo baseado na URL
                        $type = 'live';
                        if (preg_match('#/movie/#i', $streamUrl)) {
                            $type = 'movie';
                        } elseif (preg_match('#/series/#i', $streamUrl)) {
                            $type = 'series';
                        }

                        // Converter URLs para usar DNS com HTTPS
                        if ($dnsBase) {
                            $streamUrl = $this->convertToDns($streamUrl, $serverIp, $dnsBase);
                            if (!empty($currentChannel['logo_url'])) {
                                $currentChannel['logo_url'] = $this->convertToDns($currentChannel['logo_url'], $serverIp, $dnsBase);
                            }
                        }

                        $currentChannel['type'] = $type;
                        $currentChannel['stream_url'] = $streamUrl;
                        $currentChannel['stream_id'] = $streamId;
                        $currentChannel['created_at'] = now();
                        $currentChannel['updated_at'] = now();

                        $channels[] = $currentChannel;
                        $currentChannel = [];

                        // Salvar em lotes de 500
                        if (count($channels) >= 500) {
                            TestChannel::insert($channels);
                            $channels = [];
                        }
                    }
                }
            }

            // Salvar restantes
            if (!empty($channels)) {
                TestChannel::insert($channels);
            }

            AppSetting::set('ghost_reseller_last_sync', now()->format('d/m/Y H:i:s'));

            return ['success' => true, 'count' => TestChannel::count()];

        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar canais: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getServerIp(): string
    {
        return '109.205.178.143';
    }

    private function getDnsBase(): ?string
    {
        $dns = DnsServer::where('is_active', true)->first();
        if ($dns && !empty($dns->url)) {
            $url = rtrim($dns->url, '/');
            if (!str_starts_with($url, 'http')) {
                $url = 'https://' . $url;
            }
            return $url;
        }
        return null;
    }

    private function convertToDns(string $url, string $serverIp, string $dnsBase): string
    {
        // Substituir http://IP(:porta) pelo DNS base com HTTPS
        $url = preg_replace('#https?://' . preg_quote($serverIp, '#') . '(:\d+)?#', $dnsBase, $url);
        return $url;
    }
}
