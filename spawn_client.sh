#!/bin/bash

# ==========================================
# 🐳 Script de Provisionamento de Cliente SaaS
# Uso: ./spawn_client.sh [CLIENT_NAME] [DOMAIN] [XUI_HOST]
# ==========================================

CLIENT_NAME=$1
DOMAIN=$2
XUI_HOST=$3

if [ -z "$CLIENT_NAME" ] || [ -z "$DOMAIN" ]; then
    echo "❌ Uso: ./spawn_client.sh [nome_cliente] [dominio] [xui_host]"
    echo "Exemplo: ./spawn_client.sh cliente_01 painel.loja.com http://5.189.164.31"
    exit 1
fi

# Configurações Fixas
IMAGE_NAME="jonthancarpini/painelshark:latest" # Sua imagem no Docker Hub
NETWORK="saas_network"
MYSQL_HOST="mysql_central"
ROOT_PASS="root_password_segura" # Mesma do docker-compose.saas.yml

# Nomes Dinâmicos
CONTAINER_NAME="app_${CLIENT_NAME}"
DB_NAME="painel_${CLIENT_NAME}"
DB_USER="user_${CLIENT_NAME}"
DB_PASS=$(openssl rand -base64 12)

echo "🏗️  Iniciando provisionamento para: $CLIENT_NAME ($DOMAIN)..."

# 1. Criar Banco de Dados e Usuário
echo "🗄️  Criando banco de dados isolado..."
docker exec mysql_central mysql -uroot -p"$ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
docker exec mysql_central mysql -uroot -p"$ROOT_PASS" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';"
docker exec mysql_central mysql -uroot -p"$ROOT_PASS" -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';"
docker exec mysql_central mysql -uroot -p"$ROOT_PASS" -e "FLUSH PRIVILEGES;"

# 2. Subir Container do Cliente
echo "🚀 Subindo container..."
docker run -d \
    --name "$CONTAINER_NAME" \
    --network "$NETWORK" \
    --restart always \
    --label "traefik.enable=true" \
    --label "traefik.http.routers.${CONTAINER_NAME}.rule=Host(\`${DOMAIN}\`)" \
    --label "traefik.http.routers.${CONTAINER_NAME}.entrypoints=web" \
    --env DB_CONNECTION=mysql \
    --env DB_HOST="$MYSQL_HOST" \
    --env DB_PORT=3306 \
    --env DB_DATABASE="$DB_NAME" \
    --env DB_USERNAME="$DB_USER" \
    --env DB_PASSWORD="$DB_PASS" \
    --env APP_URL="http://${DOMAIN}" \
    --env APP_ENV=production \
    --env APP_DEBUG=false \
    --env XUI_HOST="$XUI_HOST" \
    "$IMAGE_NAME"

echo "✅ Container $CONTAINER_NAME rodando!"
echo "🌍 Acessível em: http://$DOMAIN"
echo "🔐 Credenciais DB: $DB_USER / $DB_PASS"
