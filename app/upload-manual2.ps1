$files = @(
    @("routes\web.php", "/var/www/painel-xui/routes/web.php"),
    @("resources\views\clients\index.blade.php", "/var/www/painel-xui/resources/views/clients/index.blade.php")

)

foreach ($file in $files) {
    $local = $file[0]
    $remote = $file[1]
    Write-Host "Enviando $local para $remote..."
    type $local | ssh root@5.189.164.31 "cat > $remote"
}

Write-Host "Arquivos enviados com sucesso!"