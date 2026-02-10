<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VodRequest;
use App\Services\TmdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VodRequestController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $status = $request->input('status', 'pending');
        $type = $request->input('type');
        $search = $request->input('search');

        $query = VodRequest::query()
            ->select('tmdb_id', 'type', 'title', 'poster_path', 'release_date', 'vote_average')
            ->selectRaw('MIN(id) as id')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('MIN(created_at) as first_request_at')
            ->selectRaw('MAX(created_at) as last_request_at')
            ->selectRaw("MIN(CASE WHEN status = 'pending' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END) as status_priority")
            ->selectRaw("CASE MIN(CASE WHEN status = 'pending' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END) WHEN 0 THEN 'pending' WHEN 1 THEN 'completed' ELSE 'rejected' END as group_status")
            ->groupBy('tmdb_id', 'type', 'title', 'poster_path', 'release_date', 'vote_average');

        if ($status && $status !== 'all') {
            $statusMap = ['pending' => 0, 'completed' => 1, 'rejected' => 2];
            $statusVal = $statusMap[$status] ?? 0;
            $query->havingRaw("MIN(CASE WHEN status = 'pending' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END) = ?", [$statusVal]);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $requests = $query->orderBy('status_priority', 'asc')
            ->orderBy('last_request_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Buscar solicitantes para cada grupo
        foreach ($requests as $req) {
            $req->requesters = VodRequest::where('tmdb_id', $req->tmdb_id)
                ->where('type', $req->type)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $stats = [
            'pending' => VodRequest::pending()->count(),
            'completed' => VodRequest::completed()->count(),
            'rejected' => VodRequest::rejected()->count(),
            'total' => VodRequest::count(),
        ];

        // Stats agrupados (títulos únicos)
        $table = (new VodRequest)->getTable();
        $groupStats = [
            'pending' => (int) DB::selectOne("SELECT COUNT(DISTINCT tmdb_id, type) as c FROM {$table} WHERE status = 'pending'")->c,
            'completed' => (int) DB::selectOne("SELECT COUNT(DISTINCT tmdb_id, type) as c FROM {$table} WHERE status = 'completed'")->c,
            'rejected' => (int) DB::selectOne("SELECT COUNT(DISTINCT tmdb_id, type) as c FROM {$table} WHERE status = 'rejected'")->c,
            'total' => (int) DB::selectOne("SELECT COUNT(DISTINCT tmdb_id, type) as c FROM {$table}")->c,
        ];

        return view('admin.vod-requests.index', compact('requests', 'stats', 'groupStats', 'status', 'type', 'search'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $vodRequest = VodRequest::with('user')->findOrFail($id);

        $requestCount = VodRequest::where('tmdb_id', $vodRequest->tmdb_id)
            ->where('type', $vodRequest->type)
            ->count();

        $allRequesters = VodRequest::where('tmdb_id', $vodRequest->tmdb_id)
            ->where('type', $vodRequest->type)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $existsInXui = $this->tmdb->checkExistsInXui($vodRequest->tmdb_id, $vodRequest->type);
        $categoryName = null;
        $addedDate = null;

        if ($existsInXui) {
            $categoryName = $this->tmdb->getCategoryName($existsInXui->category_id ?? null);
            if ($vodRequest->type === 'movie' && $existsInXui->added) {
                $addedDate = Carbon::createFromTimestamp($existsInXui->added)->format('d/m/Y H:i');
            } elseif ($vodRequest->type === 'series' && $existsInXui->last_modified) {
                $addedDate = Carbon::createFromTimestamp($existsInXui->last_modified)->format('d/m/Y H:i');
            }
        }

        $tmdbDetails = null;
        if ($vodRequest->type === 'movie') {
            $tmdbDetails = $this->tmdb->getMovieDetails($vodRequest->tmdb_id);
        } else {
            $tmdbDetails = $this->tmdb->getSeriesDetails($vodRequest->tmdb_id);
        }

        return view('admin.vod-requests.show', [
            'vodRequest' => $vodRequest,
            'requestCount' => $requestCount,
            'allRequesters' => $allRequesters,
            'existsInXui' => $existsInXui,
            'categoryName' => $categoryName,
            'addedDate' => $addedDate,
            'tmdbDetails' => $tmdbDetails,
        ]);
    }

    public function checkXui(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $request->validate([
            'tmdb_id' => 'required|integer',
            'type' => 'required|in:movie,series',
        ]);

        $tmdbId = (int) $request->input('tmdb_id');
        $type = $request->input('type');

        try {
            $existing = $this->tmdb->checkExistsInXui($tmdbId, $type);
        } catch (\Exception $e) {
            Log::error('Admin VodRequest: checkXui failed', ['tmdb_id' => $tmdbId, 'error' => $e->getMessage()]);
            return response()->json(['exists' => false, 'error' => 'Erro ao consultar servidor: ' . $e->getMessage()]);
        }

        if ($existing) {
            $categoryName = 'Sem categoria';
            try {
                $categoryName = $this->tmdb->getCategoryName($existing->category_id ?? null);
            } catch (\Exception $e) {
                Log::warning('Admin VodRequest: getCategoryName failed', ['error' => $e->getMessage()]);
            }

            $addedDate = null;
            try {
                if ($type === 'movie' && !empty($existing->added)) {
                    $addedDate = Carbon::createFromTimestamp((int) $existing->added)->format('d/m/Y H:i');
                } elseif ($type === 'series' && !empty($existing->last_modified)) {
                    $addedDate = Carbon::createFromTimestamp((int) $existing->last_modified)->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                Log::warning('Admin VodRequest: date parse failed', ['error' => $e->getMessage()]);
            }

            $name = $type === 'movie' ? ($existing->stream_display_name ?? 'Sem nome') : ($existing->title ?? 'Sem nome');

            return response()->json([
                'exists' => true,
                'data' => [
                    'name' => $name,
                    'category' => $categoryName,
                    'added_date' => $addedDate,
                    'year' => $existing->year ?? null,
                    'rating' => $existing->rating ?? null,
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function checkSeasons(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

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
            Log::error('Admin VodRequest: checkSeasons failed', ['tmdb_id' => $tmdbId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao verificar temporadas: ' . $e->getMessage()], 500);
        }
    }

    public function resolve(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:completed,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $vodRequest = VodRequest::findOrFail($id);

        $newStatus = $request->input('status');
        $note = $request->input('admin_note');

        VodRequest::where('tmdb_id', $vodRequest->tmdb_id)
            ->where('type', $vodRequest->type)
            ->where('status', 'pending')
            ->update([
                'status' => $newStatus,
                'admin_note' => $note,
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
            ]);

        $label = $newStatus === 'completed' ? 'concluído' : 'recusado';

        return redirect()->route('settings.admin.vod-requests.index')
            ->with('success', "Pedido de \"{$vodRequest->title}\" marcado como {$label}.");
    }
}
