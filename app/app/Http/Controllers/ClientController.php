<?php

namespace App\Http\Controllers;

use App\Models\Bouquet;
use App\Models\Line;
use App\Models\Package;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Parâmetros de busca e paginação
        $search = $request->input('search', '');
        $phone = $request->input('phone', '');
        $status = $request->input('status', '');
        $type = $request->input('type', '');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 20);

        // Validar perPage
        if (!in_array($perPage, [20, 50, 100, 500, 1000])) {
            $perPage = 20;
        }

        // Query base com eager loading
        $query = Line::select([
            'id', 'username', 'password', 'exp_date', 'enabled', 'admin_enabled',
            'is_trial', 'max_connections', 'member_id', 'package_id', 'created_at',
            'contact', 'admin_notes', 'bouquet'
        ])
        ->with([
            'member:id,username',
            'package:id,package_name,is_official'
        ]);

        // Filtro por permissão
        if (!$user->isAdmin()) {
            $myTreeIds = $user->getAllSubResellerIds();
            $query->whereIn('member_id', $myTreeIds);
        }

        // Busca por username ou senha
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('password', 'like', "%{$search}%");
            });
        }

        // Busca por telefone (admin_notes)
        if ($phone) {
            $query->where('admin_notes', 'like', "%{$phone}%");
        }

        // Filtro por Tipo
        if ($type === 'client') {
            $query->where('is_trial', 0);
        } elseif ($type === 'trial') {
            $query->where('is_trial', 1);
        }

        // Filtro por Status
        if ($status === 'active') {
            $query->where('enabled', 1)
                  ->where('admin_enabled', 1)
                  ->where('exp_date', '>', time());
        } elseif ($status === 'expired') {
            $query->where('exp_date', '<=', time());
        } elseif ($status === 'blocked') {
            $query->where(function($q) {
                $q->where('enabled', 0)
                  ->orWhere('admin_enabled', 0);
            });
        }

        // Filtros Rápidos (se passados via query string especial)
        $quickFilter = $request->input('quick_filter');
        if ($quickFilter) {
            $query->where('is_trial', 0); // Apenas oficiais para filtros rápidos
            $now = time();
            
            if ($quickFilter === 'today') {
                $endOfDay = strtotime('tomorrow') - 1;
                $query->whereBetween('exp_date', [$now, $endOfDay]);
            } elseif ($quickFilter === '7days') {
                $end7Days = strtotime('+7 days');
                $query->whereBetween('exp_date', [$now, $end7Days]);
            } elseif ($quickFilter === '30days') {
                $end30Days = strtotime('+30 days');
                $query->whereBetween('exp_date', [$now, $end30Days]);
            }
        }

        // Ordenação
        $query->orderBy($sortBy, $sortOrder);

        // Paginação
        $clients = $query->paginate($perPage)->appends($request->except('page'));

        // Calcular estatísticas rápidas (Apenas Clientes Oficiais)
        $statsQuery = Line::where('is_trial', 0);
        if (!$user->isAdmin()) {
            $myTreeIds = $user->getAllSubResellerIds();
            $statsQuery->whereIn('member_id', $myTreeIds);
        }

        // Clonar query para cada contador para evitar contaminação
        $now = time();
        $endOfDay = strtotime('tomorrow') - 1;
        $end7Days = strtotime('+7 days');
        $end30Days = strtotime('+30 days');

        $quickStats = cache()->remember('quick_stats_' . $user->id, 60, function () use ($statsQuery, $now, $endOfDay, $end7Days, $end30Days) {
            return [
                'today' => (clone $statsQuery)->whereBetween('exp_date', [$now, $endOfDay])->count(),
                '7days' => (clone $statsQuery)->whereBetween('exp_date', [$now, $end7Days])->count(),
                '30days' => (clone $statsQuery)->whereBetween('exp_date', [$now, $end30Days])->count(),
            ];
        });

        // Contagem total global (sem filtros) para controle de exibição de "Nenhum cliente"
        $totalGlobalQuery = Line::query();
        if (!$user->isAdmin()) {
            $myTreeIds = $user->getAllSubResellerIds();
            $totalGlobalQuery->whereIn('member_id', $myTreeIds);
        }
        $totalGlobal = $totalGlobalQuery->count();

        // Se for requisição AJAX, retornar JSON com stats atualizados
        if ($request->ajax()) {
            return response()->json([
                'html' => view('clients.partials.table', compact('clients'))->render(),
                'pagination' => view('clients.partials.pagination', compact('clients'))->render(),
                'stats' => $quickStats,
                'total' => $clients->total()
            ]);
        }

        // Cache de pacotes e bouquets
        $packages = cache()->remember('packages_all_v2', 3600, function () {
            return Package::select('id', 'package_name', 'is_official', 'is_trial', 'official_duration', 
                                   'official_duration_in', 'official_credits', 'max_connections', 
                                   'trial_duration', 'trial_duration_in', 'bouquets')
                          ->get();
        });
        
        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = cache()->remember('bouquets_filtered', 3600, function () use ($blacklist) {
            return Bouquet::select('id', 'bouquet_name', 'bouquet_order')
                          ->whereNotIn('id', $blacklist)
                          ->orderBy('bouquet_order')
                          ->get();
        });

        return view('clients.index', [
            'clients' => $clients,
            'packages' => $packages,
            'bouquets' => $bouquets,
            'quickStats' => $quickStats,
            'totalGlobal' => $totalGlobal
        ]);
    }

    public function create()
    {
        $packages = Package::where('is_official', 1)->get();
        
        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = Bouquet::whereNotIn('id', $blacklist)
            ->orderBy('bouquet_order')
            ->get();

        return view('clients.create', [
            'packages' => $packages,
            'bouquets' => $bouquets
        ]);
    }

    public function store(Request $request, LineService $lineService)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
            'package_id' => 'required|integer|exists:xui.users_packages,id',
            'bouquet_ids' => 'required|array|min:1',
            'max_connections' => 'required|integer|min:1|max:10',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            $phone = $validated['phone'] ?? '';
            $notes = $validated['notes'] ?? '';
            
            $finalNotes = $notes;
            if (!empty($phone)) {
                if (empty($finalNotes)) {
                    $finalNotes = $phone;
                } elseif (strpos($finalNotes, $phone) === false) {
                    $finalNotes = $phone . ' - ' . $finalNotes;
                }
            }

            $data = [
                'username' => $validated['username'],
                'password' => $validated['password'],
                'package_id' => $validated['package_id'],
                'bouquet_ids' => $validated['bouquet_ids'],
                'max_connections' => $validated['max_connections'],
                'email' => $validated['email'] ?? null,
                'phone' => $phone,
                'notes' => $finalNotes,
                'member_id' => $user->id,
                'is_trial' => false,
            ];

            $line = $lineService->createLine($data);
            
            // Gerar Mensagem do Cliente
            $clientMessage = $lineService->generateClientMessage($line);

            return redirect()->route('clients.index')
                ->with('success', 'Cliente criado com sucesso!')
                ->with('client_message', $clientMessage);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function createTrial()
    {
        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = Bouquet::whereNotIn('id', $blacklist)
            ->orderBy('bouquet_order')
            ->get();

        $packages = Package::all();

        return view('clients.create-trial', [
            'bouquets' => $bouquets,
            'packages' => $packages
        ]);
    }

    public function storeTrial(Request $request, LineService $lineService)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string|min:3|max:50',
                'password' => 'required|string|min:6',
                'package_id' => 'required|integer|exists:xui.users_packages,id',
                'bouquet_ids' => 'required|array|min:1',
                'duration_value' => 'required|integer|min:1',
                'duration_unit' => 'required|string|in:hours,days,months,years',
                'max_connections' => 'required|integer|min:1|max:10',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $user = Auth::user();

        try {
            $phone = $validated['phone'] ?? '';
            $notes = $validated['notes'] ?? '';
            
            $finalNotes = $notes;
            if (!empty($phone)) {
                if (empty($finalNotes)) {
                    $finalNotes = $phone;
                } elseif (strpos($finalNotes, $phone) === false) {
                    $finalNotes = $phone . ' - ' . $finalNotes;
                }
            }

            $data = [
                'username' => $validated['username'],
                'password' => $validated['password'],
                'package_id' => $validated['package_id'],
                'bouquet_ids' => $validated['bouquet_ids'],
                'duration_value' => $validated['duration_value'],
                'duration_unit' => $validated['duration_unit'],
                'max_connections' => $validated['max_connections'],
                'email' => $validated['email'] ?? null,
                'phone' => $phone,
                'notes' => $finalNotes,
                'member_id' => $user->id,
                'is_trial' => true,
            ];

            $line = $lineService->createLine($data);
            
            // Gerar Mensagem do Cliente
            $clientMessage = $lineService->generateClientMessage($line);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Teste criado com sucesso!',
                    'client' => [
                        'id' => $line->id,
                        'username' => $line->username,
                        'password' => $line->password,
                        'exp_date' => $line->exp_date,
                        'max_connections' => $line->max_connections,
                    ],
                    'client_message' => $clientMessage
                ]);
            }

            // Gerar URLs M3U para view (fallback)
            $urls = $lineService->generateM3uUrls($line);

            return redirect()->route('clients.create-trial')
                ->with('trial_success', [
                    'username' => $line->username,
                    'password' => $line->password,
                    'exp_date' => date('d/m/Y H:i', $line->exp_date),
                    'max_connections' => $line->max_connections,
                    'm3u_url' => $urls['m3u_url'],
                    'hls_url' => $urls['hls_url'],
                ])
                ->with('client_message', $clientMessage);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            
            return back()->withErrors(['error' => 'Erro ao criar teste: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(int $id)
    {
        $user = Auth::user();
        $line = Line::with('package')->find($id);

        if (!$line) {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        if (!$user->isAdmin() && $line->member_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão para editar este cliente']);
        }

        $packages = Package::where('is_official', 1)->get();
        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = Bouquet::whereNotIn('id', $blacklist)
            ->orderBy('bouquet_order')
            ->get();

        $selectedBouquets = json_decode($line->bouquet, true) ?? [];

        return view('clients.edit', [
            'client' => $line,
            'packages' => $packages,
            'bouquets' => $bouquets,
            'selectedBouquets' => $selectedBouquets
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
            'package_id' => 'nullable|integer',
            'bouquet_ids' => 'required|array|min:1',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'notes' => 'nullable|string|max:500',
            'enabled' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $line = Line::find($id);

        if (!$line) {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        if (!$user->isAdmin() && $line->member_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão para editar este cliente']);
        }

        try {
            $line->update([
                'username' => $validated['username'],
                'password' => $validated['password'],
                'package_id' => $validated['package_id'] ?? $line->package_id,
                'bouquet' => json_encode($validated['bouquet_ids']),
                'contact' => $validated['phone'] ?? '',
                'notes' => $validated['notes'] ?? '',
                'enabled' => $validated['enabled'] ?? $line->enabled,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Cliente atualizado com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            
            return back()->withErrors(['error' => 'Erro ao atualizar cliente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function renew(Request $request, int $id, LineService $lineService)
    {
        try {
            $validated = $request->validate([
                'package_id' => 'required|integer|exists:xui.users_packages,id',
                'duration_value' => 'required|integer|min:1',
                'duration_unit' => 'required|string|in:hours,days,months,years',
                'max_connections' => 'required|integer|min:1|max:10',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        $user = Auth::user();
        $line = Line::find($id);

        if (!$line) {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        if (!$user->isAdmin() && $line->member_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Sem permissão'], 403);
        }

        try {
            $data = [
                'package_id' => $validated['package_id'],
                'duration_value' => $validated['duration_value'],
                'duration_unit' => $validated['duration_unit'],
                'max_connections' => $validated['max_connections'],
            ];

            $line = $lineService->renewLine($id, $data);

            return response()->json([
                'success' => true, 
                'message' => 'Cliente renovado com sucesso!',
                'client' => [
                    'username' => $line->username,
                    'password' => $line->password,
                    'exp_date' => date('d/m/Y H:i', $line->exp_date),
                    'max_connections' => $line->max_connections,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function generateM3u(int $id, LineService $lineService)
    {
        $line = Line::find($id);

        if (!$line) {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        $urls = $lineService->generateM3uUrls($line);

        return view('clients.m3u', [
            'client' => $line,
            'm3u_url' => $urls['m3u_url'],
            'hls_url' => $urls['hls_url'],
        ]);
    }

    public function getM3uData(int $id, LineService $lineService)
    {
        $line = Line::find($id);

        if (!$line) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $urls = $lineService->generateM3uUrls($line);

        return response()->json([
            'm3u_url' => $urls['m3u_url'],
            'hls_url' => $urls['hls_url'],
        ]);
    }

    public function export()
    {
        return view('clients.export');
    }

    private function getFilteredClients(Request $request)
    {
        $user = Auth::user();
        
        $search = $request->input('search', '');
        $phone = $request->input('phone', '');
        $status = $request->input('status', '');

        $query = Line::select([
            'id', 'username', 'password', 'exp_date', 'enabled', 'admin_enabled',
            'is_trial', 'max_connections', 'member_id', 'package_id', 'created_at',
            'contact', 'admin_notes', 'bouquet'
        ])
        ->with([
            'member:id,username',
            'package:id,package_name,is_official'
        ]);

        if (!$user->isAdmin()) {
            $myTreeIds = $user->getAllSubResellerIds();
            $query->whereIn('member_id', $myTreeIds);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('password', 'like', "%{$search}%");
            });
        }

        if ($phone) {
            $query->where('admin_notes', 'like', "%{$phone}%");
        }

        if ($status === 'active') {
            $query->where('enabled', 1)
                  ->where('admin_enabled', 1)
                  ->where('exp_date', '>', time());
        } elseif ($status === 'expired') {
            $query->where('exp_date', '<=', time());
        } elseif ($status === 'trial') {
            $query->where('is_trial', 1);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function exportCSV(Request $request)
    {
        $clients = $this->getFilteredClients($request);
        
        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            fputcsv($file, ['#', 'Username', 'Senha', 'Telefone', 'Revenda', 'Tipo', 'Validade', 'Conexões', 'Status']);
            
            $index = 1;
            foreach ($clients as $client) {
                $isActive = $client->enabled && $client->admin_enabled && $client->exp_date > time();
                
                fputcsv($file, [
                    $index++,
                    $client->username,
                    $client->password,
                    $client->admin_notes ?? '',
                    $client->member->username ?? 'N/A',
                    $client->is_trial ? 'Teste' : 'Cliente',
                    date('d/m/Y H:i', $client->exp_date),
                    $client->max_connections,
                    $isActive ? 'Ativo' : 'Inativo'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTXT(Request $request)
    {
        $clients = $this->getFilteredClients($request);
        
        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.txt';
        
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $content = "# Lista de Clientes - " . date('d/m/Y H:i:s') . "\n";
        $content .= "# Total: " . $clients->count() . " clientes\n\n";
        
        foreach ($clients as $client) {
            $content .= "Username: {$client->username}\n";
            $content .= "Senha: {$client->password}\n";
            $content .= "Telefone: " . ($client->admin_notes ?? 'Não informado') . "\n";
            $content .= "Validade: " . date('d/m/Y H:i', $client->exp_date) . "\n";
            $content .= "Conexões: {$client->max_connections}\n";
            $content .= "---\n\n";
        }

        return response($content, 200, $headers);
    }

    public function exportJSON(Request $request)
    {
        $clients = $this->getFilteredClients($request);
        
        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.json';
        
        $data = $clients->map(function($client) {
            return [
                'username' => $client->username,
                'password' => $client->password,
                'phone' => $client->admin_notes ?? null,
                'reseller' => $client->member->username ?? null,
                'type' => $client->is_trial ? 'trial' : 'client',
                'expiration' => date('Y-m-d H:i:s', $client->exp_date),
                'expiration_timestamp' => $client->exp_date,
                'max_connections' => $client->max_connections,
                'enabled' => $client->enabled && $client->admin_enabled,
                'is_active' => $client->enabled && $client->admin_enabled && $client->exp_date > time(),
                'created_at' => date('Y-m-d H:i:s', $client->created_at),
            ];
        });

        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->json([
            'exported_at' => date('Y-m-d H:i:s'),
            'total' => $clients->count(),
            'clients' => $data
        ], 200, $headers);
    }

    public function exportM3U(Request $request, LineService $lineService)
    {
        $clients = $this->getFilteredClients($request);
        
        $filename = 'clientes_m3u_' . date('Y-m-d_H-i-s') . '.txt';
        
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $content = "# Lista de Links M3U8 - " . date('d/m/Y H:i:s') . "\n";
        $content .= "# Total: " . $clients->count() . " clientes\n\n";
        
        foreach ($clients as $client) {
            $urls = $lineService->generateM3uUrls($client);
            $content .= "{$urls['m3u_url']}\n";
        }

        return response($content, 200, $headers);
    }

    public function getEditData(int $id)
    {
        $user = Auth::user();
        $line = Line::find($id);

        if (!$line) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        if (!$user->isAdmin() && $line->member_id != $user->id) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = Bouquet::whereNotIn('id', $blacklist)
            ->orderBy('bouquet_order')
            ->get();

        $package = Package::find($line->package_id);

        // Buscar bouquets do PACOTE, não da linha
        $selectedBouquets = [];
        if ($package && $package->bouquets) {
            $selectedBouquets = json_decode($package->bouquets, true) ?? [];
        }

        return response()->json([
            'username' => $line->username,
            'password' => $line->password,
            'email' => '', // Email não é mais usado prioritariamente
            'phone' => $line->contact ?? '', // Retornar contact como phone
            'notes' => $line->notes ?? '',
            'package_name' => $package ? $package->package_name : 'Sem pacote',
            'max_connections' => $line->max_connections,
            'all_bouquets' => $bouquets,
            'selected_bouquets' => $selectedBouquets,
        ]);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $line = Line::find($id);

        if (!$line) {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        if (!$user->isAdmin() && $line->member_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão para excluir este cliente']);
        }

        $line->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}
