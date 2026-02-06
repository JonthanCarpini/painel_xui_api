$ErrorActionPreference = "Stop"
$baseDir = $PSScriptRoot
$tempDir = Join-Path $baseDir "temp_deploy"
$zipFile = Join-Path $baseDir "deploy.zip"
$remoteHost = "root@5.189.164.31"
$remotePath = "/var/www/painel-xui"

Write-Host "=== Preparando Deploy Otimizado ===" -ForegroundColor Cyan

# 1. Limpar arquivos temporários anteriores
if (Test-Path $tempDir) { Remove-Item -Recurse -Force $tempDir }
if (Test-Path $zipFile) { Remove-Item -Force $zipFile }

# 2. Lista de arquivos para deploy (Caminhos relativos à raiz do projeto)
$files = @(
    "app\Http\Controllers\SettingsController.php",
    "app\Http\Controllers\ChannelTestController.php",
    "app\Http\Controllers\MaintenanceController.php",
    "app\Http\Controllers\ClientController.php",
    "app\Http\Controllers\DashboardController.php",
    "app\Http\Controllers\CreditLogController.php",
    "app\Http\Controllers\ResellerController.php",
    "app\Http\Controllers\Admin\ChannelController.php",
    "app\Http\Controllers\Admin\ServerController.php",
    "app\Services\XuiApiService.php",
    "app\Services\ChannelService.php",
    "app\Services\LineService.php",
    "app\Models\TestChannel.php",
    "app\Models\ClientDetail.php",
    "app\Models\UserLog.php",
    "app\Models\Notice.php",
    "app\Models\Ticket.php",
    "app\Models\TicketCategory.php",
    "app\Models\TicketExtra.php",
    "app\Models\TicketReply.php",
    "app\Http\Middleware\CheckMaintenance.php",
    "app\Console\Commands\UpdateGhostClient.php",
    "routes\console.php",
    "bootstrap\app.php",
    "routes\web.php",
    "database\migrations\2026_02_06_024241_create_test_channels_table.php",
    "database\migrations\2026_02_06_000005_create_client_details_table.php",
    "database\migrations\2026_02_06_170000_add_color_to_notices_and_type_to_test_channels.php",
    "resources\views\settings\index.blade.php",
    "resources\views\maintenance\index.blade.php",
    "resources\views\dashboard\index.blade.php",
    "resources\views\clients\index.blade.php",
    "resources\views\clients\partials\table.blade.php",
    "resources\views\clients\partials\pagination.blade.php",
    "resources\views\credit-logs\index.blade.php",
    "resources\views\credit-logs\resellers.blade.php",
    "resources\views\errors\maintenance.blade.php",
    "resources\views\errors\reseller_blocked.blade.php",
    "resources\views\channel-test\index.blade.php",
    "resources\views\layouts\app.blade.php",
    "resources\views\admin\channels\index.blade.php",
    "resources\views\admin\channels\edit.blade.php",
    "resources\views\admin\servers\index.blade.php",
    "resources\views\admin\servers\show.blade.php",
    "resources\views\tickets\show.blade.php",
    "resources\views\clients\export.blade.php",
    "resources\views\notices\index.blade.php"
)

# 3. Copiar arquivos para estrutura temporária
Write-Host "Copiando arquivos para diretório temporário..."
foreach ($file in $files) {
    $sourcePath = Join-Path $baseDir $file
    $destPath = Join-Path $tempDir $file
    $destDir = Split-Path $destPath -Parent

    if (Test-Path $sourcePath) {
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Force -Path $destDir | Out-Null
        }
        Copy-Item $sourcePath $destPath
        Write-Host "  [OK] $file" -ForegroundColor Gray
    } else {
        Write-Warning "Arquivo não encontrado: $file"
    }
}

# 4. Criar ZIP
Write-Host "`nCompactando arquivos..."
Compress-Archive -Path "$tempDir\*" -DestinationPath $zipFile -Force
Write-Host "Arquivo ZIP criado: $zipFile" -ForegroundColor Green

# 5. Enviar ZIP via SCP
Write-Host "`n=== Enviando Arquivo (Digite a senha do servidor se solicitado) ===" -ForegroundColor Yellow
$scpCommand = "scp '$zipFile' ${remoteHost}:/tmp/deploy.zip"
Invoke-Expression $scpCommand

if ($LASTEXITCODE -eq 0) {
    Write-Host "Upload do ZIP concluído com sucesso." -ForegroundColor Green
} else {
    Write-Error "Falha no upload do ZIP."
    exit 1
}

# 6. Descompactar no servidor via SSH
Write-Host "`n=== Instalando no Servidor (Digite a senha novamente se solicitado) ===" -ForegroundColor Yellow
# Comando remoto: instala unzip se necessário, descompacta sobrescrevendo, ajusta permissões e remove o zip
$remoteCommands = "
    if ! command -v unzip &> /dev/null; then apt-get update && apt-get install -y unzip; fi
    unzip -o /tmp/deploy.zip -d $remotePath
    rm /tmp/deploy.zip
    chown -R www-data:www-data $remotePath
    chmod -R 755 $remotePath
    
    echo 'Executando comandos Artisan e Limpeza...'
    cd $remotePath
    php artisan migrate --force
    php artisan optimize:clear
    php artisan view:clear
    php artisan route:clear
    
    # Tentar recarregar PHP-FPM para limpar OPcache
    if systemctl list-units --full -all | grep -Fq 'php'; then
        echo 'Recarregando PHP-FPM...'
        systemctl reload php*-fpm || true
    fi
    
    echo 'Deploy e comandos Artisan concluídos com sucesso!'
"

$sshCommand = "ssh $remoteHost `"$remoteCommands`""
Invoke-Expression $sshCommand

# 7. Limpeza local
Remove-Item -Recurse -Force $tempDir
Remove-Item -Force $zipFile

Write-Host "`n=== Processo Finalizado ===" -ForegroundColor Cyan
