<?php

namespace App\Services;

use App\Models\AppSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class LineService
{
    public function __construct(
        private XuiApiService $api,
        private PackageService $packages,
    ) {}

    /**
     * Cria uma linha (Teste ou Oficial) via API.
     * Estratégia: debita créditos → cria linha → compensa se falhar.
     */
    public function createLine(array $data): array
    {
        $package = $this->packages->findOrFail((int)$data['package_id']);
        $isTrial = (bool)($data['is_trial'] ?? false);
        [$cost, $duration, $unit] = $this->resolvePackageRules($package, $isTrial);

        $ownerResp = $this->api->getUser((int)$data['member_id']);
        if (($ownerResp['status'] ?? '') !== 'STATUS_SUCCESS') {
            throw new Exception('Revendedor não encontrado na API XUI.');
        }
        $owner   = $ownerResp['data'];
        $isAdmin = (int)($owner['member_group_id'] ?? 2) === 1;

        if ($cost > 0 && !$isAdmin) {
            $credits = (float)($owner['credits'] ?? 0);
            if ($credits < $cost) {
                throw new Exception("Saldo insuficiente. Necessário: {$cost}. Atual: {$credits}");
            }
        }

        $expDate      = $this->calculateExpiration(time(), $duration, $unit);
        $bouquetIds   = $this->resolveBouquetIdsFromPackage($package);
        $outputFormats = $this->resolveOutputFormats($package);

        $creditsBefore = null;
        if ($cost > 0 && !$isAdmin) {
            $memberId = (int)$data['member_id'];
            $reason   = "Criação de linha: {$data['username']} (pacote: {$package->package_name})";
            $debit    = $this->api->subtractCredits($memberId, $cost, $memberId, $reason);
            if (($debit['status'] ?? '') !== 'STATUS_SUCCESS') {
                throw new Exception('Falha ao debitar créditos: ' . ($debit['message'] ?? 'erro'));
            }
            $creditsBefore = $debit['credits_before'];
        }

        $payload = [
            'username'        => $data['username'],
            'password'        => $data['password'],
            'member_id'       => (int)$data['member_id'],
            'exp_date'        => $expDate,
            'max_connections' => (int)($package->max_connections ?? 1),
            'is_trial'        => $isTrial ? 1 : 0,
            'contact'         => $data['phone'] ?? '',
            'admin_notes'     => !empty($data['notes']) ? $data['notes'] : 'Criado via Painel Office',
            'reseller_notes'  => $data['reseller_notes'] ?? ($isTrial ? 'Teste' : 'Cliente oficial'),
        ];
        if (!empty($bouquetIds)) {
            $payload['bouquets_selected'] = $bouquetIds;
        }
        if (!empty($outputFormats)) {
            $payload['access_output'] = $outputFormats;
        }

        $result = $this->api->createLine($payload);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            if ($cost > 0 && !$isAdmin && $creditsBefore !== null) {
                $memberId = (int)$data['member_id'];
                $this->api->addCredits($memberId, $cost, $memberId, "Estorno: falha ao criar linha {$data['username']}");
                Log::warning('LineService: créditos devolvidos após falha na criação', ['member_id' => $data['member_id'], 'cost' => $cost]);
            }
            throw new Exception('Falha ao criar linha na API XUI: ' . ($result['message'] ?? 'erro'));
        }

        $lineId       = (int)($result['data']['id'] ?? $result['id'] ?? 0);
        $creditsAfter = ($creditsBefore !== null) ? ($creditsBefore - $cost) : (float)($owner['credits'] ?? 0);

        return ['id' => $lineId, 'username' => $data['username'], 'password' => $data['password'], 'exp_date' => $expDate, 'is_trial' => $isTrial ? 1 : 0, 'member_id' => (int)$data['member_id']];
    }

    /**
     * Renova uma linha existente via API.
     * Estratégia: debita créditos → edita linha → compensa se falhar.
     */
    public function renewLine(int $lineId, array $data): array
    {
        $package = $this->packages->findOrFail((int)$data['package_id']);
        $cost    = (float)($package->official_credits ?? 0);

        $lineResp = $this->api->getLine($lineId);
        if (($lineResp['status'] ?? '') !== 'STATUS_SUCCESS') {
            throw new Exception('Linha não encontrada na API XUI.');
        }
        $line = $lineResp['data'];

        $ownerResp = $this->api->getUser((int)$line['member_id']);
        if (($ownerResp['status'] ?? '') !== 'STATUS_SUCCESS') {
            throw new Exception('Revendedor não encontrado na API XUI.');
        }
        $owner   = $ownerResp['data'];
        $isAdmin = (int)($owner['member_group_id'] ?? 2) === 1;

        if (!$isAdmin) {
            $credits = (float)($owner['credits'] ?? 0);
            if ($credits < $cost) {
                throw new Exception("Saldo insuficiente. Necessário: {$cost}. Atual: {$credits}");
            }
        }

        $now           = time();
        $currentExp    = (int)($line['exp_date'] ?? 0);
        $baseTimestamp = $currentExp > $now ? $currentExp : $now;
        $newExpDate    = $this->calculateExpiration($baseTimestamp, $data['duration_value'], $data['duration_unit']);

        $creditsBefore = null;
        if (!$isAdmin && $cost > 0) {
            $memberId = (int)$line['member_id'];
            $reason   = "Renovação de linha: {$line['username']} (pacote: {$package->package_name})";
            $debit    = $this->api->subtractCredits($memberId, $cost, $memberId, $reason);
            if (($debit['status'] ?? '') !== 'STATUS_SUCCESS') {
                throw new Exception('Falha ao debitar créditos: ' . ($debit['message'] ?? 'erro'));
            }
            $creditsBefore = $debit['credits_before'];
        }

        $editPayload = [
            'username'        => $line['username'],
            'exp_date'        => $newExpDate,
            'max_connections' => $data['max_connections'],
        ];
        if ((int)($line['is_trial'] ?? 0) === 1 && (int)($package->is_official ?? 0) === 1) {
            $editPayload['is_trial'] = 0;
        }

        $editResult = $this->api->editLine($lineId, $editPayload);

        if (($editResult['status'] ?? '') !== 'STATUS_SUCCESS') {
            if (!$isAdmin && $cost > 0 && $creditsBefore !== null) {
                $memberId = (int)$line['member_id'];
                $this->api->addCredits($memberId, $cost, $memberId, "Estorno: falha ao renovar linha {$line['username']}");
                Log::warning('LineService: créditos devolvidos após falha na renovação', ['line_id' => $lineId, 'cost' => $cost]);
            }
            throw new Exception('Falha ao renovar linha na API XUI: ' . ($editResult['message'] ?? 'erro'));
        }

        $durationText = $this->formatDuration($data['duration_value'], $data['duration_unit']);
        $creditsAfter = ($creditsBefore !== null) ? ($creditsBefore - $cost) : (float)($owner['credits'] ?? 0);


        return array_merge($line, ['exp_date' => $newExpDate]);
    }

    // -------------------------------------------------------------------------
    // URLs M3U
    // -------------------------------------------------------------------------

    /**
     * Gera URLs M3U. $line pode ser array (API) ou objeto (Model legado).
     */
    public function generateM3uUrls($line): array
    {
        $memberId = is_array($line) ? ($line['member_id'] ?? null) : $line->member_id;
        $username = is_array($line) ? ($line['username'] ?? '') : $line->username;
        $password = is_array($line) ? ($line['password'] ?? '') : $line->password;

        $dns = $this->resolveServerDns($memberId);

        return [
            'm3u_url' => "http://{$dns}:80/get.php?username={$username}&password={$password}&type=m3u_plus&output=ts",
            'hls_url' => "http://{$dns}:80/get.php?username={$username}&password={$password}&type=m3u_plus&output=m3u8",
            'dns'     => $dns,
        ];
    }

    // -------------------------------------------------------------------------
    // Mensagem ao cliente
    // -------------------------------------------------------------------------

    public function generateClientMessage($line): string
    {
        $expDate  = is_array($line) ? ($line['exp_date'] ?? 0) : $line->exp_date;
        $username = is_array($line) ? ($line['username'] ?? '') : $line->username;
        $password = is_array($line) ? ($line['password'] ?? '') : $line->password;

        $template   = AppSetting::get('client_message_template', '');
        $expiration = date('d/m/Y H:i:s', (int)$expDate);
        $urls       = $this->generateM3uUrls($line);
        $dns        = $urls['dns'];

        if (empty($template)) {
            $message  = "👤 USUÁRIO: {$username}\n";
            $message .= "🔑 SENHA: {$password}\n";
            $message .= "📅 VENCIMENTO: {$expiration}\n\n";
            $message .= "http://{$dns}\n\n";

            $apps = \App\Models\ClientApplication::where('is_active', true)->get();
            foreach ($apps as $app) {
                $message .= "\n📺 {$app->name}\n";
                if ($app->downloader_id)      $message .= "📥 DOWNLOADER: {$app->downloader_id}\n";
                if ($app->direct_link)        $message .= "🔗 Link Direto: {$app->direct_link}\n";
                if ($app->compatible_devices) $message .= "📱 {$app->compatible_devices}\n";
                if ($app->activation_code)    $message .= "🔑 Código: {$app->activation_code}\n";
                if ($app->login_instructions) $message .= "👉 {$app->login_instructions}\n";
            }
            return $message;
        }

        $message = str_replace(
            ['{USERNAME}', '{PASSWORD}', '{EXPIRATION}', '{DNS}', '{M3U_URL}', '{HLS_URL}'],
            [$username, $password, $expiration, $dns, $urls['m3u_url'], $urls['hls_url']],
            $template
        );

        if (strpos($message, '{APPS}') !== false) {
            $appsContent = '';
            $apps = \App\Models\ClientApplication::where('is_active', true)->get();
            foreach ($apps as $app) {
                $appsContent .= "\n📺 {$app->name}\n";
                if ($app->downloader_id)      $appsContent .= "📥 DOWNLOADER: {$app->downloader_id}\n";
                if ($app->direct_link)        $appsContent .= "🔗 Link Direto: {$app->direct_link}\n";
                if ($app->compatible_devices) $appsContent .= "📱 {$app->compatible_devices}\n";
                if ($app->activation_code)    $appsContent .= "🔑 Código: {$app->activation_code}\n";
                if ($app->login_instructions) $appsContent .= "👉 {$app->login_instructions}\n";
            }
            $message = str_replace('{APPS}', $appsContent, $message);
        }

        return $message;
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function resolvePackageRules(object $package, bool $isTrial): array
    {
        if ($isTrial) {
            return [
                (float)($package->trial_credits ?? 0),
                (int)($package->trial_duration ?? 24),
                $package->trial_duration_in ?? 'hours',
            ];
        }
        return [
            (float)($package->official_credits ?? 1),
            (int)($package->official_duration ?? 30),
            $package->official_duration_in ?? 'days',
        ];
    }

    private function resolveBouquetIdsFromPackage(object $package): array
    {
        $raw = $package->bouquets ?? null;
        if ($raw) {
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($decoded)) {
                return array_values(array_map('intval', $decoded));
            }
        }
        return [];
    }

    private function resolveOutputFormats(object $package): array
    {
        $raw = $package->output_formats ?? null;
        if ($raw) {
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($decoded)) {
                return array_values(array_map('intval', $decoded));
            }
        }
        return [1, 2];
    }

    private function resolveServerDns(?int $memberId): string
    {
        if ($memberId) {
            $userResp = $this->api->getUser($memberId);
            if (($userResp['status'] ?? '') === 'STATUS_SUCCESS') {
                $dns = $userResp['data']['reseller_dns'] ?? null;
                if (!empty($dns)) {
                    return str_replace(['http://', 'https://'], '', rtrim($dns, '/'));
                }
            }
        }

        $servers = $this->api->getServers();
        foreach ($servers as $server) {
            if (!empty($server['is_main']) && !empty($server['server_ip'])) {
                return $server['server_ip'];
            }
        }

        return config('xui.fallback_dns', '');
    }

    private function calculateExpiration(int $baseTimestamp, int|string $duration, string $unit): int
    {
        $date     = Carbon::createFromTimestamp($baseTimestamp);
        $duration = (int)$duration;

        switch (strtolower($unit)) {
            case 'hours': case 'hour':   $date->addHours($duration);   break;
            case 'days':  case 'day':    $date->addDays($duration);    break;
            case 'months': case 'month': $date->addMonths($duration);  break;
            case 'years':  case 'year':  $date->addYears($duration);   break;
            default:                     $date->addDays($duration);
        }

        return $date->timestamp;
    }

    private function formatDuration(int|string $duration, string $unit): string
    {
        $map = ['hours' => 'Horas', 'hour' => 'Hora', 'days' => 'Dias', 'day' => 'Dia', 'months' => 'Meses', 'month' => 'Mês', 'years' => 'Anos', 'year' => 'Ano'];
        return $duration . ' ' . ($map[strtolower($unit)] ?? $unit);
    }

}
