#!/bin/sh
set -e

# Configura timezone do container para São Paulo
ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime
echo "America/Sao_Paulo" > /etc/timezone

# Gera .env a partir das variáveis de ambiente do container
echo "Gerando .env..."
cat > /var/www/.env << EOF
APP_NAME="${APP_NAME:-PainelShark}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-mysql_central}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-painelshark}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

XUI_BASE_URL="${XUI_BASE_URL}"
XUI_API_KEY="${XUI_API_KEY}"
XUI_HOST="${XUI_HOST}"
XUI_TIMEOUT="${XUI_TIMEOUT:-30}"

EVOLUTION_API_URL="${EVOLUTION_API_URL}"
EVOLUTION_API_KEY="${EVOLUTION_API_KEY}"

TMDB_API_KEY="${TMDB_API_KEY}"
EOF

# Inicia o Nginx em background
service nginx start

# Ajusta permissões (crítico para Docker)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
mkdir -p /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/framework/sessions

# Roda comandos do Laravel
echo "Executando migracoes e otimizacoes..."
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

# Inicia o Laravel Scheduler em background
echo "Iniciando Laravel Scheduler..."
php artisan schedule:work >> /var/www/storage/logs/scheduler.log 2>&1 &

# Inicia o PHP-FPM (processo principal)
echo "Painelshark iniciado!"
php-fpm
