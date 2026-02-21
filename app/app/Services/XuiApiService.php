<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XuiApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('xui.base_url', ''), '/') . '/';
        $this->apiKey  = config('xui.api_key', '');
        $this->timeout = (int) config('xui.timeout', 30);
    }

    // -------------------------------------------------------------------------
    // Transporte
    // -------------------------------------------------------------------------

    private function get(string $action, array $params = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, array_merge(
                    ['api_key' => $this->apiKey, 'action' => $action],
                    $params
                ));

            return $this->parseResponse($action, $response);
        } catch (\Exception $e) {
            return $this->errorResponse($action, $e->getMessage());
        }
    }

    private function post(string $action, array $data = []): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post(
                    $this->baseUrl . '?api_key=' . urlencode($this->apiKey) . '&action=' . urlencode($action),
                    $data
                );

            return $this->parseResponse($action, $response);
        } catch (\Exception $e) {
            return $this->errorResponse($action, $e->getMessage());
        }
    }

    private function parseResponse(string $action, $response): array
    {
        if ($response->failed()) {
            Log::error('XUI API falhou', [
                'action' => $action,
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            return ['status' => 'STATUS_FAILURE', 'message' => 'Erro HTTP ' . $response->status()];
        }

        $data = $response->json();

        if ($data === null) {
            Log::error('XUI API resposta inválida', ['action' => $action, 'body' => substr($response->body(), 0, 200)]);
            return ['status' => 'STATUS_FAILURE', 'message' => 'Resposta inválida do servidor'];
        }

        return $data;
    }

    private function errorResponse(string $action, string $message): array
    {
        Log::error('XUI API exceção', ['action' => $action, 'error' => $message]);
        return ['status' => 'STATUS_FAILURE', 'message' => 'Erro de conexão: ' . $message];
    }

    private function isSuccess(array $response): bool
    {
        return ($response['status'] ?? '') === 'STATUS_SUCCESS';
    }

    // -------------------------------------------------------------------------
    // Linhas
    // -------------------------------------------------------------------------

    public function getLines(): array
    {
        return $this->get('get_lines');
    }

    public function getLine(int $id): array
    {
        return $this->get('get_line', ['id' => $id]);
    }

    public function createLine(array $data): array
    {
        $data = $this->normalizeBouquets($data);
        $data = $this->normalizeExpDate($data);
        return $this->post('create_line', $data);
    }

    /**
     * Editar linha.
     * ATENÇÃO: se username for omitido, o XUI randomiza o username da linha.
     * Este método busca o username atual automaticamente quando não fornecido.
     */
    public function editLine(int $id, array $data): array
    {
        if (!isset($data['username'])) {
            $current = $this->getLine($id);
            if ($this->isSuccess($current)) {
                $data['username'] = $current['data']['username'] ?? '';
            }
        }

        $data['id'] = $id;
        $data = $this->normalizeBouquets($data);
        $data = $this->normalizeExpDate($data);
        return $this->post('edit_line', $data);
    }

    public function deleteLine(int $id): array
    {
        return $this->post('delete_line', ['id' => $id]);
    }

    public function enableLine(int $id): array
    {
        return $this->post('enable_line', ['id' => $id]);
    }

    public function disableLine(int $id): array
    {
        return $this->post('disable_line', ['id' => $id]);
    }

    public function banLine(int $id): array
    {
        return $this->post('ban_line', ['id' => $id]);
    }

    public function unbanLine(int $id): array
    {
        return $this->post('unban_line', ['id' => $id]);
    }

    // -------------------------------------------------------------------------
    // Usuários / Revendedores
    // -------------------------------------------------------------------------

    public function getUsers(): array
    {
        return $this->get('get_users');
    }

    public function getUser(int $id): array
    {
        return $this->get('get_user', ['id' => $id]);
    }

    public function createUser(array $data): array
    {
        $data['member_group_id'] = $data['member_group_id'] ?? 2;
        return $this->post('create_user', $data);
    }

    /**
     * Editar usuário.
     * ATENÇÃO: se username ou member_group_id forem omitidos, o XUI randomiza o
     * username ou retorna STATUS_INVALID_GROUP. Este método busca os valores
     * atuais automaticamente quando não fornecidos.
     */
    public function editUser(int $id, array $data): array
    {
        if (!isset($data['username']) || !isset($data['member_group_id'])) {
            $current = $this->getUser($id);
            if (!$this->isSuccess($current)) {
                return $current;
            }
            $user = $current['data'] ?? [];
            $data['username']        = $data['username']        ?? ($user['username']        ?? '');
            $data['member_group_id'] = $data['member_group_id'] ?? ($user['member_group_id'] ?? 2);
        }

        $data['id'] = $id;
        return $this->post('edit_user', $data);
    }

    public function deleteUser(int $id): array
    {
        return $this->post('delete_user', ['id' => $id]);
    }

    public function enableUser(int $id): array
    {
        return $this->post('enable_user', ['id' => $id]);
    }

    public function disableUser(int $id): array
    {
        return $this->post('disable_user', ['id' => $id]);
    }

    /**
     * Adicionar créditos a um revendedor de forma segura.
     * add_credits NÃO EXISTE no v1.5.12 — este método busca o saldo atual e soma.
     * Retorna array com 'credits_before', 'credits_after' para log.
     *
     * @param int    $userId   ID do revendedor que recebe os créditos
     * @param float  $amount   Quantidade a adicionar
     * @param int|null $adminId  ID de quem executou a operação (para o log)
     * @param string|null $reason  Motivo da operação (para o log)
     */
    public function addCredits(int $userId, float $amount, ?int $adminId = null, ?string $reason = null): array
    {
        $current = $this->getUser($userId);

        if (!$this->isSuccess($current)) {
            return array_merge($current, ['credits_before' => null, 'credits_after' => null]);
        }

        $creditsBefore = (float) ($current['data']['credits'] ?? 0);
        $creditsAfter  = $creditsBefore + $amount;

        $result = $this->editUser($userId, ['credits' => $creditsAfter]);

        if ($this->isSuccess($result)) {
            $this->writeCreditLog($userId, $amount, $adminId, $reason);
        }

        return array_merge($result, [
            'credits_before' => $creditsBefore,
            'credits_after'  => $creditsAfter,
        ]);
    }

    /**
     * Subtrair créditos de um revendedor de forma segura.
     * Retorna array com 'credits_before', 'credits_after' para log.
     *
     * @param int    $userId   ID do revendedor que perde os créditos
     * @param float  $amount   Quantidade a subtrair (positivo)
     * @param int|null $adminId  ID de quem executou a operação (para o log)
     * @param string|null $reason  Motivo da operação (para o log)
     */
    public function subtractCredits(int $userId, float $amount, ?int $adminId = null, ?string $reason = null): array
    {
        $current = $this->getUser($userId);

        if (!$this->isSuccess($current)) {
            return array_merge($current, ['credits_before' => null, 'credits_after' => null]);
        }

        $creditsBefore = (float) ($current['data']['credits'] ?? 0);
        $creditsAfter  = $creditsBefore - $amount;

        $result = $this->editUser($userId, ['credits' => $creditsAfter]);

        if ($this->isSuccess($result)) {
            $this->writeCreditLog($userId, -$amount, $adminId, $reason);
        }

        return array_merge($result, [
            'credits_before' => $creditsBefore,
            'credits_after'  => $creditsAfter,
        ]);
    }

    /**
     * Enriquece o registro mais recente de credit_log do XUI com reason e admin_id.
     * Busca via API credit_logs, encontra o mais recente sem reason, e atualiza via edit_user
     * (a API não tem endpoint de escrita para credit_logs, então logamos apenas).
     * O XUI grava automaticamente o log quando edit_user é chamado.
     */
    private function writeCreditLog(int $targetId, float $amount, ?int $adminId, ?string $reason): void
    {
        if ($reason === null && $adminId === null) {
            return;
        }

        try {
            // Buscar logs recentes via API para confirmar que o XUI gravou
            $logsResp = $this->getCreditLogs($targetId);
            $logs     = $logsResp['data'] ?? [];

            $recent = collect($logs)
                ->filter(fn($l) => empty($l['reason']) || $l['reason'] === null)
                ->sortByDesc('id')
                ->first();

            if (!$recent) {
                Log::warning('XuiApiService: nenhum CreditLog recente encontrado via API', [
                    'target_id' => $targetId,
                    'amount'    => $amount,
                    'reason'    => $reason,
                ]);
            } else {
                Log::info('XuiApiService: CreditLog confirmado via API', [
                    'log_id'    => $recent['id'] ?? null,
                    'target_id' => $targetId,
                    'amount'    => $amount,
                    'reason'    => $reason,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('XuiApiService: falha ao verificar CreditLog via API', [
                'target_id' => $targetId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Autenticar usuário via API.
     * Busca o usuário pelo username e valida status.
     * A senha não é verificada pela API — a validação de senha é feita pelo
     * sistema de autenticação do Painelshark (PanelUser).
     */
    public function authenticateUser(string $username): ?array
    {
        $response = $this->getUsers();

        if (!isset($response['data']) || !is_array($response['data'])) {
            Log::warning('XUI Auth - Resposta inválida', ['username' => $username]);
            return null;
        }

        foreach ($response['data'] as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                if ((int)($user['status'] ?? 1) !== 1) {
                    Log::warning('XUI Auth - Usuário inativo', ['username' => $username]);
                    return null;
                }
                return $user;
            }
        }

        Log::warning('XUI Auth - Usuário não encontrado', ['username' => $username]);
        return null;
    }

    // -------------------------------------------------------------------------
    // Monitoramento
    // -------------------------------------------------------------------------

    public function getLiveConnections(): array
    {
        return $this->get('live_connections');
    }

    public function killConnection(int $activityId): array
    {
        return $this->post('kill_connection', ['activity_id' => $activityId]);
    }

    public function getActivityLogs(int $limit = 100, int $offset = 0): array
    {
        return $this->get('activity_logs', ['limit' => $limit, 'offset' => $offset]);
    }

    public function getCreditLogs(?int $userId = null): array
    {
        $params = $userId !== null ? ['user_id' => $userId] : [];
        return $this->get('credit_logs', $params);
    }

    public function getClientLogs(?int $lineId = null): array
    {
        $params = $lineId !== null ? ['line_id' => $lineId] : [];
        return $this->get('client_logs', $params);
    }

    public function getUserLogs(?int $userId = null): array
    {
        $params = $userId !== null ? ['user_id' => $userId] : [];
        return $this->get('user_logs', $params);
    }

    public function getLoginLogs(?int $success = null): array
    {
        $params = $success !== null ? ['success' => $success] : [];
        return $this->get('login_logs', $params);
    }

    public function getStreamErrors(?int $streamId = null): array
    {
        $params = $streamId !== null ? ['stream_id' => $streamId] : [];
        return $this->get('stream_errors', $params);
    }

    public function getSystemLogs(int $limit = 100): array
    {
        return $this->get('system_logs', ['limit' => $limit]);
    }

    // -------------------------------------------------------------------------
    // Servidores / Streams
    // -------------------------------------------------------------------------

    /**
     * Listar servidores.
     * ATENÇÃO: retorna array direto, sem wrapper STATUS_SUCCESS.
     */
    public function getServers(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'api_key' => $this->apiKey,
                    'action'  => 'get_servers',
                ]);

            $data = $response->json();
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error('XUI API exceção', ['action' => 'get_servers', 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function getServerStats(): array
    {
        return $this->get('get_server_stats');
    }

    public function getSettings(): array
    {
        return $this->get('get_settings');
    }

    /**
     * Listar bouquets.
     * ATENÇÃO: retorna array direto, sem wrapper STATUS_SUCCESS.
     */
    public function getBouquets(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, ['api_key' => $this->apiKey, 'action' => 'get_bouquets']);
            $data = $response->json();
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error('XUI API exceção', ['action' => 'get_bouquets', 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Listar pacotes.
     * ATENÇÃO: retorna array direto, sem wrapper STATUS_SUCCESS.
     */
    public function getPackages(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, ['api_key' => $this->apiKey, 'action' => 'get_packages']);
            $data = $response->json();
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error('XUI API exceção', ['action' => 'get_packages', 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Listar categorias.
     * ATENÇÃO: retorna array direto, sem wrapper STATUS_SUCCESS.
     * Filtrar por type: 'live', 'movie', 'series'
     */
    public function getCategories(?string $type = null): array
    {
        try {
            $params = ['api_key' => $this->apiKey, 'action' => 'get_categories'];
            if ($type !== null) {
                $params['type'] = $type;
            }
            $response = Http::timeout($this->timeout)->get($this->baseUrl, $params);
            $data = $response->json();
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error('XUI API exceção', ['action' => 'get_categories', 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Atualizar configurações do XUI.
     * Usa POST edit_settings ou update_settings — verificar qual existe.
     */
    public function updateSettings(array $data): array
    {
        return $this->post('edit_settings', $data);
    }

    public function getStreams(): array
    {
        return $this->get('get_streams');
    }

    public function getChannels(): array
    {
        return $this->get('get_channels');
    }

    public function getMovies(): array
    {
        return $this->get('get_movies');
    }

    public function getSeriesList(): array
    {
        return $this->get('get_series_list');
    }

    public function startStream(int $id): array
    {
        return $this->post('start_stream', ['id' => $id]);
    }

    public function stopStream(int $id): array
    {
        return $this->post('stop_stream', ['id' => $id]);
    }

    public function reloadNginx(): array
    {
        return $this->post('reload_nginx');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Normaliza bouquets para o formato aceito pela API: bouquets_selected[]=1&bouquets_selected[]=2
     * Remove chaves alternativas (bouquet_ids, bouquet) e converte para array indexado.
     */
    private function normalizeBouquets(array $data): array
    {
        $ids = null;

        if (isset($data['bouquets_selected'])) {
            $ids = $data['bouquets_selected'];
            unset($data['bouquets_selected']);
        } elseif (isset($data['bouquet_ids'])) {
            $ids = $data['bouquet_ids'];
            unset($data['bouquet_ids']);
        } elseif (isset($data['bouquet'])) {
            $raw = $data['bouquet'];
            $ids = is_string($raw) ? json_decode($raw, true) : $raw;
            unset($data['bouquet']);
        }

        if ($ids !== null && is_array($ids)) {
            $data['bouquets_selected'] = array_values(array_map('intval', $ids));
        }

        return $data;
    }

    /**
     * Garante que exp_date seja sempre no formato YYYY-MM-DD aceito pelo XUI.
     * O XUI v1.5.12 rejeita Unix timestamp com STATUS_INVALID_DATE.
     */
    private function normalizeExpDate(array $data): array
    {
        if (!isset($data['exp_date'])) {
            return $data;
        }

        $val = $data['exp_date'];

        if (is_numeric($val)) {
            $data['exp_date'] = date('Y-m-d', (int) $val);
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
            $ts = strtotime($val);
            $data['exp_date'] = $ts ? date('Y-m-d', $ts) : $val;
        }

        return $data;
    }
}
