#!/bin/bash
# Script de configuração rápida - Execute na VPS
set -e

echo "🚀 Configurando ambiente..."

# Criar banco de dados
echo "📦 Criando banco de dados..."
mysql -e "CREATE DATABASE IF NOT EXISTS painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'painel_user'@'localhost' IDENTIFIED BY 'Painel@2026#Strong';"
mysql -e "GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo "✅ Banco criado: painel_xui / painel_user / Painel@2026#Strong"

# Criar diretório
echo "📁 Criando diretório..."
mkdir -p /var/www/painel-xui
echo "✅ Diretório criado: /var/www/painel-xui"

# Configurar Nginx
echo "🌐 Configurando Nginx..."
cat > /etc/nginx/sites-available/painel-xui << 'NGINX_EOF'
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
NGINX_EOF

ln -sf /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
echo "✅ Nginx configurado!"

# Verificar serviços
echo ""
echo "📊 Status dos serviços:"
systemctl is-active nginx && echo "✅ Nginx: Ativo" || echo "❌ Nginx: Inativo"
systemctl is-active php8.2-fpm && echo "✅ PHP-FPM: Ativo" || echo "❌ PHP-FPM: Inativo"
systemctl is-active mysql && echo "✅ MySQL: Ativo" || echo "❌ MySQL: Inativo"

echo ""
echo "🎉 Configuração concluída!"
echo ""
echo "📋 Informações:"
echo "   Database: painel_xui"
echo "   User: painel_user"
echo "   Password: Painel@2026#Strong"
echo "   App Dir: /var/www/painel-xui"
echo ""
