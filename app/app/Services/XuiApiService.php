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
        return $this->get('get_lines', ['limit' => 100000]);
    }

    public function getLine(int $id): array
    {
        return $this->get('get_line', ['id' => $id]);
    }

    public function createLine(array $data): array
    {
        $data = $this->normalizeBouquets($data);
        $data = $this->normalizeExpDate($data);
        $data = $this->normalizeIsTrial($data);
        $data = $this->normalizeAccessOutput($data);
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
        $data = $this->normalizeIsTrial($data);
        $data = $this->normalizeAccessOutput($data);
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
        return $this->get('get_users', ['limit' => 100000]);
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
     * Adicionar créditos a um revendedor via adjust_credits.
     * Usa a API adjust_credits que grava corretamente em users_credits_logs
     * com amount = movimentação real e reason preenchido.
     *
     * @param int    $userId   ID do revendedor que recebe os créditos
     * @param float  $amount   Quantidade a adicionar (positivo)
     * @param int|null $adminId  (ignorado — adjust_credits usa o admin autenticado)
     * @param string|null $reason  Motivo da operação
     */
    public function addCredits(int $userId, float $amount, ?int $adminId = null, ?string $reason = null): array
    {
        $current = $this->getUser($userId);

        if (!$this->isSuccess($current)) {
            return array_merge($current, ['credits_before' => null, 'credits_after' => null]);
        }

        $creditsBefore = (float) ($current['data']['credits'] ?? 0);
        $result = $this->adjustCredits($userId, $amount, $reason);
        $creditsAfter  = $creditsBefore + $amount;

        return array_merge($result, [
            'credits_before' => $creditsBefore,
            'credits_after'  => $creditsAfter,
        ]);
    }

    /**
     * Subtrair créditos de um revendedor via adjust_credits.
     * Usa a API adjust_credits que grava corretamente em users_credits_logs
     * com amount = movimentação real (negativo) e reason preenchido.
     *
     * @param int    $userId   ID do revendedor que perde os créditos
     * @param float  $amount   Quantidade a subtrair (positivo)
     * @param int|null $adminId  (ignorado — adjust_credits usa o admin autenticado)
     * @param string|null $reason  Motivo da operação
     */
    public function subtractCredits(int $userId, float $amount, ?int $adminId = null, ?string $reason = null): array
    {
        $current = $this->getUser($userId);

        if (!$this->isSuccess($current)) {
            return array_merge($current, ['credits_before' => null, 'credits_after' => null]);
        }

        $creditsBefore = (float) ($current['data']['credits'] ?? 0);
        $result = $this->adjustCredits($userId, -$amount, $reason);
        $creditsAfter  = $creditsBefore - $amount;

        return array_merge($result, [
            'credits_before' => $creditsBefore,
            'credits_after'  => $creditsAfter,
        ]);
    }

    /**
     * Ajustar créditos via API adjust_credits.
     * Este é o ÚNICO endpoint do XUI que grava corretamente em users_credits_logs
     * com amount = movimentação real e reason preenchido.
     *
     * @param int    $userId   ID do usuário (reseller)
     * @param float  $credits  Valor a adicionar (positivo) ou subtrair (negativo)
     * @param string|null $reason  Motivo do ajuste
     */
    public function adjustCredits(int $userId, float $credits, ?string $reason = null): array
    {
        $params = [
            'id'      => $userId,
            'credits' => $credits,
        ];
        if ($reason !== null) {
            $params['reason'] = $reason;
        }

        return $this->post('adjust_credits', $params);
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
                    Log::warning('XUI Auth - Usuário inativo', [
                        'username' => $username,
                        'status' => $user['status'] ?? 'null'
                    ]);
                    return null;
                }

                // Buscar detalhes completos do usuário para obter a senha (hash)
                if (isset($user['id'])) {
                    $details = $this->getUser((int)$user['id']);
                    if (isset($details['data']) && is_array($details['data'])) {
                        // Mesclar dados da lista com detalhes (detalhes têm prioridade)
                        return array_merge($user, $details['data']);
                    }
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
        return $this->get('live_connections', ['limit' => 100000]);
    }

    public function killConnection(int $activityId): array
    {
        return $this->post('kill_connection', ['activity_id' => $activityId]);
    }

    public function getActivityLogs(int $limit = 100, int $offset = 0): array
    {
        return $this->get('activity_logs', ['limit' => $limit, 'offset' => $offset]);
    }

    public function getCreditLogs(?int $resellerId = null): array
    {
        $params = ['limit' => 100000];
        if ($resellerId !== null) $params['reseller'] = $resellerId;
        return $this->get('credit_logs', $params);
    }

    public function getClientLogs(?int $lineId = null): array
    {
        $params = ['limit' => 100000];
        if ($lineId !== null) $params['line_id'] = $lineId;
        return $this->get('client_logs', $params);
    }

    public function getUserLogs(?int $resellerId = null): array
    {
        $params = ['limit' => 100000];
        if ($resellerId !== null) $params['reseller'] = $resellerId;
        return $this->get('user_logs', $params);
    }

    public function getLoginLogs(?int $success = null): array
    {
        $params = ['limit' => 100000];
        if ($success !== null) $params['success'] = $success;
        return $this->get('login_logs', $params);
    }

    public function getStreamErrors(?int $streamId = null): array
    {
        $params = ['limit' => 100000];
        if ($streamId !== null) $params['stream_id'] = $streamId;
        return $this->get('stream_errors', $params);
    }

    public function getSystemLogs(int $limit = 100): array
    {
        return $this->get('system_logs', ['limit' => $limit]);
    }

    // -------------------------------------------------------------------------
    // Tickets
    // -------------------------------------------------------------------------

    public function getTickets(array $params = []): array
    {
        // params: limit, offset, member_id (opcional)
        return $this->get('get_tickets', $params);
    }

    public function getTicket(int $ticketId): array
    {
        // O endpoint pode variar, mas geralmente get_tickets com id funciona ou get_ticket
        // Se não houver get_ticket, usamos get_tickets filtrando
        return $this->get('get_ticket', ['ticket_id' => $ticketId]);
    }

    public function createTicket(string $subject, string $content, int $memberId): array
    {
        return $this->post('create_ticket', [
            'subject' => $subject,
            'content' => $content,
            'member_id' => $memberId
        ]);
    }

    public function replyTicket(int $ticketId, string $content, int $adminReply = 0): array
    {
        return $this->post('reply_ticket', [
            'ticket_id' => $ticketId,
            'content' => $content,
            'admin_reply' => $adminReply
        ]);
    }

    public function closeTicket(int $ticketId): array
    {
        return $this->post('close_ticket', ['ticket_id' => $ticketId]);
    }

    // -------------------------------------------------------------------------
    // Servidores / Streams
    // -------------------------------------------------------------------------

    /**
     * Listar servidores.
     * Retorna array de servidores (extraído de data se wrapper STATUS_SUCCESS).
     */
    public function getServers(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'api_key' => $this->apiKey,
                    'action'  => 'get_servers',
                ]);

            $json = $response->json();
            if (!is_array($json)) return [];

            if (isset($json['data']) && is_array($json['data'])) {
                return array_values($json['data']);
            }

            return array_values($json);
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

    public function getStream(int $id): array
    {
        return $this->get('get_stream', ['id' => $id]);
    }

    public function getStreams(): array
    {
        return $this->get('get_streams', ['limit' => 100000]);
    }

    public function getChannels(): array
    {
        return $this->get('get_channels', ['limit' => 100000]);
    }

    public function getMovies(): array
    {
        return $this->get('get_movies', ['limit' => 100000]);
    }

    public function getSeriesList(): array
    {
        return $this->get('get_series_list', ['limit' => 100000]);
    }

    public function startStream(int $id, ?int $serverId = null): array
    {
        $params = ['id' => $id];
        if ($serverId !== null) $params['server_id'] = $serverId;
        return $this->post('start_stream', $params);
    }

    public function stopStream(int $id, ?int $serverId = null): array
    {
        $params = ['id' => $id];
        if ($serverId !== null) $params['server_id'] = $serverId;
        return $this->post('stop_stream', $params);
    }

    public function reloadNginx(): array
    {
        return $this->post('reload_nginx');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Normaliza bouquets para o formato aceito pela API: JSON string.
     * O XUI faz json_decode($rData['bouquets_selected'], true) internamente,
     * então o valor deve ser uma string JSON como "[1,2,3]".
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

        if ($ids !== null) {
            if (is_string($ids)) {
                $decoded = json_decode($ids, true);
                if (is_array($decoded)) {
                    $ids = $decoded;
                }
            }
            if (is_array($ids)) {
                $data['bouquets_selected'] = json_encode(array_values(array_map('intval', $ids)));
            }
        }

        return $data;
    }

    /**
     * Garante que exp_date seja no formato YYYY-MM-DD HH:MM:SS aceito pelo XUI.
     * O XUI usa new DateTime($val)->format('U') internamente, aceitando qualquer
     * formato válido do DateTime do PHP. Preservamos hora/minuto/segundo.
     */
    private function normalizeExpDate(array $data): array
    {
        if (!isset($data['exp_date'])) {
            return $data;
        }

        $val = $data['exp_date'];

        if (is_numeric($val)) {
            $data['exp_date'] = date('Y-m-d H:i:s', (int) $val);
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
            $ts = strtotime($val);
            $data['exp_date'] = $ts ? date('Y-m-d H:i:s', $ts) : $val;
        }

        return $data;
    }

    /**
     * Remove is_trial, is_restreamer, is_stalker, is_isplock, bypass_ua do payload
     * quando o valor é falsy. O XUI usa isset() para esses campos — se o campo
     * existir no POST (mesmo com valor 0), o XUI define como 1.
     */
    private function normalizeIsTrial(array $data): array
    {
        $boolFields = ['is_trial', 'is_restreamer', 'is_stalker', 'is_isplock', 'bypass_ua'];

        foreach ($boolFields as $field) {
            if (array_key_exists($field, $data)) {
                if (empty($data[$field]) || $data[$field] === '0' || $data[$field] === 0) {
                    unset($data[$field]);
                } else {
                    $data[$field] = 1;
                }
            }
        }

        return $data;
    }

    /**
     * Converte allowed_outputs para access_output (nome correto do campo na API XUI).
     * O XUI espera access_output como array PHP: access_output[]=1&access_output[]=2
     */
    private function normalizeAccessOutput(array $data): array
    {
        $raw = null;

        if (isset($data['access_output'])) {
            $raw = $data['access_output'];
        } elseif (isset($data['allowed_outputs'])) {
            $raw = $data['allowed_outputs'];
            unset($data['allowed_outputs']);
        }

        if ($raw !== null) {
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $raw = is_array($decoded) ? $decoded : [$raw];
            }
            if (is_array($raw) && !empty($raw)) {
                $data['access_output'] = array_values(array_map('intval', $raw));
            }
        }

        return $data;
    }
}
