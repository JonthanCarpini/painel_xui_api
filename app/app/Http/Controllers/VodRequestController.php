<?php

namespace App\Http\Controllers;

use App\Models\VodRequest;
use App\Services\TmdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VodRequestController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $pendingRequests = VodRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedRequests = VodRequest::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('resolver')
            ->orderBy('resolved_at', 'desc')
            ->get();

        $rejectedRequests = VodRequest::where('user_id', $userId)
            ->where('status', 'rejected')
            ->with('resolver')
            ->orderBy('resolved_at', 'desc')
            ->get();

        return view('vod-requests.index', [
            'pendingRequests' => $pendingRequests,
            'completedRequests' => $completedRequests,
            'rejectedRequests' => $rejectedRequests,
            'hasTmdbKey' => $this->tmdb->hasApiKey(),
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'type' => 'required|in:movie,series',
            'query' => 'required|string|min:2|max:255',
        ]);

        if (!$this->tmdb->hasApiKey()) {
            return response()->json(['error' => 'API Key do TMDB não configurada. Contate o administrador.'], 422);
        }

        $type = $request->input('type');
        $query = $request->input('query');

        $results = $type === 'movie'
            ? $this->tmdb->searchMovies($query)
            : $this->tmdb->searchSeries($query);

        if (isset($results['error'])) {
            return response()->json(['error' => $results['error']], 422);
        }

        $items = collect($results['results'] ?? [])->take(20)->map(function ($item) use ($type) {
            return [
                'tmdb_id' => $item['id'],
                'title' => $type === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? ''),
                'original_title' => $type === 'movie' ? ($item['original_title'] ?? '') : ($item['original_name'] ?? ''),
                'poster_path' => $item['poster_path'] ?? null,
                'backdrop_path' => $item['backdrop_path'] ?? null,
                'overview' => $item['overview'] ?? '',
                'release_date' => $type === 'movie' ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? ''),
                'vote_average' => $item['vote_average'] ?? 0,
            ];
        });

        return response()->json(['results' => $items]);
    }

    public function checkExists(Request $request)
    {
        $request->validate([
            'tmdb_id' => 'required|integer',
            'type' => 'required|in:movie,series',
        ]);

        $tmdbId = (int) $request->input('tmdb_id');
        $type = $request->input('type');

        try {
            $existing = $this->tmdb->checkExistsInXui($tmdbId, $type);
        } catch (\Exception $e) {
            \Log::error('VodRequest: checkExistsInXui failed', ['tmdb_id' => $tmdbId, 'type' => $type, 'error' => $e->getMessage()]);
            $existing = null;
        }

        if ($existing) {
            $categoryName = 'Sem categoria';
            try {
                $categoryName = $this->tmdb->getCategoryName($existing->category_id ?? null);
            } catch (\Exception $e) {
                \Log::warning('VodRequest: getCategoryName failed', ['error' => $e->getMessage()]);
            }

            $addedDate = null;
            try {
                if ($type === 'movie' && !empty($existing->added)) {
                    $addedDate = Carbon::createFromTimestamp((int) $existing->added)->format('d/m/Y H:i');
                } elseif ($type === 'series' && !empty($existing->last_modified)) {
                    $addedDate = Carbon::createFromTimestamp((int) $existing->last_modified)->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                \Log::warning('VodRequest: date parse failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'exists' => true,
                'data' => [
                    'name' => $type === 'movie' ? ($existing->stream_display_name ?? 'Sem nome') : ($existing->title ?? 'Sem nome'),
                    'cover' => $type === 'movie' ? ($existing->stream_icon ?? null) : ($existing->cover ?? null),
                    'category' => $categoryName,
                    'added_date' => $addedDate,
                    'year' => $existing->year ?? null,
                    'rating' => $existing->rating ?? null,
                ],
            ]);
        }

        $alreadyRequested = VodRequest::where('tmdb_id', $tmdbId)
            ->where('type', $type)
            ->where('status', 'pending')
            ->exists();

        return response()->json([
            'exists' => false,
            'already_requested' => $alreadyRequested,
        ]);
    }

    public function checkSeasons(Request $request)
    {
        $request->validate([
            'tmdb_id' => 'required|integer',
        ]);

        $tmdbId = (int) $request->input('tmdb_id');

        try {
            $tmdbData = $this->tmdb->getSeriesSeasons($tmdbId);
            if (!$tmdbData) {
                return response()->json(['error' => 'Não foi possível buscar temporadas no TMDB.'], 422);
            }

            $xuiSeasons = $this->tmdb->checkSeriesSeasonsInXui($tmdbId);

            $seasons = collect($tmdbData['seasons'])->map(function ($s) use ($xuiSeasons) {
                $s['exists_in_xui'] = in_array($s['season_number'], $xuiSeasons);
                return $s;
            })->toArray();

            return response()->json([
                'title' => $tmdbData['title'],
                'total_seasons' => $tmdbData['total_seasons'],
                'seasons' => $seasons,
                'xui_seasons' => $xuiSeasons,
                'series_exists' => !empty($xuiSeasons),
            ]);
        } catch (\Exception $e) {
            \Log::error('VodRequest: checkSeasons failed', ['tmdb_id' => $tmdbId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao verificar temporadas: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:movie,series',
            'tmdb_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'poster_path' => 'nullable|string|max:255',
            'backdrop_path' => 'nullable|string|max:255',
            'overview' => 'nullable|string',
            'release_date' => 'nullable|string|max:20',
            'vote_average' => 'nullable|numeric',
            'season_number' => 'nullable|integer|min:1',
        ]);

        $tmdbId = (int) $request->input('tmdb_id');
        $type = $request->input('type');
        $seasonNumber = $request->input('season_number');

        if ($type === 'movie') {
            $existsInXui = $this->tmdb->checkExistsInXui($tmdbId, $type);
            if ($existsInXui) {
                return response()->json(['error' => 'Este título já existe no servidor.'], 422);
            }
        }

        $title = $request->input('title');
        if ($seasonNumber) {
            $title .= " - Temporada {$seasonNumber}";
        }

        VodRequest::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'tmdb_id' => $tmdbId,
            'title' => $title,
            'original_title' => $request->input('original_title'),
            'poster_path' => $request->input('poster_path'),
            'backdrop_path' => $request->input('backdrop_path'),
            'overview' => $request->input('overview'),
            'release_date' => $request->input('release_date'),
            'vote_average' => $request->input('vote_average', 0),
        ]);

        return response()->json(['success' => 'Pedido enviado com sucesso!']);
    }
}
