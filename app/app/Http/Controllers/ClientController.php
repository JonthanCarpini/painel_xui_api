<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ClientDetail;
use App\Models\WhatsappSetting;
use App\Services\EvolutionService;
use App\Services\LineService;
use App\Services\PackageService;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function __construct(
        private XuiApiService $api,
        private PackageService $packages,
    ) {}

    public function index(Request $request)
    {
        $user       = Auth::user();
        $search     = $request->input('search', '');
        $phone      = $request->input('phone', '');
        $status     = $request->input('status', '');
        $type       = $request->input('type', '');
        $resellerId = $request->input('reseller_id', '');
        $sortBy     = $request->input('sort_by', 'created_at');
        $sortOrder  = $request->input('sort_order', 'desc');
        $perPage    = (int)$request->input('per_page', 20);
        $quickFilter = $request->input('quick_filter');

        if (!in_array($perPage, [20, 50, 100, 500, 1000])) {
            $perPage = 20;
        }

        // Buscar todas as linhas via API
        $apiResponse = $this->api->getLines();
        $allLines    = $apiResponse['data'] ?? [];

        // Determinar IDs de revendedores permitidos
        $allowedMemberIds = null;
        if (!$user->isAdmin()) {
            $allowedMemberIds = $this->getSubResellerIds($user->xui_id);
        }

        // Filtros em memória (a API não suporta filtros server-side)
        $now = time();
        $filtered = array_filter($allLines, function ($line) use (
            $user, $allowedMemberIds, $resellerId, $search, $phone, $status, $type, $quickFilter, $now
        ) {
            // Permissão
            if ($allowedMemberIds !== null && !in_array((int)($line['member_id'] ?? 0), $allowedMemberIds)) {
                return false;
            }

            // Filtro por revenda
            if ($resellerId) {
                if ($resellerId === 'mine') {
                    if ((int)($line['member_id'] ?? 0) !== (int)$user->xui_id) return false;
                } else {
                    if ((int)($line['member_id'] ?? 0) !== (int)$resellerId) return false;
                }
            }

            // Busca por username/senha
            if ($search) {
                $u = strtolower($line['username'] ?? '');
                $p = strtolower($line['password'] ?? '');
                $s = strtolower($search);
                if (strpos($u, $s) === false && strpos($p, $s) === false) return false;
            }

            // Filtro por tipo
            if ($type === 'client' && (int)($line['is_trial'] ?? 0) !== 0) return false;
            if ($type === 'trial'  && (int)($line['is_trial'] ?? 0) !== 1) return false;

            // Filtro por status
            $exp     = (int)($line['exp_date'] ?? 0);
            $enabled = (int)($line['enabled'] ?? 1);
            $adminEn = (int)($line['admin_enabled'] ?? 1);
            if ($status === 'active'  && !($enabled && $adminEn && $exp > $now)) return false;
            if ($status === 'expired' && $exp > $now) return false;
            if ($status === 'blocked' && ($enabled && $adminEn)) return false;

            // Filtros rápidos (apenas oficiais)
            if ($quickFilter) {
                if ((int)($line['is_trial'] ?? 0) !== 0) return false;
                if ($quickFilter === 'today'  && !($exp >= $now && $exp <= strtotime('tomorrow') - 1)) return false;
                if ($quickFilter === '7days'  && !($exp >= $now && $exp <= strtotime('+7 days'))) return false;
                if ($quickFilter === '30days' && !($exp >= $now && $exp <= strtotime('+30 days'))) return false;
            }

            return true;
        });

        // Busca por telefone (via ClientDetail local)
        if ($phone) {
            $localIds = ClientDetail::where('phone', 'like', "%{$phone}%")->pluck('xui_client_id')->toArray();
            $filtered = array_filter($filtered, function ($line) use ($phone, $localIds) {
                $inNotes = strpos($line['admin_notes'] ?? '', $phone) !== false;
                return $inNotes || in_array((int)($line['id'] ?? 0), $localIds);
            });
        }

        $filtered = array_values($filtered);

        // Ordenação
        usort($filtered, function ($a, $b) use ($sortBy, $sortOrder) {
            $va = $a[$sortBy] ?? 0;
            $vb = $b[$sortBy] ?? 0;
            return $sortOrder === 'asc' ? ($va <=> $vb) : ($vb <=> $va);
        });

        // Paginação manual
        $total      = count($filtered);
        $page       = (int)$request->input('page', 1);
        $offset     = ($page - 1) * $perPage;
        $pageItems  = array_slice($filtered, $offset, $perPage);

        // Enriquecer com dados locais e pacotes
        $packages   = $this->packages->all()->keyBy('id');
        $clientIds  = array_column($pageItems, 'id');
        $localDetails = ClientDetail::whereIn('xui_client_id', $clientIds)->get()->keyBy('xui_client_id');

        foreach ($pageItems as &$line) {
            $lid = (int)($line['id'] ?? 0);
            $detail = $localDetails->get($lid);
            $line['local_phone'] = $detail ? $detail->phone : null;
            $line['local_notes'] = $detail ? $detail->notes : null;
            $pkg = $packages->get((int)($line['package_id'] ?? 0));
            $line['package_name'] = $pkg ? $pkg->package_name : null;
        }
        unset($line);

        // Paginador LengthAwarePaginator
        $clients = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        // Quick stats
        $quickStats = cache()->remember('quick_stats_api_' . $user->xui_id, 60, function () use ($allLines, $allowedMemberIds, $now) {
            $official = array_filter($allLines, function ($l) use ($allowedMemberIds) {
                if ((int)($l['is_trial'] ?? 0) !== 0) return false;
                return $allowedMemberIds === null || in_array((int)($l['member_id'] ?? 0), $allowedMemberIds);
            });
            $endDay   = strtotime('tomorrow') - 1;
            $end7     = strtotime('+7 days');
            $end30    = strtotime('+30 days');
            return [
                'today'  => count(array_filter($official, fn($l) => ($l['exp_date'] ?? 0) >= $now && ($l['exp_date'] ?? 0) <= $endDay)),
                '7days'  => count(array_filter($official, fn($l) => ($l['exp_date'] ?? 0) >= $now && ($l['exp_date'] ?? 0) <= $end7)),
                '30days' => count(array_filter($official, fn($l) => ($l['exp_date'] ?? 0) >= $now && ($l['exp_date'] ?? 0) <= $end30)),
            ];
        });

        $totalGlobal = count(array_filter($allLines, function ($l) use ($allowedMemberIds) {
            return $allowedMemberIds === null || in_array((int)($l['member_id'] ?? 0), $allowedMemberIds);
        }));

        // Revendedores para filtro (via API)
        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];
        $resellers = array_filter($allUsers, function ($u) use ($user) {
            if ((int)($u['member_group_id'] ?? 0) !== 2) return false;
            if ($user->isAdmin()) return true;
            return (int)($u['owner_id'] ?? 0) === (int)$user->xui_id;
        });

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('clients.partials.table', compact('clients'))->render(),
                'pagination' => view('clients.partials.pagination', compact('clients'))->render(),
                'stats'      => $quickStats,
                'total'      => $total,
            ]);
        }

        $allPackages = $this->packages->all();

        $bouquets = $this->packages->bouquets()->toArray();

        return view('clients.index', [
            'clients'     => $clients,
            'packages'    => $allPackages,
            'bouquets'    => $bouquets,
            'quickStats'  => $quickStats,
            'totalGlobal' => $totalGlobal,
            'resellers'   => array_values($resellers),
        ]);
    }

    public function create()
    {
        $packages = $this->packages->where('is_official', true);
        $bouquets = $this->packages->bouquets();

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
            
            // XUI Legado: Salvar no admin_notes também (opcional, mas bom para compatibilidade)
            $finalNotes = $notes;
            if (!empty($phone)) {
                if (empty($finalNotes)) {
                    $finalNotes = $phone;
                } elseif (strpos($finalNotes, $phone) === false) {
                    $finalNotes = $phone . ' - ' . $finalNotes;
                }
            }

            $data = [
                'username'        => $validated['username'],
                'password'        => $validated['password'],
                'package_id'      => $validated['package_id'],
                'bouquet_ids'     => $validated['bouquet_ids'],
                'max_connections' => $validated['max_connections'],
                'email'           => $validated['email'] ?? null,
                'phone'           => $phone,
                'notes'           => $finalNotes,
                'member_id'       => $user->xui_id,
                'is_trial'        => false,
            ];

            $line = $lineService->createLine($data);

            // Salvar Detalhes Locais (Painel Plus DB)
            ClientDetail::create([
                'xui_client_id' => $line['id'],
                'phone'         => $phone,
                'notes'         => $notes,
            ]);
            
            // Gerar Mensagem do Cliente
            $clientMessage = $lineService->generateClientMessage($line);

            return redirect()->route('clients.index')
                ->with('success', 'Cliente criado com sucesso!')
                ->with('client_message', $clientMessage)
                ->with('client_phone', $phone);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    // ... (createTrial and storeTrial methods remain mostly unchanged, just need to add ClientDetail logic to storeTrial if desired)

    public function createTrial()
    {
        // Verificar bloqueio de testes via configuração local do Painel
        $disableTrial = AppSetting::get('disable_trial', 0);
        if ($disableTrial == 1 && !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'A criação de testes está temporariamente desabilitada.');
        }

        $bouquets = $this->packages->bouquets();
        $packages = $this->packages->all();

        return view('clients.create-trial', [
            'bouquets' => $bouquets,
            'packages' => $packages
        ]);
    }

    public function storeTrial(Request $request, LineService $lineService)
    {
        // Verificar bloqueio de testes via configuração local do Painel
        $disableTrial = AppSetting::get('disable_trial', 0);
        if ($disableTrial == 1 && !Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'A criação de testes está temporariamente desabilitada.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'A criação de testes está temporariamente desabilitada.');
        }

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
                'username'        => $validated['username'],
                'password'        => $validated['password'],
                'package_id'      => $validated['package_id'],
                'bouquet_ids'     => $validated['bouquet_ids'],
                'duration_value'  => $validated['duration_value'],
                'duration_unit'   => $validated['duration_unit'],
                'max_connections' => $validated['max_connections'],
                'email'           => $validated['email'] ?? null,
                'phone'           => $phone,
                'notes'           => $finalNotes,
                'member_id'       => $user->xui_id,
                'is_trial'        => true,
            ];

            $line = $lineService->createLine($data);

            // Salvar Detalhes Locais também para testes
            ClientDetail::create([
                'xui_client_id' => $line['id'],
                'phone'         => $phone,
                'notes'         => $notes,
            ]);

            // Gerar Mensagem do Cliente
            $clientMessage = $lineService->generateClientMessage($line);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Teste criado com sucesso!',
                    'client' => [
                        'id'              => $line['id'],
                        'username'        => $line['username'],
                        'password'        => $line['password'],
                        'exp_date'        => $line['exp_date'],
                        'max_connections' => $line['max_connections'] ?? $validated['max_connections'],
                    ],
                    'client_message' => $clientMessage,
                ]);
            }

            // Gerar URLs M3U para view (fallback)
            $urls = $lineService->generateM3uUrls($line);

            return redirect()->route('clients.create-trial')
                ->with('trial_success', [
                    'username'        => $line['username'],
                    'password'        => $line['password'],
                    'exp_date'        => date('d/m/Y H:i', (int)$line['exp_date']),
                    'max_connections' => $line['max_connections'] ?? $validated['max_connections'],
                    'm3u_url'         => $urls['m3u_url'],
                    'hls_url'         => $urls['hls_url'],
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
        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        $line = $result['data'];

        if (!$user->isAdmin() && (int)($line['member_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão para editar este cliente']);
        }

        $packages = $this->packages->where('is_official', true);
        $bouquets = $this->packages->bouquets();

        $rawBouquet = $line['bouquet'] ?? '[]';
        $selectedBouquets = is_array($rawBouquet) ? $rawBouquet : (json_decode($rawBouquet, true) ?? []);

        $localDetail = ClientDetail::where('xui_client_id', $id)->first();
        $line['local_phone'] = $localDetail ? $localDetail->phone : null;
        $line['local_notes'] = $localDetail ? $localDetail->notes : null;

        return view('clients.edit', [
            'client'           => $line,
            'packages'         => $packages,
            'bouquets'         => $bouquets,
            'selectedBouquets' => $selectedBouquets,
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

        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        $line = $result['data'];

        if (!$user->isAdmin() && (int)($line['member_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão para editar este cliente']);
        }

        try {
            $phone = $validated['phone'] ?? '';
            $notes = $validated['notes'] ?? '';

            $editPayload = [
                'username'          => $validated['username'],
                'password'          => $validated['password'],
                'member_id'         => (int)($line['member_id'] ?? 0),
                'package_id'        => $validated['package_id'] ?? ($line['package_id'] ?? null),
                'bouquets_selected' => array_map('intval', $validated['bouquet_ids']),
                'contact'           => $phone,
                'admin_notes'       => $notes,
            ];

            if (isset($validated['enabled'])) {
                $editPayload['enabled'] = (int)$validated['enabled'];
            }

            $editResult = $this->api->editLine($id, $editPayload);

            if (($editResult['status'] ?? '') !== 'STATUS_SUCCESS') {
                throw new \Exception($editResult['message'] ?? 'Erro ao atualizar na API XUI');
            }

            ClientDetail::updateOrCreate(
                ['xui_client_id' => $id],
                ['phone' => $phone, 'notes' => $notes]
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
            }

            return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return back()->withErrors(['error' => 'Erro ao atualizar cliente: ' . $e->getMessage()])->withInput();
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

        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        $lineData = $result['data'];

        if (!$user->isAdmin() && (int)($lineData['member_id'] ?? 0) !== (int)$user->xui_id) {
            return response()->json(['success' => false, 'message' => 'Sem permissão'], 403);
        }

        try {
            $data = [
                'package_id'      => $validated['package_id'],
                'duration_value'  => $validated['duration_value'],
                'duration_unit'   => $validated['duration_unit'],
                'max_connections' => $validated['max_connections'],
            ];

            $line = $lineService->renewLine($id, $data);

            $localDetail = ClientDetail::where('xui_client_id', $id)->first();
            $clientPhone = $localDetail->phone ?? ($line['contact'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Cliente renovado com sucesso!',
                'client'  => [
                    'username'        => $line['username'],
                    'password'        => $line['password'],
                    'exp_date'        => date('d/m/Y H:i', (int)$line['exp_date']),
                    'max_connections' => $line['max_connections'] ?? $validated['max_connections'],
                    'phone'           => $clientPhone,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Renovar em Confiança
    public function renewTrust(int $id, LineService $lineService)
    {
        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado'], 404);
        }

        $lineData = $result['data'];

        if (!$user->isAdmin() && (int)($lineData['member_id'] ?? 0) !== (int)$user->xui_id) {
            return response()->json(['success' => false, 'message' => 'Sem permissão'], 403);
        }

        $trustPackageId = AppSetting::get('trust_renew_package_id');
        if (!$trustPackageId) {
            return response()->json(['success' => false, 'message' => 'Pacote de confiança não configurado pelo Administrador.'], 400);
        }

        $package = $this->packages->find((int)$trustPackageId);
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Pacote de confiança configurado não existe mais.'], 400);
        }

        try {
            $data = [
                'package_id'      => $package->id,
                'duration_value'  => $package->official_duration,
                'duration_unit'   => $package->official_duration_in,
                'max_connections' => $package->max_connections ?? 1,
            ];

            $line = $lineService->renewLine($id, $data);

            $localDetail = ClientDetail::where('xui_client_id', $id)->first();
            $clientPhone = $localDetail->phone ?? ($line['contact'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Renovação em confiança realizada com sucesso!',
                'client'  => [
                    'username' => $line['username'],
                    'exp_date' => date('d/m/Y H:i', (int)$line['exp_date']),
                    'phone'    => $clientPhone,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Sincronizar Clientes XUI -> Local
    public function sync(Request $request)
    {
        $user = Auth::user();

        try {
            $apiResponse = $this->api->getLines();
            $allLines    = $apiResponse['data'] ?? [];

            if (!$user->isAdmin()) {
                $allowed  = $this->getSubResellerIds($user->xui_id);
                $allLines = array_filter($allLines, fn($l) => in_array((int)($l['member_id'] ?? 0), $allowed));
            }

            $count = 0;
            foreach ($allLines as $line) {
                $lineId = (int)($line['id'] ?? 0);
                if (!$lineId) continue;

                $exists = ClientDetail::where('xui_client_id', $lineId)->exists();
                if (!$exists) {
                    ClientDetail::create([
                        'xui_client_id' => $lineId,
                        'phone'         => $line['contact'] ?? '',
                        'notes'         => '',
                    ]);
                    $count++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Sincronização concluída. {$count} clientes importados para base local.",
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function generateM3u(int $id, LineService $lineService)
    {
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        $line = $result['data'];
        $urls = $lineService->generateM3uUrls($line);

        return view('clients.m3u', [
            'client'  => $line,
            'm3u_url' => $urls['m3u_url'],
            'hls_url' => $urls['hls_url'],
        ]);
    }

    public function getM3uData(int $id, LineService $lineService)
    {
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $urls = $lineService->generateM3uUrls($result['data']);

        return response()->json([
            'm3u_url' => $urls['m3u_url'],
            'hls_url' => $urls['hls_url'],
        ]);
    }

    public function getMessage(int $id, LineService $lineService)
    {
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $message = $lineService->generateClientMessage($result['data']);

        return response()->json(['message' => $message]);
    }

    public function sendWhatsapp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4000',
        ]);

        $user = Auth::user();
        $panelUser = $user->panelUser;

        if (!$panelUser) {
            return response()->json(['success' => false, 'message' => 'Perfil não encontrado.'], 403);
        }

        $setting = WhatsappSetting::where('panel_user_id', $panelUser->id)->first();

        if (!$setting || $setting->connection_status !== 'connected') {
            return response()->json(['success' => false, 'message' => 'WhatsApp não está conectado. Conecte na aba Perfil > WhatsApp.'], 422);
        }

        $phone = preg_replace('/\D/', '', $request->phone);

        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        $evo = new EvolutionService();
        $result = $evo->sendText($setting->instance_name, $phone, $request->message);

        Log::info('WhatsApp message sent', [
            'user' => $user->username,
            'phone' => $phone,
            'success' => $result['success'],
        ]);

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'Falha ao enviar: ' . ($result['error'] ?? 'Erro desconhecido')], 500);
    }

    public function export()
    {
        return view('clients.export');
    }

    public function exportCSV(Request $request)
    {
        $clients = $this->getFilteredClients($request);
        
        $mode = $request->input('mode', 'full');
        $filename = 'clientes_' . ($mode === 'simple' ? 'contatos_' : '') . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($clients, $mode) {
            $file      = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            $delimiter = ';';

            if ($mode === 'simple') {
                fputcsv($file, ['Username', 'Telefone'], $delimiter);
            } else {
                fputcsv($file, ['#', 'Username', 'Senha', 'Telefone', 'Revenda', 'Tipo', 'Validade', 'Conexões', 'Status'], $delimiter);
            }

            $index = 1;
            $now   = time();
            foreach ($clients as $c) {
                $phone = $c['local_phone'] ?? $c['admin_notes'] ?? $c['contact'] ?? '';

                if ($mode === 'simple') {
                    fputcsv($file, [$c['username'] ?? '', $phone], $delimiter);
                } else {
                    $exp      = (int)($c['exp_date'] ?? 0);
                    $enabled  = (int)($c['enabled'] ?? 1);
                    $adminEn  = (int)($c['admin_enabled'] ?? 1);
                    $isActive = $enabled && $adminEn && $exp > $now;

                    fputcsv($file, [
                        $index++,
                        $c['username'] ?? '',
                        $c['password'] ?? '',
                        $phone,
                        $c['member_username'] ?? 'N/A',
                        (int)($c['is_trial'] ?? 0) ? 'Teste' : 'Cliente',
                        $exp ? date('d/m/Y H:i', $exp) : '',
                        $c['max_connections'] ?? 1,
                        $isActive ? 'Ativo' : 'Inativo',
                    ], $delimiter);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTXT(Request $request)
    {
        $clients  = $this->getFilteredClients($request);
        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.txt';
        $headers  = [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $content  = "# Lista de Clientes - " . date('d/m/Y H:i:s') . "\n";
        $content .= "# Total: " . count($clients) . " clientes\n\n";

        foreach ($clients as $c) {
            $phone    = $c['local_phone'] ?? $c['admin_notes'] ?? $c['contact'] ?? 'Não informado';
            $exp      = (int)($c['exp_date'] ?? 0);
            $content .= "Username: {$c['username']}\n";
            $content .= "Senha: {$c['password']}\n";
            $content .= "Telefone: {$phone}\n";
            $content .= "Validade: " . ($exp ? date('d/m/Y H:i', $exp) : 'N/A') . "\n";
            $content .= "Conexões: {$c['max_connections']}\n";
            $content .= "---\n\n";
        }

        return response($content, 200, $headers);
    }

    public function exportJSON(Request $request)
    {
        $clients  = $this->getFilteredClients($request);
        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.json';
        $now      = time();

        $data = array_map(function ($c) use ($now) {
            $exp     = (int)($c['exp_date'] ?? 0);
            $enabled = (int)($c['enabled'] ?? 1) && (int)($c['admin_enabled'] ?? 1);
            return [
                'username'             => $c['username'] ?? '',
                'password'             => $c['password'] ?? '',
                'phone'                => $c['local_phone'] ?? $c['admin_notes'] ?? $c['contact'] ?? null,
                'reseller'             => $c['member_username'] ?? null,
                'type'                 => (int)($c['is_trial'] ?? 0) ? 'trial' : 'client',
                'expiration'           => $exp ? date('Y-m-d H:i:s', $exp) : null,
                'expiration_timestamp' => $exp,
                'max_connections'      => $c['max_connections'] ?? 1,
                'enabled'              => $enabled,
                'is_active'            => $enabled && $exp > $now,
                'created_at'           => isset($c['created_at']) ? date('Y-m-d H:i:s', (int)$c['created_at']) : null,
            ];
        }, $clients);

        $headers = [
            'Content-Type'        => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->json([
            'exported_at' => date('Y-m-d H:i:s'),
            'total'       => count($clients),
            'clients'     => $data,
        ], 200, $headers);
    }

    public function exportM3U(Request $request, LineService $lineService)
    {
        $clients  = $this->getFilteredClients($request);
        $filename = 'clientes_m3u_' . date('Y-m-d_H-i-s') . '.txt';
        $headers  = [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $content  = "# Lista de Links M3U8 - " . date('d/m/Y H:i:s') . "\n";
        $content .= "# Total: " . count($clients) . " clientes\n\n";

        foreach ($clients as $c) {
            $urls     = $lineService->generateM3uUrls($c);
            $content .= "{$urls['m3u_url']}\n";
        }

        return response($content, 200, $headers);
    }

    public function getEditData(int $id)
    {
        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $line = $result['data'];

        if (!$user->isAdmin() && (int)($line['member_id'] ?? 0) !== (int)$user->xui_id) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $bouquets = $this->packages->bouquets();
        $package  = $this->packages->find((int)($line['package_id'] ?? 0));

        $rawBouquet = $line['bouquet'] ?? null;
        $selectedBouquets = [];
        if ($rawBouquet) {
            $selectedBouquets = is_array($rawBouquet) ? $rawBouquet : (json_decode($rawBouquet, true) ?? []);
        } elseif ($package && $package->bouquets) {
            $pkgBouquets = $package->bouquets;
            $selectedBouquets = is_string($pkgBouquets) ? (json_decode($pkgBouquets, true) ?? []) : (array)$pkgBouquets;
        }

        $localDetail = ClientDetail::where('xui_client_id', $id)->first();
        $phone = $localDetail ? $localDetail->phone : ($line['contact'] ?? '');
        $notes = $localDetail ? $localDetail->notes : ($line['admin_notes'] ?? '');

        return response()->json([
            'username'          => $line['username'] ?? '',
            'password'          => $line['password'] ?? '',
            'email'             => '',
            'phone'             => $phone,
            'notes'             => $notes,
            'package_name'      => $package ? $package->package_name : 'Sem pacote',
            'max_connections'   => $line['max_connections'] ?? 1,
            'all_bouquets'      => $bouquets,
            'selected_bouquets' => $selectedBouquets,
        ]);
    }

    public function destroy(int $id)
    {
        $user   = Auth::user();
        $result = $this->api->getLine($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Cliente não encontrado']);
        }

        $line = $result['data'];

        if (!$user->isAdmin() && (int)($line['member_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão para excluir este cliente']);
        }

        $deleteResult = $this->api->deleteLine($id);

        if (($deleteResult['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Erro ao excluir cliente na API XUI: ' . ($deleteResult['message'] ?? 'erro')]);
        }

        ClientDetail::where('xui_client_id', $id)->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente excluído com sucesso!');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Retorna IDs do revendedor + todos os sub-revendedores recursivamente via API.
     */
    private function getSubResellerIds(int $userId): array
    {
        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];

        $ids = [$userId];
        $changed = true;

        while ($changed) {
            $changed = false;
            foreach ($allUsers as $u) {
                $uid = (int)($u['id'] ?? 0);
                $oid = (int)($u['owner_id'] ?? 0);
                if ($oid && in_array($oid, $ids) && !in_array($uid, $ids)) {
                    $ids[]   = $uid;
                    $changed = true;
                }
            }
        }

        return array_unique($ids);
    }

    /**
     * Retorna linhas filtradas para exportação (via API).
     */
    private function getFilteredClients(Request $request): array
    {
        $user    = Auth::user();
        $search  = $request->input('search', '');
        $phone   = $request->input('phone', '');
        $status  = $request->input('status', '');

        $apiResponse = $this->api->getLines();
        $allLines    = $apiResponse['data'] ?? [];

        $allowedMemberIds = null;
        if (!$user->isAdmin()) {
            $allowedMemberIds = $this->getSubResellerIds($user->xui_id);
        }

        $now = time();
        $filtered = array_filter($allLines, function ($line) use ($allowedMemberIds, $search, $status, $now) {
            if ($allowedMemberIds !== null && !in_array((int)($line['member_id'] ?? 0), $allowedMemberIds)) {
                return false;
            }
            if ($search) {
                $u = strtolower($line['username'] ?? '');
                $p = strtolower($line['password'] ?? '');
                $s = strtolower($search);
                if (strpos($u, $s) === false && strpos($p, $s) === false) return false;
            }
            $exp     = (int)($line['exp_date'] ?? 0);
            $enabled = (int)($line['enabled'] ?? 1);
            $adminEn = (int)($line['admin_enabled'] ?? 1);
            if ($status === 'active'  && !($enabled && $adminEn && $exp > $now)) return false;
            if ($status === 'expired' && $exp > $now) return false;
            if ($status === 'trial'   && (int)($line['is_trial'] ?? 0) !== 1) return false;
            return true;
        });

        if ($phone) {
            $localIds = ClientDetail::where('phone', 'like', "%{$phone}%")->pluck('xui_client_id')->toArray();
            $filtered = array_filter($filtered, function ($line) use ($phone, $localIds) {
                return strpos($line['admin_notes'] ?? '', $phone) !== false
                    || in_array((int)($line['id'] ?? 0), $localIds);
            });
        }

        $filtered = array_values($filtered);

        // Enriquecer com dados locais
        $packages   = $this->packages->all()->keyBy('id');
        $clientIds  = array_column($filtered, 'id');
        $localDetails = ClientDetail::whereIn('xui_client_id', $clientIds)->get()->keyBy('xui_client_id');

        // Enriquecer com dados de revendedores
        $usersResp = $this->api->getUsers();
        $usersMap  = collect($usersResp['data'] ?? [])->keyBy('id');

        foreach ($filtered as &$line) {
            $lid = (int)($line['id'] ?? 0);
            $detail = $localDetails->get($lid);
            $line['local_phone']   = $detail ? $detail->phone : null;
            $line['package_name']  = $packages->get((int)($line['package_id'] ?? 0))?->package_name;
            $line['member_username'] = $usersMap->get((int)($line['member_id'] ?? 0))['username'] ?? 'N/A';
        }
        unset($line);

        return $filtered;
    }
}
