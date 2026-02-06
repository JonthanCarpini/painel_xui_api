<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketExtra;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $categoriesStats = [];
        $selectedCategory = null;

        if ($user->isAdmin()) {
            // Lógica de Pastas/Filtros por Categoria
            
            // 1. Buscar todas as categorias
            $categories = TicketCategory::all();
            
            // 2. Buscar IDs de tickets ativos (não fechados) para contagem
            // Status: 1 (Aberto), 2 (Respondido) -> != 0 (Fechado)
            $activeTicketIds = Ticket::where('status', '!=', 0)->pluck('id');
            
            // 3. Contar tickets por categoria (apenas ativos)
            $extrasCount = TicketExtra::whereIn('ticket_id', $activeTicketIds)
                ->selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
                
            foreach ($categories as $cat) {
                $categoriesStats[$cat->id] = [
                    'name' => $cat->name,
                    'count' => $extrasCount[$cat->id] ?? 0,
                    'active' => $request->query('category') == $cat->id
                ];
            }
            
            // Contar sem categoria (Ativos totais - Ativos com categoria)
            // Cuidado: Um ticket pode não ter extra.
            $categorizedTicketIds = TicketExtra::pluck('ticket_id');
            $uncategorizedCount = Ticket::where('status', '!=', 0)
                ->whereNotIn('id', $categorizedTicketIds)
                ->count();
                
            // Query Principal Filtrada
            $query = Ticket::with(['user', 'extra.category'])
                ->orderBy('status', 'asc') // Abertos primeiro
                ->orderBy('id', 'desc');

            if ($request->has('category')) {
                $filter = $request->query('category');
                
                if ($filter === 'uncategorized') {
                    $selectedCategory = 'Sem Categoria';
                    // Filtra onde NÃO tem registro na tabela ticket_extras
                    // Como é cross-database, usamos whereNotIn com os IDs que tem extra
                    $query->whereNotIn('id', $categorizedTicketIds);
                } elseif (is_numeric($filter)) {
                    $selectedCatObj = $categories->find($filter);
                    $selectedCategory = $selectedCatObj ? $selectedCatObj->name : 'Categoria Desconhecida';
                    
                    // Filtra onde TEM registro na tabela ticket_extras com o category_id
                    $ticketsInCat = TicketExtra::where('category_id', $filter)->pluck('ticket_id');
                    $query->whereIn('id', $ticketsInCat);
                }
            } else {
                $selectedCategory = 'Todos';
            }

            $tickets = $query->paginate(20);

        } else {
            // Revendedor vê apenas seus tickets
            $tickets = Ticket::where('member_id', $user->id)
                ->with('extra.category')
                ->orderBy('status', 'asc')
                ->orderBy('id', 'desc')
                ->paginate(20);
                
            $uncategorizedCount = 0;
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

        // Criar Ticket (Banco XUI)
        $ticket = Ticket::create([
            'member_id' => $user->id,
            'title' => $request->title,
            'status' => 1, // 1 = Aberto
            'admin_read' => 0,
            'user_read' => 1,
        ]);

        // Criar Vínculo de Categoria (Banco Local)
        TicketExtra::create([
            'ticket_id' => $ticket->id,
            'category_id' => $request->category_id,
        ]);

        // Criar primeira mensagem (Banco XUI)
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'admin_reply' => 0, // Usuário criou
            'message' => $request->message,
            'date' => time(),
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso!');
    }

    public function show($id)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $ticket = Ticket::with(['replies', 'user', 'extra.category'])->findOrFail($id);
            // Marcar como lido pelo admin
            if ($ticket->admin_read == 0) {
                $ticket->admin_read = 1;
                $ticket->save();
            }
        } else {
            $ticket = Ticket::with(['replies', 'extra.category'])->where('member_id', $user->id)->findOrFail($id);
            // Marcar como lido pelo usuário
            if ($ticket->user_read == 0) {
                $ticket->user_read = 1;
                $ticket->save();
            }
        }

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $ticket = Ticket::findOrFail($id);
            $isAdminReply = 1;
            
            // Atualizar status do ticket para Respondido (2) ou manter Aberto (1)
            // Normalmente admin respondendo muda para Respondido
            $ticket->status = 2; 
            $ticket->user_read = 0; // Usuário não leu ainda
            $ticket->save();
        } else {
            $ticket = Ticket::where('member_id', $user->id)->findOrFail($id);
            $isAdminReply = 0;
            
            // Se usuário responde, reabre o ticket se estiver fechado ou respondido
            $ticket->status = 1;
            $ticket->admin_read = 0; // Admin não leu ainda
            $ticket->save();
        }

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'admin_reply' => $isAdminReply,
            'message' => $request->message,
            'date' => time(),
        ]);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Resposta enviada!');
    }

    public function close($id)
    {
        // Apenas admin ou dono pode fechar? Vamos permitir ambos por enquanto
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $ticket = Ticket::findOrFail($id);
        } else {
            $ticket = Ticket::where('member_id', $user->id)->findOrFail($id);
        }

        $ticket->status = 0; // 0 = Fechado
        $ticket->save();

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Ticket fechado.');
    }
}
