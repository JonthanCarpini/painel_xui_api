#!/bin/bash
# Script para corrigir banco de dados

# Recriar usuário do banco
mysql -e "DROP USER IF EXISTS 'painel_user'@'localhost';"
mysql -e "CREATE USER 'painel_user'@'localhost' IDENTIFIED BY 'Painel@2026#Strong';"
mysql -e "GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Testar conexão
mysql -u painel_user -pPainel@2026#Strong painel_xui -e "SELECT 1;" && echo "✅ Conexão OK!"

# Ir para o diretório
cd /var/www/painel-xui

# Limpar cache
php artisan config:clear
php artisan cache:clear

# Testar Laravel
php artisan migrate --force

echo "✅ Tudo corrigido!"
