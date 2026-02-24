<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.themoviedb.org/3';
    protected string $language = 'pt-BR';

    public function __construct(
        protected XuiPlayerApiService $playerApi,
        protected XuiApiService $xuiApi
    ) {
        $this->apiKey = AppSetting::get('tmdb_api_key');
    }

    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    public function searchMovies(string $query, int $page = 1): array
    {
        return $this->search('movie', $query, $page);
    }

    public function searchSeries(string $query, int $page = 1): array
    {
        return $this->search('tv', $query, $page);
    }

    public function getMovieDetails(int $tmdbId): ?array
    {
        return $this->getDetails('movie', $tmdbId);
    }

    public function getSeriesDetails(int $tmdbId): ?array
    {
        return $this->getDetails('tv', $tmdbId);
    }

    protected function search(string $type, string $query, int $page): array
    {
        if (!$this->hasApiKey()) {
            return ['results' => [], 'total_results' => 0, 'error' => 'API Key do TMDB não configurada.'];
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/search/{$type}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
                'query' => $query,
                'page' => $page,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('TmdbService: search failed', ['type' => $type, 'query' => $query, 'status' => $response->status()]);
            return ['results' => [], 'total_results' => 0, 'error' => 'Erro na busca TMDB (HTTP ' . $response->status() . ')'];
        } catch (\Exception $e) {
            Log::error('TmdbService: search exception', ['error' => $e->getMessage()]);
            return ['results' => [], 'total_results' => 0, 'error' => 'Erro de conexão com TMDB.'];
        }
    }

    protected function getDetails(string $type, int $tmdbId): ?array
    {
        if (!$this->hasApiKey()) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$type}/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
                'append_to_response' => 'credits',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('TmdbService: details exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function checkExistsInXui(int $tmdbId, string $type, string $title = ''): ?array
    {
        if ($type === 'movie') {
            $resp = $this->xuiApi->getMovies();
            $movies = $resp['data'] ?? [];
            foreach ($movies as $movie) {
                if ((int)($movie['tmdb_id'] ?? 0) === $tmdbId) {
                    return [
                        'id'          => $movie['id'],
                        'name'        => $movie['stream_display_name'] ?? $movie['name'] ?? '',
                        'tmdb_id'     => $movie['tmdb_id'],
                        'category_id' => $movie['category_id'] ?? null,
                        'stream_icon' => $movie['stream_icon'] ?? '',
                        'added'       => $movie['added'] ?? '',
                        'year'        => $movie['year'] ?? '',
                        'rating'      => $movie['rating'] ?? '',
                    ];
                }
            }
            return null;
        }

        $resp = $this->xuiApi->getSeriesList();
        $seriesList = $resp['data'] ?? [];
        foreach ($seriesList as $series) {
            if ((int)($series['tmdb_id'] ?? 0) === $tmdbId) {
                return [
                    'id'          => $series['id'],
                    'name'        => $series['title'] ?? $series['name'] ?? '',
                    'tmdb_id'     => $series['tmdb_id'],
                    'category_id' => $series['category_id'] ?? null,
                    'cover'       => $series['cover'] ?? '',
                    'last_modified' => $series['last_modified'] ?? '',
                    'year'        => $series['year'] ?? '',
                    'rating'      => $series['rating'] ?? '',
                ];
            }
        }
        return null;
    }

    public function getSeriesSeasons(int $tmdbId): ?array
    {
        if (!$this->hasApiKey()) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/tv/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => $this->language,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $seasons = collect($data['seasons'] ?? [])->filter(function ($s) {
                    return ($s['season_number'] ?? 0) > 0;
                })->map(function ($s) {
                    return [
                        'season_number' => $s['season_number'],
                        'name' => $s['name'] ?? "Temporada {$s['season_number']}",
                        'episode_count' => $s['episode_count'] ?? 0,
                        'air_date' => $s['air_date'] ?? null,
                        'poster_path' => $s['poster_path'] ?? null,
                    ];
                })->values()->toArray();

                return [
                    'title' => $data['name'] ?? '',
                    'total_seasons' => $data['number_of_seasons'] ?? 0,
                    'seasons' => $seasons,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('TmdbService: getSeriesSeasons exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function checkSeriesSeasonsInXui(int $tmdbId): array
    {
        $resp = $this->xuiApi->getSeriesList();
        $seriesList = $resp['data'] ?? [];

        $seriesId = null;
        foreach ($seriesList as $s) {
            if ((int)($s['tmdb_id'] ?? 0) === $tmdbId) {
                $seriesId = (int)$s['id'];
                break;
            }
        }

        if (!$seriesId) {
            return [];
        }

        $epResp = $this->xuiApi->getEpisodes($seriesId);
        $episodes = $epResp['data'] ?? [];

        $seasons = [];
        foreach ($episodes as $ep) {
            $sn = (int)($ep['season_num'] ?? 0);
            if ($sn > 0 && !in_array($sn, $seasons)) {
                $seasons[] = $sn;
            }
        }

        sort($seasons);
        return $seasons;
    }

    public function getCategoryName(?string $categoryIdJson): string
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

        $allCategories = $this->xuiApi->getCategories();
        $names = [];

        foreach ($allCategories as $cat) {
            if (in_array((int)($cat['id'] ?? 0), $ids)) {
                $names[] = $cat['category_name'] ?? $cat['name'] ?? '';
            }
        }

        $names = array_filter($names);
        return !empty($names) ? implode(', ', $names) : 'Sem categoria';
    }
}
