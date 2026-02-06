#!/bin/bash

# Script de Deploy Automatizado - Painel XUI
# Versão: 1.0
# Data: 04/02/2026

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_message() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Verificar se está rodando como usuário correto
if [ "$EUID" -eq 0 ]; then 
    print_error "Não execute este script como root!"
    exit 1
fi

# Diretório da aplicação
APP_DIR="/var/www/painel-xui"

print_message "🚀 Iniciando deploy do Painel XUI..."

# 1. Entrar no diretório
print_info "Entrando no diretório da aplicação..."
cd $APP_DIR || exit 1

# 2. Ativar modo de manutenção
print_info "Ativando modo de manutenção..."
php artisan down || print_warning "Modo de manutenção já estava ativo"

# 3. Fazer backup do banco de dados
print_info "Criando backup do banco de dados..."
BACKUP_DIR="$HOME/backups"
mkdir -p $BACKUP_DIR
DATE=$(date +%Y-%m-%d_%H-%M-%S)

if [ -f .env ]; then
    DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)
    
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_backup_$DATE.sql"
    print_message "✅ Backup criado: $BACKUP_DIR/db_backup_$DATE.sql"
else
    print_warning "Arquivo .env não encontrado, pulando backup"
fi

# 4. Atualizar código (Git)
if [ -d .git ]; then
    print_info "Atualizando código do repositório..."
    git fetch origin
    git pull origin main || git pull origin master
    print_message "✅ Código atualizado"
else
    print_warning "Não é um repositório Git, pulando atualização"
fi

# 5. Instalar/Atualizar dependências
print_info "Instalando dependências do Composer..."
composer install --optimize-autoloader --no-dev --no-interaction
print_message "✅ Dependências instaladas"

# 6. Limpar caches antigos
print_info "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
print_message "✅ Caches limpos"

# 7. Executar migrations
print_info "Executando migrations..."
php artisan migrate --force
print_message "✅ Migrations executadas"

# 8. Recriar caches otimizados
print_info "Recriando caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_message "✅ Caches recriados"

# 9. Otimizar autoloader
print_info "Otimizando autoloader..."
composer dump-autoload --optimize
print_message "✅ Autoloader otimizado"

# 10. Ajustar permissões
print_info "Ajustando permissões..."
sudo chown -R $USER:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache
print_message "✅ Permissões ajustadas"

# 11. Reiniciar serviços
print_info "Reiniciando serviços..."
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# Reiniciar workers do Supervisor (se existir)
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart painel-xui-worker:* || print_warning "Workers não encontrados"
fi

print_message "✅ Serviços reiniciados"

# 12. Desativar modo de manutenção
print_info "Desativando modo de manutenção..."
php artisan up
print_message "✅ Aplicação online"

# 13. Limpar backups antigos (manter últimos 7 dias)
print_info "Limpando backups antigos..."
find $BACKUP_DIR -name "db_backup_*.sql" -mtime +7 -delete
print_message "✅ Backups antigos removidos"

# 14. Verificar status
print_info "Verificando status dos serviços..."
echo ""
echo "Status dos Serviços:"
echo "==================="
sudo systemctl is-active nginx && echo "✅ Nginx: Ativo" || echo "❌ Nginx: Inativo"
sudo systemctl is-active php8.2-fpm && echo "✅ PHP-FPM: Ativo" || echo "❌ PHP-FPM: Inativo"
sudo systemctl is-active mysql && echo "✅ MySQL: Ativo" || echo "❌ MySQL: Inativo"

if command -v supervisorctl &> /dev/null; then
    echo ""
    echo "Workers:"
    sudo supervisorctl status painel-xui-worker:* || echo "Nenhum worker configurado"
fi

echo ""
print_message "🎉 Deploy concluído com sucesso!"
print_info "Tempo total: $SECONDS segundos"
print_info "Backup salvo em: $BACKUP_DIR/db_backup_$DATE.sql"

# 15. Mostrar últimas linhas do log
echo ""
print_info "Últimas linhas do log:"
echo "======================"
tail -n 10 storage/logs/laravel.log 2>/dev/null || print_warning "Nenhum log encontrado ainda"

echo ""
print_message "✅ Tudo pronto! Acesse: https://seudominio.com"
