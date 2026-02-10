#!/bin/bash
# Cria container Nginx proxy para XUI com SSL via Traefik
# Uso: ./create_xui_proxy.sh <instance_id> <xui_ip> <subdomain>

INSTANCE_ID=$1
XUI_IP=$2
SUBDOMAIN=$3

if [ -z "$INSTANCE_ID" ] || [ -z "$XUI_IP" ] || [ -z "$SUBDOMAIN" ]; then
    echo "Uso: $0 <instance_id> <xui_ip> <subdomain>"
    echo "Exemplo: $0 6 109.205.178.143 xui.genial.vp1.officex.site"
    exit 1
fi

CONTAINER_NAME="xui_proxy_${INSTANCE_ID}"
CONF_PATH="/opt/xui_proxy_${INSTANCE_ID}.conf"

# Criar config Nginx
cat > "$CONF_PATH" << 'EOF'
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://XUI_IP_PLACEHOLDER;
        proxy_set_header Host XUI_IP_PLACEHOLDER;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_buffering off;
        proxy_read_timeout 300s;
        proxy_connect_timeout 10s;

        # Reescrever redirects HTTP do XUI para HTTPS via proxy
        proxy_redirect ~^http://XUI_IP_PLACEHOLDER_ESCAPED(:\d+)?(.*)$ https://$host$2;
    }
}
EOF

XUI_IP_ESCAPED=$(echo "$XUI_IP" | sed 's/\./\\\\./g')
sed -i "s/XUI_IP_PLACEHOLDER_ESCAPED/${XUI_IP_ESCAPED}/g" "$CONF_PATH"
sed -i "s/XUI_IP_PLACEHOLDER/${XUI_IP}/g" "$CONF_PATH"

# Remover container antigo se existir
docker rm -f "$CONTAINER_NAME" 2>/dev/null

# Criar container
docker run -d \
    --name "$CONTAINER_NAME" \
    --restart always \
    --network web_network \
    -v "${CONF_PATH}:/etc/nginx/conf.d/default.conf:ro" \
    --label "traefik.enable=true" \
    --label "traefik.http.routers.${CONTAINER_NAME}.rule=Host(\`${SUBDOMAIN}\`)" \
    --label "traefik.http.routers.${CONTAINER_NAME}.entrypoints=websecure" \
    --label "traefik.http.routers.${CONTAINER_NAME}.tls.certresolver=le" \
    --label "traefik.http.services.${CONTAINER_NAME}.loadbalancer.server.port=80" \
    nginx:alpine

echo "Container $CONTAINER_NAME criado para $SUBDOMAIN -> $XUI_IP"
