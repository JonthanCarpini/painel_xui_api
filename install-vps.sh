#!/bin/bash
# Script de Instalação Automática - Painel XUI
# Execute na VPS: bash install-vps.sh

set -e

echo "🚀 Iniciando instalação do Painel XUI..."
echo ""

# 1. Atualizar sistema
echo "📦 [1/8] Atualizando sistema..."
apt update && apt upgrade -y

# 2. Instalar dependências
echo "📦 [2/8] Instalando Nginx, PHP 8.2, MySQL..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y nginx mysql-server \
php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl \
php8.2-xml php8.2-bcmath php8.2-intl \
git curl supervisor ufw fail2ban unzip

# 3. Instalar Composer
echo "📦 [3/8] Instalando Composer..."
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# 4. Configurar Firewall
echo "🔒 [4/8] Configurando firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# 5. Configurar MySQL
echo "💾 [5/8] Configurando MySQL..."
mysql -e "CREATE DATABASE IF NOT EXISTS painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'painel_user'@'localhost' IDENTIFIED BY 'Painel@2026#Strong';"
mysql -e "GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# 6. Criar usuário deploy
echo "👤 [6/8] Criando usuário deploy..."
if ! id "deploy" &>/dev/null; then
    adduser deploy --disabled-password --gecos ""
    usermod -aG sudo deploy
    echo "deploy ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers
fi

# 7. Criar diretório da aplicação
echo "📁 [7/8] Criando diretório da aplicação..."
mkdir -p /var/www/painel-xui
chown -R deploy:deploy /var/www/painel-xui

# 8. Configurar Nginx
echo "🌐 [8/8] Configurando Nginx..."
cat > /etc/nginx/sites-available/painel-xui << 'EOF'
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
EOF

ln -sf /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx

echo ""
echo "✅ Instalação concluída!"
echo ""
echo "📋 Informações importantes:"
echo "   Database: painel_xui"
echo "   User: painel_user"
echo "   Password: Painel@2026#Strong"
echo "   App Dir: /var/www/painel-xui"
echo ""
echo "🎯 Próximos passos:"
echo "   1. Fazer upload do código para /var/www/painel-xui"
echo "   2. Configurar .env"
echo "   3. Executar: composer install"
echo "   4. Executar: php artisan key:generate"
echo "   5. Importar banco de dados"
echo ""
