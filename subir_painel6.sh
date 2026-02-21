#!/bin/bash
# Sobe painel_6 do zero com nova imagem (API-based, sem DB XUI)

IMAGE="carpini/painelshark:latest"

docker rm -f painel_6 2>/dev/null || true

docker run -d \
  --name painel_6 \
  --restart always \
  --network web_network \
  -v /opt/custom_entrypoint.sh:/usr/local/bin/entrypoint.sh \
  -e APP_NAME="PainelShark" \
  -e APP_ENV=production \
  -e APP_KEY=base64:$(openssl rand -base64 32) \
  -e APP_DEBUG=false \
  -e APP_URL=https://genial.vp1.officex.site \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=mysql_central \
  -e DB_PORT=3306 \
  -e DB_DATABASE=painel_6 \
  -e DB_USERNAME=user_6 \
  -e DB_PASSWORD=6170dffcdb0cc467a2824b07 \
  -e XUI_BASE_URL=http://192.168.100.210/fXvFkkfq/ \
  -e XUI_API_KEY=5EE3138A43E3190ED00F031B1107EA30 \
  -e XUI_HOST=http://109.205.178.143 \
  -e XUI_TIMEOUT=30 \
  -e EVOLUTION_API_URL=https://evo.onpanel.site \
  -e EVOLUTION_API_KEY=evo_standalone_key_2026 \
  --label "traefik.enable=true" \
  --label "traefik.http.routers.painel_6.rule=Host(\`genial.vp1.officex.site\`)" \
  --label "traefik.http.routers.painel_6.entrypoints=websecure" \
  --label "traefik.http.routers.painel_6.tls.certresolver=myresolver" \
  --label "traefik.http.services.painel_6.loadbalancer.server.port=80" \
  "$IMAGE"

echo "Aguardando 25s para inicializar..."
sleep 25

echo ""
echo "=== Logs do painel_6 ==="
docker logs painel_6 --tail 30

echo ""
echo "=== Status ==="
docker ps --format 'table {{.Names}}\t{{.Status}}' | grep painel_6
