#!/bin/bash
# Script de deploy na VPS — clona repo, faz build e recria containers painel_*
# Executar na VPS: bash /opt/deploy_vps.sh

set -e

REPO_URL="https://github.com/JonthanCarpini/painel_xui_api.git"
REPO_DIR="/opt/painel_xui_api"
IMAGE_NAME="carpini/painelshark:latest"

XUI_BASE_URL="http://192.168.100.210/fXvFkkfq/"
XUI_API_KEY="5EE3138A43E3190ED00F031B1107EA30"

echo "=============================="
echo " PainelShark — Deploy via Git"
echo "=============================="

# 1. Clonar ou atualizar o repositório
if [ -d "$REPO_DIR/.git" ]; then
    echo "[1/4] Atualizando repositório..."
    cd "$REPO_DIR"
    git pull origin main
else
    echo "[1/4] Clonando repositório..."
    git clone "$REPO_URL" "$REPO_DIR"
    cd "$REPO_DIR"
fi

# 2. Build da imagem Docker
echo "[2/4] Fazendo build da imagem Docker..."
docker build -t "$IMAGE_NAME" .
echo "Build concluído: $IMAGE_NAME"

# 3. Recriar cada container painel_* com a nova imagem
echo "[3/4] Recriando containers painel_*..."

for CONTAINER in $(docker ps -a --format '{{.Names}}' | grep '^painel_'); do
    echo ""
    echo "--- Processando: $CONTAINER ---"

    # Capturar env vars atuais
    ENV_ARGS=$(docker inspect "$CONTAINER" --format '{{range .Config.Env}}-e "{{.}}" {{end}}')

    # Capturar labels Traefik
    LABEL_ARGS=$(docker inspect "$CONTAINER" --format '{{range $k,$v := .Config.Labels}}--label "{{$k}}={{$v}}" {{end}}')

    # Adicionar/sobrescrever XUI_BASE_URL e XUI_API_KEY
    # Remove entradas antigas se existirem
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_BASE_URL=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_API_KEY=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_DB_HOST=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_DB_PORT=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_DB_DATABASE=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_DB_USERNAME=[^"]*" //g')
    ENV_ARGS=$(echo "$ENV_ARGS" | sed 's/-e "XUI_DB_PASSWORD=[^"]*" //g')

    # Adicionar novas vars
    ENV_ARGS="$ENV_ARGS -e \"XUI_BASE_URL=$XUI_BASE_URL\" -e \"XUI_API_KEY=$XUI_API_KEY\""

    # Parar e remover container antigo
    docker stop "$CONTAINER" || true
    docker rm "$CONTAINER" || true

    # Recriar com nova imagem
    eval docker run -d \
        --name "$CONTAINER" \
        --restart always \
        --network web_network \
        -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
        $ENV_ARGS \
        $LABEL_ARGS \
        "$IMAGE_NAME"

    echo "Container $CONTAINER recriado. Aguardando 20s..."
    sleep 20

    # Verificar se está rodando
    if docker ps --format '{{.Names}}' | grep -q "^${CONTAINER}$"; then
        echo "✓ $CONTAINER está rodando"
        docker logs "$CONTAINER" --tail 10
    else
        echo "✗ ERRO: $CONTAINER não iniciou!"
        docker logs "$CONTAINER" --tail 30
    fi
done

echo ""
echo "[4/4] Deploy concluído!"
echo ""
echo "Containers ativos:"
docker ps --format 'table {{.Names}}\t{{.Status}}' | grep -E 'painel_|NAMES'
