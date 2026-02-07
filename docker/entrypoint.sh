#!/bin/sh
set -e

# Inicia o Nginx em background
service nginx start

# Aguarda o banco de dados estar disponível (opcional, mas recomendado)
# while ! mysqladmin ping -h"$DB_HOST" --silent; do
#     sleep 1
# done

# Ajusta permissões (crítico para Docker)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Roda comandos do Laravel
echo "🚀 Executando migrações e otimizações..."
php artisan package:discover --ansi
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

# Inicia o PHP-FPM (processo principal)
echo "✅ Painelshark iniciado!"
php-fpm
