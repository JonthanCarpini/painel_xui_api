<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VodRequest;
use App\Services\TmdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $query = VodRequest::query()->with('user');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'completed', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $stats = [
            'pending' => VodRequest::pending()->count(),
            'completed' => VodRequest::completed()->count(),
            'rejected' => VodRequest::rejected()->count(),
            'total' => VodRequest::count(),
        ];

        return view('admin.vod-requests.index', compact('requests', 'stats', 'status', 'type', 'search'));
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
