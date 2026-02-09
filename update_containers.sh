#!/bin/bash
# =============================================================================
# Script de atualização de containers Painelshark
# Uso: bash update_containers.sh
# - Faz docker pull da imagem mais recente
# - Recria cada container painel_* preservando env vars, labels Traefik e volumes
# - Corrige permissões e roda migrations + optimize
# =============================================================================

set -e

IMAGE="carpini/painelshark:latest"
VOLUME="-v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh"

echo "=== Puxando imagem mais recente ==="
docker pull "$IMAGE"
echo ""

CONTAINERS=$(docker ps -a --filter "name=painel_" --format "{{.Names}}" | sort)

if [ -z "$CONTAINERS" ]; then
    echo "Nenhum container painel_* encontrado."
    exit 0
fi

echo "Containers encontrados: $CONTAINERS"
echo ""

for NAME in $CONTAINERS; do
    echo "=== Atualizando $NAME ==="

    # 1. Capturar env vars (excluir vars internas do Docker/PHP)
    ENV_ARGS=()
    while IFS= read -r line; do
        line=$(echo "$line" | xargs)
        [[ -z "$line" ]] && continue
        case "$line" in
            PATH=*|HOSTNAME=*|PHP*|PHPIZE*|GPG*|HOME=*) continue ;;
        esac
        ENV_ARGS+=("-e" "$line")
    done < <(docker inspect --format='{{range .Config.Env}}{{println .}}{{end}}' "$NAME" 2>/dev/null)

    # 2. Capturar labels Traefik
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
        echo ""
        continue
    fi

    # 3. Parar e remover container antigo
    echo "  Parando..."
    docker stop "$NAME" 2>/dev/null || true
    docker rm "$NAME" 2>/dev/null || true

    # 4. Recriar com nova imagem
    echo "  Recriando..."
    docker run -d \
        --name "$NAME" \
        --restart always \
        --network web_network \
        -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
        "${ENV_ARGS[@]}" \
        "${LABEL_ARGS[@]}" \
        "$IMAGE"

    # 5. Aguardar inicialização
    echo "  Aguardando inicialização (15s)..."
    sleep 15

    # 6. Corrigir permissões
    docker exec "$NAME" chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    docker exec "$NAME" chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

    # 7. Rodar migrations e otimizar
    echo "  Rodando migrations..."
    docker exec "$NAME" php artisan migrate --force 2>&1 || true
    docker exec "$NAME" php artisan optimize:clear 2>&1 || true
    docker exec "$NAME" php artisan optimize 2>&1 || true

    echo "  $NAME atualizado!"
    echo ""
done

echo "=== Todos os containers atualizados! ==="
docker ps --filter "name=painel_" --format "table {{.Names}}\t{{.Status}}\t{{.Image}}"
