# ⚡ GUIA DE DEPLOY RÁPIDO - PAINEL XUI

## 🎯 CENÁRIO 1: VPS NOVA (Ubuntu 22.04)

### Instalação Completa em 10 Minutos

```bash
# 1. Conectar na VPS
ssh root@SEU_IP

# 2. Executar script de instalação automática
curl -fsSL https://raw.githubusercontent.com/SEU_REPO/painel-xui/main/install.sh | bash

# OU fazer manualmente:

# Atualizar sistema
apt update && apt upgrade -y

# Instalar tudo de uma vez
apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
php8.2-bcmath php8.2-intl git curl supervisor ufw fail2ban

# Instalar Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configurar firewall
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# Criar usuário deploy
adduser deploy --disabled-password --gecos ""
usermod -aG sudo deploy
echo "deploy ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Configurar MySQL
mysql -e "CREATE DATABASE painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'painel_user'@'localhost' IDENTIFIED BY 'SENHA_FORTE';"
mysql -e "GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Clonar aplicação
cd /var/www
git clone https://github.com/SEU_REPO/painel-xui.git
cd painel-xui

# Configurar aplicação
cp .env.example .env
nano .env  # Editar configurações

composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force

# Ajustar permissões
chown -R deploy:www-data /var/www/painel-xui
chmod -R 755 /var/www/painel-xui
chmod -R 775 /var/www/painel-xui/storage
chmod -R 775 /var/www/painel-xui/bootstrap/cache

# Configurar Nginx
cat > /etc/nginx/sites-available/painel-xui << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/painel-xui/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

ln -s /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t && systemctl restart nginx

# Instalar SSL (após apontar domínio)
apt install -y certbot python3-certbot-nginx
certbot --nginx -d seudominio.com --non-interactive --agree-tos -m seu@email.com

echo "✅ Deploy concluído! Acesse: http://SEU_IP"
```

---

## 🚀 CENÁRIO 2: ATUALIZAÇÃO RÁPIDA

```bash
# Conectar na VPS
ssh deploy@SEU_IP

# Navegar para o diretório
cd /var/www/painel-xui

# Executar script de deploy
./deploy.sh

# OU manualmente:
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.2-fpm nginx
php artisan up

echo "✅ Atualização concluída!"
```

---

## 🔧 CENÁRIO 3: DEPLOY COM DOCKER (Alternativa)

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: painel-xui
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - painel-network
    depends_on:
      - db

  nginx:
    image: nginx:alpine
    container_name: painel-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx:/etc/nginx/conf.d
    networks:
      - painel-network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: painel-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: painel_xui
      MYSQL_USER: painel_user
      MYSQL_PASSWORD: senha_forte
      MYSQL_ROOT_PASSWORD: root_senha_forte
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - painel-network

networks:
  painel-network:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

### Dockerfile
```dockerfile
FROM php:8.2-fpm

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar aplicação
COPY . .

# Instalar dependências
RUN composer install --optimize-autoloader --no-dev

# Ajustar permissões
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

### Deploy com Docker
```bash
# Build e iniciar
docker-compose up -d --build

# Executar migrations
docker-compose exec app php artisan migrate --force

# Ver logs
docker-compose logs -f app
```

---

## 📦 CENÁRIO 4: DEPLOY COM DEPLOYER

### deploy.php
```php
<?php
namespace Deployer;

require 'recipe/laravel.php';

// Configuração
set('application', 'Painel XUI');
set('repository', 'git@github.com:SEU_REPO/painel-xui.git');
set('keep_releases', 5);

// Hosts
host('producao')
    ->setHostname('SEU_IP')
    ->setRemoteUser('deploy')
    ->set('deploy_path', '/var/www/painel-xui');

// Tasks customizadas
task('deploy:secrets', function () {
    upload('.env.production', '{{deploy_path}}/shared/.env');
});

// Hooks
after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'artisan:migrate');
after('deploy:symlink', 'artisan:cache:clear');
after('deploy:symlink', 'artisan:config:cache');
after('deploy:symlink', 'artisan:route:cache');
after('deploy:symlink', 'artisan:view:cache');
```

### Usar Deployer
```bash
# Instalar Deployer
composer require deployer/deployer --dev

