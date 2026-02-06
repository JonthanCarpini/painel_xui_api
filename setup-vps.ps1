# Script PowerShell para configurar VPS
$VPS_IP = "5.189.164.31"
$VPS_USER = "root"

Write-Host "🚀 Configurando VPS..." -ForegroundColor Green

# Comando 4: Criar banco de dados
Write-Host "`n📦 [4/7] Criando banco de dados..." -ForegroundColor Cyan
$cmd4 = @"
mysql -e 'CREATE DATABASE IF NOT EXISTS painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;' && mysql -e 'CREATE USER IF NOT EXISTS painel_user@localhost IDENTIFIED BY \"Painel@2026#Strong\";' && mysql -e 'GRANT ALL PRIVILEGES ON painel_xui.* TO painel_user@localhost;' && mysql -e 'FLUSH PRIVILEGES;' && echo 'Banco criado!'
"@
ssh "$VPS_USER@$VPS_IP" $cmd4

# Comando 5: Criar diretório
Write-Host "`n📁 [5/7] Criando diretório..." -ForegroundColor Cyan
ssh "$VPS_USER@$VPS_IP" "mkdir -p /var/www/painel-xui && echo 'Diretório criado!'"

# Comando 6: Configurar Nginx
Write-Host "`n🌐 [6/7] Configurando Nginx..." -ForegroundColor Cyan
$nginxConfig = @'
server {
    listen 80;
    server_name 5.189.164.31;
    root /var/www/painel-xui/public;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
'@

# Salvar config localmente e enviar
$nginxConfig | Out-File -FilePath "nginx-painel.conf" -Encoding UTF8
scp nginx-painel.conf "$VPS_USER@${VPS_IP}:/etc/nginx/sites-available/painel-xui"
Remove-Item nginx-painel.conf

ssh "$VPS_USER@$VPS_IP" "ln -sf /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/ && rm -f /etc/nginx/sites-enabled/default && nginx -t && systemctl restart nginx && echo 'Nginx configurado!'"

# Comando 7: Verificar status
Write-Host "`n✅ [7/7] Verificando serviços..." -ForegroundColor Cyan
ssh "$VPS_USER@$VPS_IP" "systemctl is-active nginx && echo 'Nginx: OK' && systemctl is-active php8.2-fpm && echo 'PHP-FPM: OK' && systemctl is-active mysql && echo 'MySQL: OK'"

Write-Host "`n🎉 Configuração concluída!" -ForegroundColor Green
Write-Host "`n📋 Credenciais do Banco:" -ForegroundColor Yellow
Write-Host "   Database: painel_xui"
Write-Host "   User: painel_user"
Write-Host "   Password: Painel@2026#Strong"
Write-Host "`n🎯 Próximo passo: Fazer upload do código da aplicação"
