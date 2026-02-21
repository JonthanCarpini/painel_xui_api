#!/bin/bash
# Atualiza repo, rebuilda imagem e recria TODOS os painéis
# Executar na VPS: bash /opt/full_update_vps.sh

REPO_DIR="/opt/painel_xui_api"
IMAGE_NAME="carpini/painelshark:latest"

echo "=== 1. Atualizando repositório ==="
cd "$REPO_DIR"
git fetch origin
git reset --hard origin/main

echo "=== 2. Build da imagem Docker (SEM CACHE) ==="
docker build --no-cache -t "$IMAGE_NAME" .

echo "=== 3. Recriando Containers ==="

# Definição das variáveis de ambiente padrão
XUI_BASE_URL="http://109.205.178.143/fXvFkkfq/"
XUI_API_KEY="5EE3138A43E3190ED00F031B1107EA30"
XUI_HOST="http://109.205.178.143"
EVO_URL="https://evo.onpanel.site"
EVO_KEY="evo_standalone_key_2026"

recreate_panel() {
    local NAME=$1
    local DOMAIN=$2
    local DB_USER=$3
    local DB_PASS=$4
    
    echo "Recriando $NAME ($DOMAIN)..."
    
    docker rm -f "$NAME" 2>/dev/null || true
    KEY="base64:$(openssl rand -base64 32)"
    
    # Tratamento especial para painel_9
    local RULE_LABEL="traefik.http.routers.${NAME}.rule=Host(\`${DOMAIN}\`)"
    if [ "$NAME" == "painel_9" ]; then
        RULE_LABEL="traefik.http.routers.${NAME}.rule=Host(\`blade.vp1.officex.site\`) || Host(\`painelx.website\`)"
    fi

    docker run -d \
      --name "$NAME" \
      --restart always \
      --network web_network \
      -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
      -e APP_NAME=PainelShark \
      -e APP_ENV=production \
      -e APP_KEY="$KEY" \
      -e APP_DEBUG=false \
      -e APP_URL="https://$DOMAIN" \
      -e DB_CONNECTION=mysql \
      -e DB_HOST=mysql_central \
      -e DB_PORT=3306 \
      -e DB_DATABASE="$NAME" \
      -e DB_USERNAME="$DB_USER" \
      -e DB_PASSWORD="$DB_PASS" \
      -e XUI_BASE_URL="$XUI_BASE_URL" \
      -e XUI_API_KEY="$XUI_API_KEY" \
      -e XUI_HOST="$XUI_HOST" \
      -e XUI_TIMEOUT=30 \
      -e EVOLUTION_API_URL="$EVO_URL" \
      -e EVOLUTION_API_KEY="$EVO_KEY" \
      --label "traefik.enable=true" \
      --label "$RULE_LABEL" \
      --label "traefik.http.routers.${NAME}.entrypoints=websecure" \
      --label "traefik.http.routers.${NAME}.tls.certresolver=myresolver" \
      --label "traefik.http.services.${NAME}.loadbalancer.server.port=80" \
      "$IMAGE_NAME"
      
    echo "Aguardando 5s..."
    sleep 5
}

# Painel 5
recreate_panel "painel_5" "opera.vp1.officex.site" "user_5" "3b4308c5b5e91559e5202003"

# Painel 6
recreate_panel "painel_6" "genial.vp1.officex.site" "user_6" "6170dffcdb0cc467a2824b07"

# Painel 9
recreate_panel "painel_9" "blade.vp1.officex.site" "user_9" "0c49a64cfcae6d05999055c3"

# Painel 18
recreate_panel "painel_18" "carpini2.vp1.officex.site" "user_18" "752e184891e6856f71ec0b45"

# Painel 20
recreate_panel "painel_20" "p2player.vp1.officex.site" "user_20" "28e460109527c46988b51f18"

echo "=== 4. Limpeza Final de Caches ==="
for p in painel_5 painel_6 painel_9 painel_18 painel_20; do
    echo "Limpando $p..."
    docker exec "$p" php artisan optimize:clear
    docker exec "$p" php artisan view:clear
    # Garantir permissões
    docker exec "$p" chown -R www-data:www-data /var/www/storage
    docker exec "$p" chmod -R 775 /var/www/storage
done

echo ""
echo "=== Status Final ==="
docker ps --format 'table {{.Names}}\t{{.Status}}\t{{.Image}}' | grep painel_
