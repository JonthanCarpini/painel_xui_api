<?php

namespace App\Http\Controllers;

use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitorController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index()
    {
        $user        = Auth::user();
        $liveResp    = $this->api->getLiveConnections();
        $connections = $liveResp['data'] ?? [];

        if (!$user->isAdmin()) {
            $allowedIds  = $this->getSubResellerIds((int)$user->xui_id);
            $linesResp   = $this->api->getLines();
            $allLines    = $linesResp['data'] ?? [];

            // Mapear line_id → member_id para filtrar conexões
            $lineMemberMap = [];
            foreach ($allLines as $l) {
                $lineMemberMap[(int)($l['id'] ?? 0)] = (int)($l['member_id'] ?? 0);
            }

            $connections = array_values(array_filter($connections, function ($c) use ($allowedIds, $lineMemberMap) {
                $lineId   = (int)($c['user_id'] ?? 0);
                $memberId = $lineMemberMap[$lineId] ?? 0;
                return in_array($memberId, $allowedIds);
            }));
        }

        // Calcular duração em segundos para cada conexão
        $now = time();
        foreach ($connections as &$c) {
            $c['duration'] = $now - (int)($c['date_start'] ?? $now);
        }
        unset($c);

        // Ordenar por date_start desc
        usort($connections, fn($a, $b) => ($b['date_start'] ?? 0) <=> ($a['date_start'] ?? 0));

        // Converter para Collection de objetos para compatibilidade com a view
        $connections = collect($connections)->map(fn($c) => (object) $c);

        return view('monitor.index', ['connections' => $connections]);
    }

    public function kill(int $activityId)
    {
        try {
            $user     = Auth::user();
            $liveResp = $this->api->getLiveConnections();
            $liveConns = $liveResp['data'] ?? [];

            // Localizar a conexão pelo activity_id
            $connection = null;
            foreach ($liveConns as $c) {
                if ((int)($c['activity_id'] ?? 0) === $activityId) {
                    $connection = $c;
                    break;
                }
            }

            if (!$connection) {
                return response()->json(['success' => false, 'message' => 'Conexão não encontrada ou já encerrada.'], 404);
            }

            // Verificar permissão para não-admins
            if (!$user->isAdmin()) {
                $lineId    = (int)($connection['user_id'] ?? 0);
                $lineResp  = $this->api->getLine($lineId);
                $lineData  = $lineResp['data'] ?? [];
                $allowedIds = $this->getSubResellerIds((int)$user->xui_id);

                if (!in_array((int)($lineData['member_id'] ?? 0), $allowedIds)) {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para derrubar esta conexão.'], 403);
                }
            }

            // Derrubar via API
            $killResult = $this->api->killConnection($activityId);

            if (($killResult['status'] ?? '') !== 'STATUS_SUCCESS') {
                return response()->json(['success' => false, 'message' => 'Erro ao derrubar conexão: ' . ($killResult['message'] ?? 'erro')], 500);
            }

            return response()->json(['success' => true, 'message' => 'Conexão derrubada com sucesso!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao derrubar conexão: ' . $e->getMessage()], 500);
        }
    }

    private function getSubResellerIds(int $userId): array
    {
        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];
        $ids       = [$userId];
        $changed   = true;

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
}
