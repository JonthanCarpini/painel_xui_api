<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketExtra;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $categoriesStats = [];
        $selectedCategory = null;
        $uncategorizedCount = 0;

        $query = Ticket::query()->orderByRaw("CASE WHEN status = 0 THEN 1 ELSE 0 END ASC, id DESC");

        if (!$user->isAdmin()) {
            $query->where('member_id', (int)$user->xui_id);
        }

        $tickets = $query->paginate(20)->withQueryString();

        if ($user->isAdmin()) {
            $categories = TicketCategory::all();
            $activeTicketIds = Ticket::where('status', '!=', 0)->pluck('id')->toArray();

            $extrasCount = !empty($activeTicketIds)
                ? TicketExtra::whereIn('ticket_id', $activeTicketIds)
                    ->selectRaw('category_id, count(*) as total')
                    ->groupBy('category_id')
                    ->pluck('total', 'category_id')
                : collect();

            foreach ($categories as $cat) {
                $categoriesStats[$cat->id] = [
                    'name'   => $cat->name,
                    'count'  => $extrasCount[$cat->id] ?? 0,
                    'active' => $request->query('category') == $cat->id,
                ];
            }

            $categorizedTicketIds = TicketExtra::pluck('ticket_id')->toArray();
            $uncategorizedCount = Ticket::where('status', '!=', 0)
                ->whereNotIn('id', $categorizedTicketIds)
                ->count();
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
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:ticket_categories,id',
            'message'     => 'required|string',
        ]);

        $user = Auth::user();

        $ticket = Ticket::create([
            'member_id'  => (int)$user->xui_id,
            'title'      => $request->title,
            'status'     => Ticket::STATUS_OPEN,
            'admin_read' => false,
            'user_read'  => false,
        ]);

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'admin_reply' => false,
            'message'     => $request->message,
            'date'        => time(),
        ]);

        TicketExtra::create([
            'ticket_id'   => $ticket->id,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket criado com sucesso!');
    }

    public function show($id)
    {
        $user = Auth::user();
        $ticket = Ticket::with('replies')->findOrFail((int)$id);

        if (!$user->isAdmin() && (int)$ticket->member_id !== (int)$user->xui_id) {
            abort(403);
        }

        $extra = TicketExtra::where('ticket_id', $ticket->id)->first();
        $ticket->category = $extra ? TicketCategory::find($extra->category_id) : null;

        if ($user->isAdmin()) {
            $ticket->update(['admin_read' => true]);
        } else {
            $ticket->update(['user_read' => true]);
        }

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $ticket = Ticket::findOrFail((int)$id);

        if (!$user->isAdmin() && (int)$ticket->member_id !== (int)$user->xui_id) {
            abort(403);
        }

        $isAdmin = $user->isAdmin();

        TicketReply::create([
            'ticket_id'   => $ticket->id,
            'admin_reply' => $isAdmin,
            'message'     => $request->message,
            'date'        => time(),
        ]);

        $ticket->update([
            'admin_read' => $isAdmin ? true : false,
            'user_read'  => $isAdmin ? false : true,
        ]);

        return redirect()->route('tickets.show', $id)->with('success', 'Resposta enviada!');
    }

    public function close($id)
    {
        $user = Auth::user();
        $ticket = Ticket::findOrFail((int)$id);

        if (!$user->isAdmin() && (int)$ticket->member_id !== (int)$user->xui_id) {
            abort(403);
        }

        $ticket->update(['status' => Ticket::STATUS_CLOSED]);

        return redirect()->route('tickets.show', $id)->with('success', 'Ticket fechado.');
    }
}
