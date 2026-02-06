$files = @(
    @("app\Services\LineService.php", "/var/www/painel-xui/app/Services/LineService.php"),
    @("resources\views\clients\index.blade.php", "/var/www/painel-xui/resources/views/clients/index.blade.php"),
    @("app\Models\Package.php", "/var/www/painel-xui/app/Models/Package.php"),
    @("resources\views\clients\create.blade.php", "/var/www/painel-xui/resources/views/clients/create.blade.php"),
    @("resources\views\clients\create-trial.blade.php", "/var/www/painel-xui/resources/views/clients/create-trial.blade.php"),
    @("app\Http\Controllers\SettingsController.php", "/var/www/painel-xui/app/Http/Controllers/SettingsController.php"),
    @("routes\web.php", "/var/www/painel-xui/routes/web.php"),
    @("routes\fix_template.php", "/var/www/painel-xui/routes/fix_template.php"),
    @("app\Http\Controllers\ClientController.php", "/var/www/painel-xui/app/Http/Controllers/ClientController.php"),
    @("app\Models\ClientApplication.php", "/var/www/painel-xui/app/Models/ClientApplication.php"),
    @("app\Models\DnsServer.php", "/var/www/painel-xui/app/Models/DnsServer.php"),
    @("resources\views\settings\index.blade.php", "/var/www/painel-xui/resources/views/settings/index.blade.php"),
    @("database\migrations\2026_02_05_000003_create_apps_and_dns_tables.php", "/var/www/painel-xui/database/migrations/2026_02_05_000003_create_apps_and_dns_tables.php"),
    @("resources\views\dashboard\index.blade.php", "/var/www/painel-xui/resources/views/dashboard/index.blade.php"),
    @("resources\views\layouts\app.blade.php", "/var/www/painel-xui/resources/views/layouts/app.blade.php"),
    @("resources\views\clients\export.blade.php", "/var/www/painel-xui/resources/views/clients/export.blade.php")
)

foreach ($file in $files) {
    $local = $file[0]
    $remote = $file[1]
    
    if (Test-Path $local) {
        Write-Host "Processando $local..."
        # Lê os bytes do arquivo e converte para Base64
        $content = [System.IO.File]::ReadAllBytes($local)
        $base64 = [Convert]::ToBase64String($content)
        
        # Envia o Base64 e decodifica no servidor remoto
        $command = "echo '$base64' | base64 -d > $remote"
        ssh root@5.189.164.31 $command
        Write-Host "  -> Enviado para $remote"
    } else {
        Write-Warning "Arquivo local nao encontrado: $local"
    }
}

Write-Host "Todos os arquivos foram atualizados via Base64!"