# Deploy
vendor/bin/dep deploy producao
```

---

## 🌐 CENÁRIO 5: DEPLOY EM DIFERENTES PROVEDORES

### DigitalOcean App Platform
```yaml
# .do/app.yaml
name: painel-xui
services:
- name: web
  github:
    repo: SEU_REPO/painel-xui
    branch: main
  build_command: composer install --optimize-autoloader --no-dev
  run_command: php artisan serve --host=0.0.0.0 --port=8080
  envs:
  - key: APP_ENV
    value: production
  - key: APP_DEBUG
    value: "false"
  - key: APP_KEY
    value: ${APP_KEY}
databases:
- name: db
  engine: MYSQL
  version: "8"
```

### AWS Elastic Beanstalk
```yaml
# .ebextensions/01_laravel.config
option_settings:
  aws:elasticbeanstalk:container:php:phpini:
    document_root: /public
    memory_limit: 512M
  aws:elasticbeanstalk:application:environment:
    APP_ENV: production
    APP_DEBUG: false
```

### Heroku
```yaml
# Procfile
web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/
```

---

## ⚙️ CONFIGURAÇÕES OTIMIZADAS

### PHP-FPM Otimizado (4GB RAM)
```ini
# /etc/php/8.2/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
pm.process_idle_timeout = 10s
```

### Nginx Otimizado
```nginx
# /etc/nginx/nginx.conf
worker_processes auto;
worker_rlimit_nofile 65535;

events {
    worker_connections 4096;
    use epoll;
    multi_accept on;
}

http {
    # Gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript 
               application/x-javascript application/xml+rss 
               application/json application/javascript;
    
    # Cache
    open_file_cache max=10000 inactive=20s;
    open_file_cache_valid 30s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # Buffers
    client_body_buffer_size 128k;
    client_max_body_size 10m;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    output_buffers 1 32k;
    postpone_output 1460;
}
```

### MySQL Otimizado (4GB RAM)
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 200
query_cache_size = 0
query_cache_type = 0
```

---

## 🔍 MONITORAMENTO RÁPIDO

### Script de Monitoramento
```bash
#!/bin/bash
# monitor.sh

echo "=== STATUS DOS SERVIÇOS ==="
systemctl is-active nginx && echo "✅ Nginx" || echo "❌ Nginx"
systemctl is-active php8.2-fpm && echo "✅ PHP-FPM" || echo "❌ PHP-FPM"
systemctl is-active mysql && echo "✅ MySQL" || echo "❌ MySQL"

echo -e "\n=== USO DE RECURSOS ==="
echo "CPU: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1"%"}')"
echo "RAM: $(free -m | awk 'NR==2{printf "%.2f%%", $3*100/$2 }')"
echo "Disco: $(df -h / | awk 'NR==2{print $5}')"

echo -e "\n=== ÚLTIMOS ERROS ==="
tail -n 5 /var/www/painel-xui/storage/logs/laravel.log 2>/dev/null || echo "Nenhum erro"

echo -e "\n=== CONEXÕES ATIVAS ==="
netstat -an | grep :80 | wc -l
```

---

## 📱 COMANDOS DE EMERGÊNCIA

```bash
# Reiniciar tudo
sudo systemctl restart nginx php8.2-fpm mysql
sudo supervisorctl restart all

# Modo de manutenção
php artisan down --message="Manutenção programada" --retry=60

# Sair do modo de manutenção
php artisan up

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Limpar tudo
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Backup de emergência
mysqldump -u painel_user -p painel_xui > emergency_backup.sql
tar -czf emergency_files.tar.gz /var/www/painel-xui

# Restaurar backup
mysql -u painel_user -p painel_xui < emergency_backup.sql
```

---

## 🎯 RESUMO DE COMANDOS POR SITUAÇÃO

### Deploy Inicial
```bash
./install.sh
```

### Atualização
```bash
./deploy.sh
```

### Problema no Site
```bash
tail -f storage/logs/laravel.log
sudo systemctl restart nginx php8.2-fpm
```

### Site Lento
```bash
php artisan optimize
sudo systemctl restart php8.2-fpm
```

### Backup
```bash
./backup.sh
```

---

**Última atualização:** 04/02/2026
**Versão:** 1.0
