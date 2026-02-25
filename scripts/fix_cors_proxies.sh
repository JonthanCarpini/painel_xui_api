#!/bin/bash
# Fix v5: Recriar containers xui_proxy com config CORS + stream rewrite
XUI_IP="109.205.178.143"

get_ghost() {
    local NUM="$1" DBUSER="$2" DBPASS="$3"
    local GU="" GP="" SURL=""

    GU=$(echo "SELECT setting_value FROM app_settings WHERE setting_key='ghost_reseller_username' LIMIT 1;" | docker exec -i mysql_central mysql -u "$DBUSER" -p"$DBPASS" "painel_${NUM}" -N 2>/dev/null | tr -d '\r\n')
    GP=$(echo "SELECT setting_value FROM app_settings WHERE setting_key='ghost_reseller_password' LIMIT 1;" | docker exec -i mysql_central mysql -u "$DBUSER" -p"$DBPASS" "painel_${NUM}" -N 2>/dev/null | tr -d '\r\n')

    if [ -z "$GU" ] || [ -z "$GP" ]; then
        SURL=$(echo "SELECT stream_url FROM test_channels WHERE stream_url IS NOT NULL AND stream_url != '' LIMIT 1;" | docker exec -i mysql_central mysql -u "$DBUSER" -p"$DBPASS" "painel_${NUM}" -N 2>/dev/null | tr -d '\r\n')
        if [ -n "$SURL" ]; then
            local PATH_PART
            PATH_PART=$(echo "$SURL" | sed 's|https\?://[^/]*/||')
            PATH_PART=$(echo "$PATH_PART" | sed 's|^\(live\|movie\|series\)/||')
            GU=$(echo "$PATH_PART" | cut -d'/' -f1)
            GP=$(echo "$PATH_PART" | cut -d'/' -f2)
        fi
    fi

    [ -z "$GU" ] && GU="fantasma"
    [ -z "$GP" ] && GP="fantasma123"
    echo "${GU}:${GP}"
}

write_and_recreate() {
    local NUM="$1" GU="$2" GP="$3" DOMAIN="$4"
    local CONTAINER="xui_proxy_${NUM}"
    local CONF="/opt/xui_proxy_${NUM}.conf"

    # Escrever config no host (truncar mesmo inode)
    # Usar truncate + append para preservar inode
    : > "$CONF"
    cat >> "$CONF" << HEREDOC
server {
    listen 80;
    server_name _;

    location ~ ^/stream/(live|movie|series)/(.+)\$ {
        add_header Access-Control-Allow-Origin * always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS, HEAD" always;
        add_header Access-Control-Allow-Headers "Range, Origin, Accept, Content-Type" always;
        add_header Access-Control-Expose-Headers "Content-Length, Content-Range" always;

        if (\$request_method = OPTIONS) {
            return 204;
        }

        proxy_pass http://${XUI_IP}/\$1/${GU}/${GP}/\$2;
        proxy_set_header Host ${XUI_IP};
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_buffering off;
        proxy_read_timeout 300s;
        proxy_connect_timeout 10s;
    }

    location / {
        add_header Access-Control-Allow-Origin * always;
        add_header Access-Control-Allow-Methods "GET, OPTIONS, HEAD" always;
        add_header Access-Control-Allow-Headers "Range, Origin, Accept, Content-Type" always;
        add_header Access-Control-Expose-Headers "Content-Length, Content-Range" always;

        if (\$request_method = OPTIONS) {
            return 204;
        }

        proxy_pass http://${XUI_IP};
        proxy_set_header Host ${XUI_IP};
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_buffering off;
        proxy_read_timeout 300s;
        proxy_connect_timeout 10s;
    }
}
HEREDOC

    echo "  Config escrita: ghost=${GU}"

    # Recriar container (remove + run)
    docker rm -f "$CONTAINER" 2>/dev/null

    docker run -d \
      --name "$CONTAINER" \
      --restart always \
      --network web_network \
      -v "${CONF}:/etc/nginx/conf.d/default.conf" \
      --label "traefik.enable=true" \
      --label "traefik.http.routers.${CONTAINER}.rule=Host(\`xui.${DOMAIN}\`)" \
      --label "traefik.http.routers.${CONTAINER}.entrypoints=websecure" \
      --label "traefik.http.routers.${CONTAINER}.tls.certresolver=le" \
      --label "traefik.http.services.${CONTAINER}.loadbalancer.server.port=80" \
      nginx:alpine

    echo "  $CONTAINER recriado"
    sleep 2
    docker exec "$CONTAINER" nginx -t 2>&1 | tail -1
}

echo "=== Recriando XUI Proxies ==="

CREDS=$(get_ghost 5 "user_5" "3b4308c5b5e91559e5202003")
write_and_recreate 5 "${CREDS%%:*}" "${CREDS#*:}" "opera.vp1.officex.site"

CREDS=$(get_ghost 6 "user_6" "6170dffcdb0cc467a2824b07")
write_and_recreate 6 "${CREDS%%:*}" "${CREDS#*:}" "genial.vp1.officex.site"

CREDS=$(get_ghost 9 "user_9" "0c49a64cfcae6d05999055c3")
write_and_recreate 9 "${CREDS%%:*}" "${CREDS#*:}" "blade.vp1.officex.site"

CREDS=$(get_ghost 18 "user_18" "752e184891e6856f71ec0b45")
write_and_recreate 18 "${CREDS%%:*}" "${CREDS#*:}" "carpini2.vp1.officex.site"

CREDS=$(get_ghost 20 "user_20" "28e460109527c46988b51f18")
write_and_recreate 20 "${CREDS%%:*}" "${CREDS#*:}" "p2player.vp1.officex.site"

echo ""
echo "=== Teste ==="
sleep 3
curl -sI -H "Origin: https://p2player.vp1.officex.site" "https://xui.p2player.vp1.officex.site/stream/live/2.m3u8" 2>&1 | head -12
echo "---"
docker exec xui_proxy_20 cat /etc/nginx/conf.d/default.conf | head -25
