#!/bin/bash
# =============================================================================
# Adiciona XUI_BASE_URL e XUI_API_KEY nos containers painel_* existentes
# Uso: bash add_xui_api_envs.sh
# Deve ser rodado NA VPS antes ou depois do update_containers.sh
# =============================================================================

set -e

IMAGE="carpini/painelshark:latest"

# Mapeamento: container → XUI_BASE_URL e XUI_API_KEY
# Ajuste os valores conforme cada cliente
declare -A XUI_BASE_URLS=(
    ["painel_5"]="http://109.205.178.143/fXvFkkfq/"
    ["painel_6"]="http://109.205.178.143/fXvFkkfq/"
    ["painel_9"]="http://109.205.178.143/fXvFkkfq/"
    ["painel_18"]="http://109.205.178.143/fXvFkkfq/"
    ["painel_20"]="http://109.205.178.143/fXvFkkfq/"
)

declare -A XUI_API_KEYS=(
    ["painel_5"]="5EE3138A43E3190ED00F031B1107EA30"
    ["painel_6"]="5EE3138A43E3190ED00F031B1107EA30"
    ["painel_9"]="5EE3138A43E3190ED00F031B1107EA30"
    ["painel_18"]="5EE3138A43E3190ED00F031B1107EA30"
    ["painel_20"]="5EE3138A43E3190ED00F031B1107EA30"
)

CONTAINERS=$(docker ps -a --filter "name=painel_" --format "{{.Names}}" | sort)

if [ -z "$CONTAINERS" ]; then
    echo "Nenhum container painel_* encontrado."
    exit 0
fi

echo "Containers encontrados: $CONTAINERS"
echo ""

for NAME in $CONTAINERS; do
    XUI_BASE_URL="${XUI_BASE_URLS[$NAME]}"
    XUI_API_KEY="${XUI_API_KEYS[$NAME]}"

    if [ -z "$XUI_BASE_URL" ]; then
        echo "[AVISO] $NAME não tem XUI_BASE_URL definida no script. Pulando."
        continue
    fi

    echo "=== Atualizando $NAME ==="

    # Capturar env vars existentes
    ENV_ARGS=()
    while IFS= read -r line; do
        line=$(echo "$line" | xargs)
        [[ -z "$line" ]] && continue
        case "$line" in
            PATH=*|HOSTNAME=*|PHP*|PHPIZE*|GPG*|HOME=*|XUI_BASE_URL=*|XUI_API_KEY=*) continue ;;
        esac
        ENV_ARGS+=("-e" "$line")
    done < <(docker inspect --format='{{range .Config.Env}}{{println .}}{{end}}' "$NAME" 2>/dev/null)

    # Adicionar novas vars
    ENV_ARGS+=("-e" "XUI_BASE_URL=${XUI_BASE_URL}")
    ENV_ARGS+=("-e" "XUI_API_KEY=${XUI_API_KEY}")

    # Capturar labels Traefik
    LABEL_ARGS=()
    while IFS= read -r line; do
        line=$(echo "$line" | xargs)
        [[ -z "$line" ]] && continue
        case "$line" in
            traefik.*) LABEL_ARGS+=("--label" "$line") ;;
        esac
    done < <(docker inspect --format='{{range $k,$v := .Config.Labels}}{{printf "%s=%s\n" $k $v}}{{end}}' "$NAME" 2>/dev/null)

    if [ ${#LABEL_ARGS[@]} -eq 0 ]; then
        echo "  [AVISO] Nenhuma label Traefik encontrada! Pulando $NAME."
        continue
    fi

    # Parar e remover
    echo "  Parando..."
    docker stop "$NAME" 2>/dev/null || true
    docker rm "$NAME" 2>/dev/null || true

    # Recriar com novas env vars
    echo "  Recriando com XUI_BASE_URL e XUI_API_KEY..."
    docker run -d \
        --name "$NAME" \
        --restart always \
        --network web_network \
        -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
        "${ENV_ARGS[@]}" \
        "${LABEL_ARGS[@]}" \
        "$IMAGE"

    echo "  Aguardando inicialização (15s)..."
    sleep 15

    docker exec "$NAME" chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    docker exec "$NAME" php artisan migrate --force 2>&1 || true
    docker exec "$NAME" php artisan optimize:clear 2>&1 || true
    docker exec "$NAME" php artisan optimize 2>&1 || true

    echo "  $NAME atualizado com XUI API!"
    echo ""
done

echo "=== Concluído! ==="
docker ps --filter "name=painel_" --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"
