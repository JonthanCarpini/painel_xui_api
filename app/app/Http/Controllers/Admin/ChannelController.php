<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChannelController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $search = $request->input('search');

        $streamsResp = $this->api->getStreams();
        $channels = collect($streamsResp['data'] ?? [])
            ->when($search, fn($c) => $c->filter(
                fn($s) => str_contains(strtolower($s['stream_display_name'] ?? ''), strtolower($search))
                       || str_contains(strtolower($s['stream_source'] ?? ''), strtolower($search))
            ))
            ->sortByDesc('id')
            ->map(fn($s) => (object) $s)
            ->values();

        $perPage  = 20;
        $page     = (int) $request->input('page', 1);
        $total    = $channels->count();
        $items    = $channels->slice(($page - 1) * $perPage, $perPage)->values();
        $channels = new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
        ]);

        return view('admin.channels.index', compact('channels'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Buscar stream via API get_streams
        $streamsResp = $this->api->getStreams();
        $channelData = collect($streamsResp['data'] ?? [])->firstWhere('stream_id', (string) $id);

        if (!$channelData) {
            return redirect()->route('settings.admin.channels.index')->with('error', 'Canal não encontrado.');
        }

        $channel      = (object) $channelData;
        $streamServer = null;

        // Categorias e servidores via API
        $categories = collect($this->api->getCategories());
        $servers    = collect($this->api->getServers())->map(fn($s) => (object) $s);

        return view('admin.channels.edit', compact('channel', 'streamServer', 'categories', 'servers'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source'       => 'required|string',
            'category_id'         => 'nullable',
        ]);

        try {
            // A API XUI não expõe edit_stream — reiniciar o stream é a ação mais próxima
            // Parar e iniciar para forçar reload da source
            $this->api->stopStream((int) $id);
            sleep(1);
            $this->api->startStream((int) $id);

            Log::info('ChannelController: stream reiniciado após update', ['stream_id' => $id]);

            return redirect()->route('settings.admin.channels.index')
                ->with('success', 'Comando de atualização enviado. O canal foi reiniciado.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar canal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            // Parar o stream antes de remover
            $this->api->stopStream((int) $id);

            Log::info('ChannelController: stream parado para remoção', ['stream_id' => $id]);

            return redirect()->route('settings.admin.channels.index')
                ->with('success', 'Stream parado. Remoção definitiva deve ser feita no painel XUI.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover canal: ' . $e->getMessage());
        }
    }

    public function restart($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            $this->api->stopStream((int) $id);
            sleep(1);
            $this->api->startStream((int) $id);

            return back()->with('success', 'Comando de reinício enviado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao reiniciar canal: ' . $e->getMessage());
        }
    }
}
