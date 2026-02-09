#!/bin/sh
set -e

# Configura timezone do container para São Paulo
ln -snf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime
echo "America/Sao_Paulo" > /etc/timezone

# Inicia o Nginx em background
service nginx start

# Ajusta permissões (crítico para Docker)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Roda comandos do Laravel
echo "Executando migracoes e otimizacoes..."
php artisan package:discover --ansi
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

# Inicia o Laravel Scheduler em background
echo "Iniciando Laravel Scheduler..."
php artisan schedule:work >> /var/www/storage/logs/scheduler.log 2>&1 &

# Inicia o PHP-FPM (processo principal)
echo "Painelshark iniciado!"
php-fpm
