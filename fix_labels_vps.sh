#!/bin/bash
# Recria containers painel_* preservando env vars e corrigindo labels Traefik
# Executar na VPS: bash /opt/fix_labels_vps.sh

IMAGE="carpini/painelshark:latest"
XUI_BASE_URL="http://192.168.100.210/fXvFkkfq/"
XUI_API_KEY="5EE3138A43E3190ED00F031B1107EA30"

recreate_container() {
    local NAME=$1
    local DOMAIN=$2
    local CERT_RESOLVER=$3

    echo ""
    echo "=== Recriando $NAME (dominio: $DOMAIN) ==="

    # Capturar todas as env vars atuais
    mapfile -t ENVS < <(docker inspect "$NAME" --format '{{range .Config.Env}}{{println .}}{{end}}' 2>/dev/null)

    # Montar args de env, removendo vars XUI antigas e adicionando novas
    ENV_ARGS=()
    for env in "${ENVS[@]}"; do
        key="${env%%=*}"
        case "$key" in
            XUI_BASE_URL|XUI_API_KEY|XUI_DB_HOST|XUI_DB_PORT|XUI_DB_DATABASE|XUI_DB_USERNAME|XUI_DB_PASSWORD)
                # Ignorar — vamos adicionar as novas abaixo
                ;;
            *)
                ENV_ARGS+=(-e "$env")
                ;;
        esac
    done

    # Adicionar novas vars XUI
    ENV_ARGS+=(-e "XUI_BASE_URL=$XUI_BASE_URL")
    ENV_ARGS+=(-e "XUI_API_KEY=$XUI_API_KEY")

    # Parar e remover
    docker stop "$NAME" 2>/dev/null || true
    docker rm "$NAME" 2>/dev/null || true

    # Recriar com labels corretas
    docker run -d \
        --name "$NAME" \
        --restart always \
        --network web_network \
        -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
        "${ENV_ARGS[@]}" \
        --label "traefik.enable=true" \
        --label "traefik.http.routers.${NAME}.rule=Host(\`${DOMAIN}\`)" \
        --label "traefik.http.routers.${NAME}.entrypoints=websecure" \
        --label "traefik.http.routers.${NAME}.tls.certresolver=${CERT_RESOLVER}" \
        --label "traefik.http.services.${NAME}.loadbalancer.server.port=80" \
        "$IMAGE"

    echo "Aguardando 20s..."
    sleep 20

    if docker ps --format '{{.Names}}' | grep -q "^${NAME}$"; then
        echo "✓ $NAME rodando"
        docker logs "$NAME" --tail 5
    else
        echo "✗ ERRO: $NAME nao iniciou"
        docker logs "$NAME" --tail 20
    fi
}

# Recriar cada instância com domínio e cert resolver corretos
recreate_container "painel_5"  "opera.vp1.officex.site"    "myresolver"
recreate_container "painel_6"  "genial.vp1.officex.site"   "myresolver"
recreate_container "painel_9"  "blade.vp1.officex.site"    "myresolver"
recreate_container "painel_18" "carpini2.vp1.officex.site" "myresolver"
recreate_container "painel_20" "p2player.vp1.officex.site" "myresolver"

echo ""
echo "=== Verificando labels do painel_6 ==="
docker inspect painel_6 --format '{{json .Config.Labels}}'

echo ""
echo "=== Containers ativos ==="
docker ps --format 'table {{.Names}}\t{{.Status}}' | grep -E 'painel_|NAMES'
