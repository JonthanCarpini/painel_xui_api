<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppSetting;
use App\Services\XuiApiService;
use App\Services\ChannelService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateGhostClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ghost:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotaciona a senha do cliente fantasma e atualiza a lista de canais';

    /**
     * Execute the console command.
     */
    public function handle(XuiApiService $xuiApi, ChannelService $channelService)
    {
        $username = AppSetting::get('ghost_reseller_username');
        
        if (!$username) {
            $this->error('Usuário fantasma não configurado nas configurações.');
            return 1;
        }

        $this->info("Iniciando rotação para o usuário: $username");

        // 1. Buscar ID do usuário no XUI
        $users = $xuiApi->getUsers();
        $targetUser = null;

        if (isset($users['data']) && is_array($users['data'])) {
            foreach ($users['data'] as $user) {
                if ($user['username'] === $username) {
                    $targetUser = $user;
                    break;
                }
            }
        }

        if (!$targetUser) {
            $this->error('Usuário não encontrado no painel XUI.');
            Log::error("Ghost Client Rotation: User $username not found in XUI.");
            return 1;
        }

        // 2. Gerar nova senha
        $newPassword = Str::random(12);

        // 3. Atualizar no XUI
        // Nota: O método editUser espera um array com os dados a serem atualizados
        // Precisamos manter os dados obrigatórios ou o XUI pode reclamar?
        // Geralmente 'password' é suficiente se o endpoint suportar patch, mas o XUI costuma ser chato.
        // Vamos enviar username e password.
        $updateData = [
            'username' => $username,
            'password' => $newPassword,
            'status' => 1 // Garantir que está ativo
        ];

        // Se tivermos mais dados do usuário que precisam ser preservados (como exp_date), seria bom, 
        // mas o XApiService deve lidar com o ID.
        $updateResult = $xuiApi->editUser($targetUser['id'], $updateData);

        if (!$updateResult['result']) {
            $this->error('Falha ao atualizar senha no XUI.');
            Log::error("Ghost Client Rotation: Failed to update password for $username.");
            return 1;
        }

        // 4. Salvar localmente
        AppSetting::set('ghost_reseller_password', $newPassword);
        $this->info("Senha atualizada com sucesso no XUI e banco local.");

        // 5. Sincronizar Canais
        $this->info("Iniciando sincronização de canais...");
        $syncResult = $channelService->syncChannels($username, $newPassword);

        if ($syncResult['success']) {
            $this->info("Canais sincronizados com sucesso. Total: " . $syncResult['count']);
            Log::info("Ghost Client Rotation: Success. Password rotated and channels synced.");
        } else {
            $this->error("Erro na sincronização de canais: " . $syncResult['message']);
            Log::error("Ghost Client Rotation: Channels sync failed. " . $syncResult['message']);
            return 1;
        }

        // 6. Notificar SaaS para atualizar proxy Nginx com novas credenciais
        $this->notifySaasProxyUpdate($username, $newPassword);

        return 0;
    }

    private function notifySaasProxyUpdate(string $username, string $password): void
    {
        $saasApiUrl = env('SAAS_API_URL');
        $instanceToken = env('INSTANCE_TOKEN');

        if (empty($saasApiUrl) || empty($instanceToken)) {
            $this->warn("SAAS_API_URL ou INSTANCE_TOKEN não configurados. Proxy não atualizado.");
            return;
        }

        $url = rtrim($saasApiUrl, '/') . "/api/instance/{$instanceToken}/ghost-credentials";

        try {
            $response = Http::timeout(15)->post($url, [
                'username' => $username,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $this->info("Proxy Nginx atualizado com novas credenciais via SaaS API.");
                Log::info("Ghost Rotation: SaaS proxy updated successfully.");
            } else {
                $this->warn("Falha ao atualizar proxy via SaaS API: HTTP " . $response->status());
                Log::warning("Ghost Rotation: SaaS proxy update failed", ['status' => $response->status()]);
            }
        } catch (\Throwable $e) {
            $this->warn("Erro ao notificar SaaS: " . $e->getMessage());
            Log::warning("Ghost Rotation: SaaS notification failed", ['error' => $e->getMessage()]);
        }
    }
}
