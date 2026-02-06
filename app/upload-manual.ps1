$baseDir = $PSScriptRoot
Write-Host "Diretório base do script: $baseDir"

# Lista de arquivos para upload - Teste de Canais e Revenda Fantasma
# Formato: @("caminho\local\relativo", "/caminho/remoto/absoluto")
$files = @(
    # Backend e Serviços
    @("app\Http\Controllers\SettingsController.php", "/var/www/painel-xui/app/Http/Controllers/SettingsController.php"),
    @("app\Http\Controllers\ChannelTestController.php", "/var/www/painel-xui/app/Http/Controllers/ChannelTestController.php"),
    @("app\Http\Controllers\MaintenanceController.php", "/var/www/painel-xui/app/Http/Controllers/MaintenanceController.php"),
    @("app\Services\XuiApiService.php", "/var/www/painel-xui/app/Services/XuiApiService.php"),
    @("app\Services\ChannelService.php", "/var/www/painel-xui/app/Services/ChannelService.php"),
    @("app\Models\TestChannel.php", "/var/www/painel-xui/app/Models/TestChannel.php"),
    @("app\Http\Middleware\CheckMaintenance.php", "/var/www/painel-xui/app/Http/Middleware/CheckMaintenance.php"),
    
    # Comandos e Console
    @("app\Console\Commands\UpdateGhostClient.php", "/var/www/painel-xui/app/Console/Commands/UpdateGhostClient.php"),
    @("routes\console.php", "/var/www/painel-xui/routes/console.php"),
    @("bootstrap\app.php", "/var/www/painel-xui/bootstrap/app.php"),

    # Rotas e Migrations
    @("routes\web.php", "/var/www/painel-xui/routes/web.php"),
    @("database\migrations\2026_02_06_024241_create_test_channels_table.php", "/var/www/painel-xui/database/migrations/2026_02_06_024241_create_test_channels_table.php"),
    
    # Views - Configurações e Manutenção
    @("resources\views\settings\index.blade.php", "/var/www/painel-xui/resources/views/settings/index.blade.php"),
    @("resources\views\maintenance\index.blade.php", "/var/www/painel-xui/resources/views/maintenance/index.blade.php"),
    @("resources\views\errors\maintenance.blade.php", "/var/www/painel-xui/resources/views/errors/maintenance.blade.php"),
    
    # Views - Teste de Canais
    @("resources\views\channel-test\index.blade.php", "/var/www/painel-xui/resources/views/channel-test/index.blade.php"),

    # Views - Layout (Menu Sidebar)
    @("resources\views\layouts\app.blade.php", "/var/www/painel-xui/resources/views/layouts/app.blade.php")
)

foreach ($file in $files) {
    $relativePath = $file[0]
    $remotePath = $file[1]
    
    # Construir caminho absoluto local corretamente usando Join-Path
    $localPath = Join-Path -Path $baseDir -ChildPath $relativePath
    
    Write-Host "Processando: $relativePath"
    
    if (Test-Path $localPath) {
        Write-Host "  -> Enviando via SCP para $remotePath..."
        
        # Usar scp com caminhos entre aspas para evitar problemas com espaços
        # -B (batch mode) previne perguntas interativas se possível
        # Nota: O usuário precisará digitar a senha se não tiver chave configurada
        
        $process = Start-Process scp -ArgumentList "`"$localPath`"", "root@5.189.164.31:$remotePath" -NoNewWindow -PassThru -Wait
        
        if ($process.ExitCode -eq 0) {
            Write-Host "  [OK] Enviado com sucesso." -ForegroundColor Green
        } else {
            Write-Host "  [ERRO] Falha no SCP. Código de saída: $($process.ExitCode)" -ForegroundColor Red
        }
    } else {
        Write-Warning "  [AVISO] Arquivo local não encontrado: $localPath"
    }
}

Write-Host "`nOperação concluída!"
