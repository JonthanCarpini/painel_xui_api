<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdatesController extends Controller
{
    public function index()
    {
        // Buscar API Key do TMDB nas configurações
        $settings = DB::connection('xui')->table('settings')->where('id', 1)->first();
        $tmdbApiKey = $settings->tmdb_api_key ?? null;

        // Buscar últimos filmes adicionados
        $movies = DB::connection('xui')->table('streams')
            ->select('id', 'stream_display_name', 'stream_icon', 'added', 'rating', 'year', 'tmdb_id')
            ->where('type', 2) // 2 = VOD
            ->orderBy('added', 'desc')
            ->limit(60)
            ->get();

        // Buscar últimas séries atualizadas/adicionadas
        $series = DB::connection('xui')->table('streams_series')
            ->select('id', 'title', 'cover', 'last_modified', 'rating', 'release_date', 'tmdb_id')
            ->orderBy('last_modified', 'desc')
            ->limit(60)
            ->get();

        // Processar e agrupar filmes por data
        $moviesGrouped = $movies->map(function($movie) {
            $movie->added_at = $movie->added ? Carbon::createFromTimestamp($movie->added) : null;
            $movie->group_date = $movie->added_at ? $movie->added_at->format('d/m/Y') : 'Data Desconhecida';
            return $movie;
        })->groupBy('group_date');

        // Processar e agrupar séries por data
        $seriesGrouped = $series->map(function($serie) {
            $serie->updated_at = $serie->last_modified ? Carbon::createFromTimestamp($serie->last_modified) : null;
            $serie->group_date = $serie->updated_at ? $serie->updated_at->format('d/m/Y') : 'Data Desconhecida';
            return $serie;
        })->groupBy('group_date');

        return view('updates.index', [
            'moviesGrouped' => $moviesGrouped,
            'seriesGrouped' => $seriesGrouped,
            'tmdbApiKey' => $tmdbApiKey
        ]);
    }
}
