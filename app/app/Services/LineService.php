<?php

namespace App\Services;

use App\Models\Bouquet;
use App\Models\CreditLog;
use App\Models\Line;
use App\Models\Package;
use App\Models\XuiUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class LineService
{
    /**
     * Cria uma linha (Teste ou Oficial) baseada no Pacote.
     */
    public function createLine(array $data)
    {
        return DB::connection('xui')->transaction(function () use ($data) {
            
            // 1. Carregar Configurações
            $package = Package::findOrFail($data['package_id']);
            $owner = XuiUser::lockForUpdate()->findOrFail($data['member_id']);

            // 2. Definir Regras (Trial vs Oficial)
            $isTrial = $data['is_trial'] ?? false;

            if ($isTrial) {
                $cost = $package->trial_credits ?? 0;
                $duration = $package->trial_duration ?? 24;
                $unit = $package->trial_duration_in ?? 'hours';
            } else {
                $cost = $package->official_credits ?? 1;
                $duration = $package->official_duration ?? 30;
                $unit = $package->official_duration_in ?? 'days';
            }

            // 3. Validações
            if (Line::where('username', $data['username'])->exists()) {
                throw new Exception("O usuário '{$data['username']}' já existe.");
            }

            // Verifica Saldo (Apenas se tiver custo e não for Admin)
            if ($cost > 0 && !$owner->isAdmin()) {
                if ($owner->credits < $cost) {
                    throw new Exception("Saldo insuficiente. Necessário: {$cost}. Atual: {$owner->credits}");
                }
            }

            // 4. Calcular Expiração (Timestamp Unix)
            $expirationDate = $this->calculateExpiration(time(), $duration, $unit);

            // 5. Preparar Bouquets (Canais)
            $bouquets = isset($data['bouquet_ids']) 
                ? json_encode($data['bouquet_ids']) 
                : $package->bouquets;

            // 6. AÇÃO A: Criar a Linha
            $line = Line::create([
                'member_id' => $owner->id,
                'username' => $data['username'],
                'password' => $data['password'],
                'exp_date' => $expirationDate,
                'admin_enabled' => 1,
                'enabled' => 1,
                'bouquet' => $bouquets,
                'max_connections' => $data['max_connections'] ?? $package->max_connections ?? 1,
                'is_trial' => $isTrial ? 1 : 0,
                'created_at' => time(),
                'contact' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'admin_notes' => !empty($data['notes']) ? $data['notes'] : 'Criado via Painel Office',
                'reseller_notes' => $data['reseller_notes'] ?? ($isTrial ? 'Teste' : 'Cliente oficial'),
                'allowed_ips' => '[]',
                'allowed_ua' => '[]',
                'package_id' => $package->id,
            ]);

            // 7. AÇÃO B: Debitar Créditos (Se houver custo e não for Admin)
            if ($cost > 0 && !$owner->isAdmin()) {
                $owner->decrement('credits', $cost);

                // 8. AÇÃO C: Log Financeiro (credit_logs)
                CreditLog::create([
                    'target_id' => $owner->id,
                    'admin_id' => $owner->id,
                    'amount' => -$cost,
                    'date' => time(),
                    'reason' => "Criação de Linha: {$line->username} (Pacote: {$package->package_name})",
                ]);
            }

            return $line;
        });
    }

    /**
     * Renova uma linha existente
     */
    public function renewLine(int $lineId, array $data)
    {
        return DB::connection('xui')->transaction(function () use ($lineId, $data) {
            
            // 1. Carregar Linha, Pacote e Dono
            $line = Line::lockForUpdate()->findOrFail($lineId);
            $owner = XuiUser::lockForUpdate()->findOrFail($line->member_id);
            $package = Package::findOrFail($data['package_id']);

            // 2. Definir Custo do Pacote
            $cost = $package->official_credits ?? 0;

            // 3. Validação de Saldo (Apenas se não for Admin)
            if (!$owner->isAdmin()) {
                if ($owner->credits < $cost) {
                    throw new Exception("Saldo insuficiente. Necessário: {$cost}. Atual: {$owner->credits}");
                }
            }

            // 4. Calcular Nova Data de Expiração
            $currentExpTimestamp = $line->exp_date;
            $now = time();
            
            // Se a linha já expirou, renovar a partir de agora
            // Se ainda está ativa, adicionar ao vencimento atual
            $baseTimestamp = $currentExpTimestamp > $now ? $currentExpTimestamp : $now;
            $newExpDate = $this->calculateExpiration($baseTimestamp, $data['duration_value'], $data['duration_unit']);

            // 5. AÇÃO A: Atualizar a Linha
            $updateData = [
                'exp_date' => $newExpDate,
                'enabled' => 1,
                'admin_enabled' => 1,
                'package_id' => $data['package_id'],
                'max_connections' => $data['max_connections'],
                'bouquet' => $line->bouquet, // Manter bouquets atuais
            ];

            // Se renovar um teste com pacote oficial, converter para cliente oficial
            if ($line->is_trial == 1 && $package->is_official == 1) {
                $updateData['is_trial'] = 0;
            }

            $line->update($updateData);

            // 6. AÇÃO B: Debitar Créditos (Se não for Admin)
            if (!$owner->isAdmin()) {
                $owner->decrement('credits', $cost);

                // 7. AÇÃO C: Log Financeiro
                $durationText = $data['duration_value'] . ' ' . $data['duration_unit'];
                CreditLog::create([
                    'target_id' => $owner->id,
                    'admin_id' => $owner->id,
                    'amount' => -$cost,
                    'date' => time(),
                    'reason' => "Renovação de Linha: {$line->username} (+{$durationText})",
                ]);
            }

            return $line;
        });
    }

    /**
     * Helper para calcular data futura baseada na unidade do XUI
     */
    private function calculateExpiration($baseTimestamp, $duration, $unit)
    {
        $date = Carbon::createFromTimestamp($baseTimestamp);
        $duration = (int) $duration; // Converter para int

        switch (strtolower($unit)) {
            case 'hours':
            case 'hour':
                $date->addHours($duration);
                break;
            case 'days':
            case 'day':
                $date->addDays($duration);
                break;
            case 'months':
            case 'month':
                $date->addMonths($duration);
                break;
            case 'years':
            case 'year':
                $date->addYears($duration);
                break;
            default:
                $date->addDays($duration); // Fallback seguro
        }

        return $date->timestamp;
    }

    /**
     * Gera URLs M3U para um cliente, usando DNS do revendedor se disponível
     */
    public function generateM3uUrls(Line $line)
    {
        // Buscar o revendedor (dono da linha)
        $reseller = XuiUser::find($line->member_id);
        
        // Verificar se o revendedor tem DNS configurado
        $dns = null;
        if ($reseller && !empty($reseller->reseller_dns)) {
            $dns = $reseller->reseller_dns;
        }
        
        // Se não tiver DNS do revendedor, usar o DNS do Main Server
        if (!$dns) {
            $mainServer = DB::connection('xui')
                ->table('servers')
                ->where('is_main', 1)
                ->first();
            
            if ($mainServer && !empty($mainServer->server_ip)) {
                $dns = $mainServer->server_ip;
            } else {
                // Fallback para IP padrão
                $dns = '109.205.178.143';
            }
        }

        // Remover protocolo se vier junto
        $dns = str_replace(['http://', 'https://'], '', $dns);

        // Gerar URLs
        $protocol = 'http';
        $port = '80';

        $m3uUrl = sprintf(
            '%s://%s:%s/get.php?username=%s&password=%s&type=m3u_plus&output=ts',
            $protocol,
            $dns,
            $port,
            $line->username,
            $line->password
        );

        $hlsUrl = sprintf(
            '%s://%s:%s/get.php?username=%s&password=%s&type=m3u_plus&output=m3u8',
            $protocol,
            $dns,
            $port,
            $line->username,
            $line->password
        );

        return [
            'm3u_url' => $m3uUrl,
            'hls_url' => $hlsUrl,
            'dns' => $dns,
        ];
    }
}
