<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\XuiPlayerApiService;
use Carbon\Carbon;

class UpdatesController extends Controller
{
    public function __construct(private XuiPlayerApiService $playerApi) {}

    public function index()
    {
        $tmdbApiKey = AppSetting::get('tmdb_api_key');

        // Buscar últimos filmes via API pública (VOD streams)
        $rawMovies = $this->playerApi->getVodStreams();
        $movies = collect($rawMovies)
            ->sortByDesc(fn($m) => (int)($m['added'] ?? 0))
            ->take(60)
            ->values();

        // Buscar últimas séries via API pública
        $rawSeries = $this->playerApi->getSeries();
        $series = collect($rawSeries)
            ->sortByDesc(fn($s) => (int)($s['last_modified'] ?? 0))
            ->take(60)
            ->values();

        // Agrupar filmes por data de adição
        $moviesGrouped = $movies->map(function ($movie) {
            $added = (int)($movie['added'] ?? 0);
            $movie['added_at']   = $added ? Carbon::createFromTimestamp($added) : null;
            $movie['group_date'] = $added ? Carbon::createFromTimestamp($added)->format('d/m/Y') : 'Data Desconhecida';
            return $movie;
        })->groupBy('group_date');

        // Agrupar séries por data de modificação
        $seriesGrouped = $series->map(function ($serie) {
            $modified = (int)($serie['last_modified'] ?? 0);
            $serie['updated_at']  = $modified ? Carbon::createFromTimestamp($modified) : null;
            $serie['group_date']  = $modified ? Carbon::createFromTimestamp($modified)->format('d/m/Y') : 'Data Desconhecida';
            return $serie;
        })->groupBy('group_date');

        return view('updates.index', [
            'moviesGrouped' => $moviesGrouped,
            'seriesGrouped' => $seriesGrouped,
            'tmdbApiKey'    => $tmdbApiKey,
        ]);
    }
}
