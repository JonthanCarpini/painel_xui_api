<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use App\Models\TicketExtra;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $categoriesStats = [];
        $selectedCategory = null;
        $tickets = [];
        $uncategorizedCount = 0;

        // Buscar tickets via API
        $response = $this->api->getTickets(['limit' => 1000]);
        $allTickets = $response['data'] ?? [];

        // Filtrar tickets localmente (já que a API não filtra por tudo)
        $filteredTickets = [];
        
        if ($user->isAdmin()) {
            // Admin vê tudo
            $filteredTickets = $allTickets;
        } else {
            // Revendedor vê apenas seus tickets
            $filteredTickets = array_filter($allTickets, function ($t) use ($user) {
                return (int)($t['member_id'] ?? 0) === (int)$user->xui_id;
            });
        }

        // Ordenar por status (abertos primeiro) e ID desc
        usort($filteredTickets, function ($a, $b) {
            $statusA = (int)($a['status'] ?? 0);
            $statusB = (int)($b['status'] ?? 0);
            
            // Status 0 (Fechado) deve ir pro final
            if ($statusA === 0 && $statusB !== 0) return 1;
            if ($statusA !== 0 && $statusB === 0) return -1;
            if ($statusA !== $statusB) return $statusA <=> $statusB;
            
            return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
        });

        // Paginação manual
        $page = $request->get('page', 1);
        $perPage = 20;
        $total = count($filteredTickets);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filteredTickets, $offset, $perPage);
        
        $tickets = new LengthAwarePaginator(
            $items, 
            $total, 
            $perPage, 
            $page, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Stats de categorias (apenas Admin)
        if ($user->isAdmin()) {
            $categories = TicketCategory::all();
            
            // Buscar IDs de tickets ativos
            $activeTicketIds = array_map(function($t) { return $t['id']; }, array_filter($allTickets, function($t) {
                return (int)($t['status'] ?? 0) !== 0;
            }));

            if (!empty($activeTicketIds)) {
                $extrasCount = TicketExtra::whereIn('ticket_id', $activeTicketIds)
                    ->selectRaw('category_id, count(*) as total')
                    ->groupBy('category_id')
                    ->pluck('total', 'category_id');
            } else {
                $extrasCount = [];
            }

            foreach ($categories as $cat) {
                $categoriesStats[$cat->id] = [
                    'name' => $cat->name,
                    'count' => $extrasCount[$cat->id] ?? 0,
                    'active' => $request->query('category') == $cat->id
                ];
            }
            
            // Contar sem categoria
            $categorizedTicketIds = TicketExtra::pluck('ticket_id')->toArray();
            $uncategorizedCount = count(array_filter($allTickets, function($t) use ($categorizedTicketIds) {
                return (int)($t['status'] ?? 0) !== 0 && !in_array($t['id'], $categorizedTicketIds);
            }));
        }

        return view('tickets.index', compact('tickets', 'categoriesStats', 'uncategorizedCount', 'selectedCategory'));
    }

    public function create()
    {
        $categories = TicketCategory::all();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:ticket_categories,id',
            'message' => 'required|string',
        ]);

        $user = Auth::user();

        // Criar Ticket via API
        $response = $this->api->createTicket($request->title, $request->message, (int)$user->xui_id);
        
        if (($response['status'] ?? '') === 'STATUS_SUCCESS' && isset($response['data']['id'])) {
            $ticketId = $response['data']['id'];
            
            // Criar Vínculo de Categoria (Banco Local)
            TicketExtra::create([
                'ticket_id' => $ticketId,
                'category_id' => $request->category_id,
            ]);

            return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao criar ticket na API.');
    }

    public function show($id)
    {
        $user = Auth::user();

        $response = $this->api->getTicket((int)$id);

        if (($response['status'] ?? '') !== 'STATUS_SUCCESS' || !isset($response['data'])) {
            return redirect()->route('tickets.index')->with('error', 'Ticket não encontrado.');
        }

        $ticketData = $response['data'];

        // Converter replies (arrays) para collection de objetos
        $replies = collect($ticketData['replies'] ?? [])->map(fn($r) => (object)$r);
        unset($ticketData['replies']);

        $ticket = (object)$ticketData;
        $ticket->replies = $replies;

        // Buscar categoria do banco local via TicketExtra
        $extra = TicketExtra::where('ticket_id', (int)$id)->first();
        $ticket->category = $extra ? TicketCategory::find($extra->category_id) : null;

        // Validar permissão
        if (!$user->isAdmin() && (int)$ticket->member_id !== (int)$user->xui_id) {
            abort(403);
        }

        // Marcar como lido no XUI
        if ($user->isAdmin()) {
            $this->api->runQuery("UPDATE tickets SET admin_read = 1 WHERE id = {$id}");
        } else {
            $this->api->runQuery("UPDATE tickets SET user_read = 1 WHERE id = {$id}");
        }

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $isAdminReply = $user->isAdmin() ? 1 : 0;

        $response = $this->api->replyTicket((int)$id, $request->message, $isAdminReply);

        if (($response['status'] ?? '') === 'STATUS_SUCCESS') {
            return redirect()->route('tickets.show', $id)->with('success', 'Resposta enviada!');
        }

        return redirect()->back()->with('error', 'Erro ao enviar resposta.');
    }

    public function close($id)
    {
        $response = $this->api->closeTicket((int)$id);

        if (($response['status'] ?? '') === 'STATUS_SUCCESS') {
            return redirect()->route('tickets.show', $id)->with('success', 'Ticket fechado.');
        }

        return redirect()->back()->with('error', 'Erro ao fechar ticket.');
    }
}
