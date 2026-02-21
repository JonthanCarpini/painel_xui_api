<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Acessa a API pública do XUI (player_api.php) usando as credenciais
 * da revenda fantasma configurada em AppSetting.
 *
 * Todos os dados são cacheados por 5 minutos para evitar requisições repetidas.
 */
class XuiPlayerApiService
{
    protected ?string $baseUrl;
    protected ?string $username;
    protected ?string $password;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('xui.base_url', env('XUI_BASE_URL', '')), '/');
        $this->username = AppSetting::get('ghost_reseller_username');
        $this->password = AppSetting::get('ghost_reseller_password');
    }

    // -------------------------------------------------------------------------
    // Verificação de credenciais
    // -------------------------------------------------------------------------

    public function hasCredentials(): bool
    {
        return !empty($this->baseUrl) && !empty($this->username) && !empty($this->password);
    }

    // -------------------------------------------------------------------------
    // Streams (VOD / Live / Series)
    // -------------------------------------------------------------------------

    public function getVodStreams(?int $categoryId = null): array
    {
        return $this->call('get_vod_streams', $categoryId ? ['category_id' => $categoryId] : []);
    }

    public function getLiveStreams(?int $categoryId = null): array
    {
        return $this->call('get_live_streams', $categoryId ? ['category_id' => $categoryId] : []);
    }

    public function getSeries(?int $categoryId = null): array
    {
        return $this->call('get_series', $categoryId ? ['category_id' => $categoryId] : []);
    }

    public function getVodInfo(int $vodId): array
    {
        return $this->call('get_vod_info', ['vod_id' => $vodId]);
    }

    public function getSeriesInfo(int $seriesId): array
    {
        return $this->call('get_series_info', ['series_id' => $seriesId]);
    }

    // -------------------------------------------------------------------------
    // Categorias
    // -------------------------------------------------------------------------

    public function getVodCategories(): array
    {
        return $this->call('get_vod_categories');
    }

    public function getLiveCategories(): array
    {
        return $this->call('get_live_categories');
    }

    public function getSeriesCategories(): array
    {
        return $this->call('get_series_categories');
    }

    // -------------------------------------------------------------------------
    // Helpers para TmdbService
    // -------------------------------------------------------------------------

    /**
     * Verifica se um filme (VOD) com determinado tmdb_id existe no XUI.
     * Retorna array com dados do stream ou null.
     */
    public function findMovieByTmdbId(int $tmdbId): ?array
    {
        $streams = $this->getVodStreams();
        foreach ($streams as $s) {
            if ((int)($s['tmdb_id'] ?? 0) === $tmdbId) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Verifica se uma série com determinado tmdb_id existe no XUI.
     * Retorna array com dados da série ou null.
     */
    public function findSeriesByTmdbId(int $tmdbId): ?array
    {
        $series = $this->getSeries();
        foreach ($series as $s) {
            if ((int)($s['tmdb_id'] ?? 0) === $tmdbId) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Retorna as temporadas disponíveis de uma série pelo series_id do XUI.
     * Usa get_series_info que retorna episodes agrupados por temporada.
     */
    public function getAvailableSeasons(int $seriesId): array
    {
        $info    = $this->getSeriesInfo($seriesId);
        $seasons = array_keys($info['episodes'] ?? []);
        return array_map('intval', $seasons);
    }

    /**
     * Resolve nome(s) de categoria pelo category_id (string JSON ou inteiro).
     */
    public function resolveCategoryName(?string $categoryIdJson): string
    {
        if (empty($categoryIdJson)) {
            return 'Sem categoria';
        }

        $ids = json_decode($categoryIdJson, true);
        if (!is_array($ids)) {
            $ids = [(int)$categoryIdJson];
        }
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return 'Sem categoria';
        }

        // Buscar categorias de VOD e Live e Series
        $allCategories = array_merge(
            $this->getVodCategories(),
            $this->getLiveCategories(),
            $this->getSeriesCategories()
        );

        $names = [];
        foreach ($allCategories as $cat) {
            if (in_array((int)($cat['category_id'] ?? 0), $ids)) {
                $names[] = $cat['category_name'] ?? '';
            }
        }

        return !empty($names) ? implode(', ', $names) : 'Sem categoria';
    }

    // -------------------------------------------------------------------------
    // Core HTTP
    // -------------------------------------------------------------------------

    protected function call(string $action, array $extra = []): array
    {
        if (!$this->hasCredentials()) {
            Log::warning('XuiPlayerApiService: credenciais da revenda fantasma não configuradas.');
            return [];
        }

        $cacheKey = "xui_player_{$action}_" . md5(json_encode($extra));

        return Cache::remember($cacheKey, 300, function () use ($action, $extra) {
            $params = array_merge([
                'username' => $this->username,
                'password' => $this->password,
                'action'   => $action,
            ], $extra);

            try {
                $response = Http::timeout(15)->get("{$this->baseUrl}/player_api.php", $params);

                if (!$response->successful()) {
                    Log::warning('XuiPlayerApiService: resposta não-OK', ['action' => $action, 'status' => $response->status()]);
                    return [];
                }

                $data = $response->json();

                // A API pública retorna array direto para listas
                return is_array($data) ? $data : [];

            } catch (\Exception $e) {
                Log::error('XuiPlayerApiService: exceção', ['action' => $action, 'error' => $e->getMessage()]);
                return [];
            }
        });
    }
}
