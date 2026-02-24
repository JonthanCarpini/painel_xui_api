#!/bin/bash
# ============================================================================
# Painel XUI (Laravel) — Testes de Segurança
# ============================================================================
# USO: bash security_test_painel.sh <PAINEL_URL>
# Exemplo: bash security_test_painel.sh https://p2player.vp1.officex.site
# ============================================================================

TARGET="${1:-https://p2player.vp1.officex.site}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

VULN_COUNT=0
SAFE_COUNT=0

print_header() {
    echo ""
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}  $1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

print_vuln() {
    echo -e "  ${RED}[VULNERAVEL]${NC} $1"
    VULN_COUNT=$((VULN_COUNT + 1))
}

print_safe() {
    echo -e "  ${GREEN}[SEGURO]${NC} $1"
    SAFE_COUNT=$((SAFE_COUNT + 1))
}

print_info() {
    echo -e "  ${YELLOW}[INFO]${NC} $1"
}

print_header "PAINEL XUI — SECURITY TEST SUITE"
echo -e "  Alvo: ${TARGET}"
echo ""

# ============================================================================
# TESTE 1 — /clear-cache sem autenticação
# ============================================================================
print_header "TESTE 1: /clear-cache sem autenticacao"

RESP=$(curl -s --max-time 10 "${TARGET}/clear-cache" 2>/dev/null)
RESP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${TARGET}/clear-cache" 2>/dev/null)

echo "  HTTP Status: ${RESP_CODE}"

if echo "$RESP" | grep -qi "limpos com sucesso\|caches.*cleared\|sucesso"; then
    print_vuln "/clear-cache acessivel SEM autenticacao! Cache limpo com sucesso!"
    echo "  Resposta: $(echo "$RESP" | head -c 200)"
elif [ "$RESP_CODE" = "200" ]; then
    print_vuln "/clear-cache retorna 200 sem autenticacao"
    echo "  Resposta: $(echo "$RESP" | head -c 200)"
elif [ "$RESP_CODE" = "302" ] || [ "$RESP_CODE" = "401" ] || [ "$RESP_CODE" = "403" ]; then
    print_safe "/clear-cache protegido (HTTP ${RESP_CODE})"
else
    print_info "/clear-cache retornou HTTP ${RESP_CODE}"
fi

# ============================================================================
# TESTE 2 — SSRF via Image Proxy
# ============================================================================
print_header "TESTE 2: SSRF via /img-proxy (requer auth)"

# Sem auth
RESP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${TARGET}/img-proxy?url=http://127.0.0.1/" 2>/dev/null)
echo "  Sem auth, HTTP Status: ${RESP_CODE}"

if [ "$RESP_CODE" = "302" ] || [ "$RESP_CODE" = "401" ]; then
    print_safe "/img-proxy requer autenticacao"
elif [ "$RESP_CODE" = "200" ]; then
    print_vuln "/img-proxy acessivel SEM auth — SSRF possivel!"
fi

# Testar se aceita IPs internos (se tivessemos cookie de auth)
print_info "SSRF: /img-proxy?url=http://127.0.0.1 acessivel para users logados"
print_info "SSRF: /img-proxy?url=http://169.254.169.254 (metadata AWS/GCP) acessivel para users logados"
print_info "Para testar SSRF completo, usar cookie de sessao autenticada"

# ============================================================================
# TESTE 3 — Login Brute Force
# ============================================================================
print_header "TESTE 3: Login Brute Force / Rate Limiting"

echo "  Testando 15 logins invalidos rapidos..."
BLOCKED=0
for i in $(seq 1 15); do
    RESP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 \
        -X POST "${TARGET}/login" \
        -d "_token=fake&username=brutetest${i}&password=wrong${i}" \
        -H "Content-Type: application/x-www-form-urlencoded" 2>/dev/null)
    if [ "$RESP_CODE" = "429" ] || [ "$RESP_CODE" = "403" ]; then
        print_safe "Rate limiting ativo — bloqueado apos ${i} tentativas (HTTP ${RESP_CODE})"
        BLOCKED=1
        break
    fi
done

if [ "$BLOCKED" = "0" ]; then
    print_vuln "Sem rate limiting no login apos 15 tentativas rapidas"
fi

# ============================================================================
# TESTE 4 — Webhooks sem autenticacao
# ============================================================================
print_header "TESTE 4: Webhooks — Forjar pagamento"

# Testar webhook com secret aleatório
RESP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 \
    -X POST "${TARGET}/webhook/asaas/test-fake-secret-12345" \
    -H "Content-Type: application/json" \
    -d '{"event":"PAYMENT_RECEIVED","payment":{"id":"pay_123","billingType":"PIX","value":100,"externalReference":"TEST_123"}}' 2>/dev/null)

echo "  Webhook Asaas com secret falso: HTTP ${RESP_CODE}"

if [ "$RESP_CODE" = "200" ]; then
    print_info "Webhook retorna 200 com secret invalido (verifica se gateway existe)"
elif [ "$RESP_CODE" = "404" ]; then
    print_safe "Webhook rejeita secret desconhecido (HTTP 404)"
else
    print_info "Webhook retornou HTTP ${RESP_CODE}"
fi

# Testar se webhook Asaas aceita sem token de auth
RESP=$(curl -s --max-time 10 \
    -X POST "${TARGET}/webhook/asaas/test-fake-secret-12345" \
    -H "Content-Type: application/json" \
    -d '{"event":"PAYMENT_RECEIVED","payment":{"id":"pay_123","billingType":"PIX","value":100,"externalReference":"TEST_123"}}' 2>/dev/null)

if echo "$RESP" | grep -q '"status":"ok"'; then
    print_vuln "Webhook Asaas processou pagamento sem validar token!"
else
    print_safe "Webhook Asaas nao processou pagamento falso"
fi

# ============================================================================
# TESTE 5 — Diretorios e arquivos sensiveis
# ============================================================================
print_header "TESTE 5: Arquivos e Diretorios Expostos"

for P in ".env" ".git/HEAD" ".git/config" "storage/logs/laravel.log" "storage/" "vendor/" "artisan" "config/" "database/" "phpinfo.php" ".htaccess" "composer.json" "composer.lock"; do
    RESP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 3 "${TARGET}/${P}" 2>/dev/null)
    if [ "$RESP_CODE" = "200" ]; then
        CONTENT=$(curl -s --max-time 3 "${TARGET}/${P}" 2>/dev/null | head -c 100)
        if echo "$CONTENT" | grep -qi "APP_KEY\|DB_PASSWORD\|APP_NAME\|ref:\|gitdir\|autoload\|require\|laravel"; then
            print_vuln "/${P} EXPOSTO! Conteudo sensivel acessivel"
        else
            print_info "/${P} retorna 200 (verificar conteudo)"
        fi
    fi
done

# ============================================================================
# TESTE 6 — Headers de seguranca
# ============================================================================
print_header "TESTE 6: Headers de Seguranca HTTP"

HEADERS=$(curl -sI --max-time 5 "${TARGET}/" 2>/dev/null)

# Server header
SERVER_H=$(echo "$HEADERS" | grep -i "^server:" | head -1)
if [ -n "$SERVER_H" ]; then
    echo "  ${SERVER_H}"
fi

# X-Powered-By
if echo "$HEADERS" | grep -qi "x-powered-by"; then
    XPB=$(echo "$HEADERS" | grep -i "x-powered-by" | head -1)
    print_vuln "X-Powered-By expoe tecnologia: ${XPB}"
else
    print_safe "X-Powered-By nao presente"
fi

# X-Content-Type-Options
if echo "$HEADERS" | grep -qi "x-content-type-options"; then
    print_safe "X-Content-Type-Options presente"
else
    print_info "X-Content-Type-Options ausente"
fi

# X-Frame-Options
if echo "$HEADERS" | grep -qi "x-frame-options"; then
    print_safe "X-Frame-Options presente (protecao contra clickjacking)"
else
    print_vuln "X-Frame-Options ausente — vulneravel a clickjacking"
fi

# HSTS
if echo "$HEADERS" | grep -qi "strict-transport-security"; then
    print_safe "HSTS presente"
else
    print_info "HSTS ausente"
fi

# Content-Security-Policy
if echo "$HEADERS" | grep -qi "content-security-policy"; then
    print_safe "Content-Security-Policy presente"
else
    print_info "Content-Security-Policy ausente"
fi

# ============================================================================
# TESTE 7 — Cookie flags
# ============================================================================
print_header "TESTE 7: Cookie Security Flags"

COOKIE_H=$(echo "$HEADERS" | grep -i "set-cookie" | head -1)

if [ -n "$COOKIE_H" ]; then
    echo "  Cookie: $(echo "$COOKIE_H" | head -c 200)"
    
    if echo "$COOKIE_H" | grep -qi "httponly"; then
        print_safe "Cookie tem flag HttpOnly"
    else
        print_vuln "Cookie SEM flag HttpOnly — acessivel via JavaScript (XSS)"
    fi

    if echo "$COOKIE_H" | grep -qi "secure"; then
        print_safe "Cookie tem flag Secure"
    else
        print_info "Cookie sem flag Secure (ok se HTTP)"
    fi

    if echo "$COOKIE_H" | grep -qi "samesite"; then
        print_safe "Cookie tem flag SameSite"
    else
        print_vuln "Cookie SEM flag SameSite — vulneravel a CSRF"
    fi
else
    print_info "Nenhum cookie na resposta inicial"
fi

# ============================================================================
# TESTE 8 — CORS
# ============================================================================
print_header "TESTE 8: CORS Misconfiguration"

CORS_RESP=$(curl -sI --max-time 5 -H "Origin: http://evil.com" "${TARGET}/" 2>/dev/null)

if echo "$CORS_RESP" | grep -qi "access-control-allow-origin: \*"; then
    print_vuln "CORS permite qualquer origin (Access-Control-Allow-Origin: *)"
elif echo "$CORS_RESP" | grep -qi "access-control-allow-origin: http://evil.com"; then
    print_vuln "CORS reflete origin arbitrario!"
else
    print_safe "CORS configurado corretamente"
fi

# ============================================================================
# TESTE 9 — Debug mode / Error disclosure
# ============================================================================
print_header "TESTE 9: Debug Mode / Error Disclosure"

RESP=$(curl -s --max-time 10 "${TARGET}/nonexistent-page-trigger-error-12345" 2>/dev/null)

if echo "$RESP" | grep -qi "whoops\|stack trace\|exception\|APP_DEBUG\|vendor/laravel"; then
    print_vuln "Debug mode ATIVO — expoe stack traces e caminhos internos!"
else
    print_safe "Debug mode desativado (sem stack traces)"
fi

# Testar erro em rota com parametro invalido
RESP2=$(curl -s --max-time 10 "${TARGET}/tickets/abc" 2>/dev/null)

if echo "$RESP2" | grep -qi "whoops\|stack trace\|exception.*sql\|SQLSTATE"; then
    print_vuln "Erros SQL expostos em paginas de erro"
else
    print_safe "Erros internos nao expostos"
fi

# ============================================================================
# TESTE 10 — Comunicacao Painel→XUI (verificar se HTTP ou HTTPS)
# ============================================================================
print_header "TESTE 10: Comunicacao Painel->XUI"

# Verificar via Docker se a config usa HTTP
print_info "Verificando config XUI no servidor..."
XUI_URL=$(docker exec painel_20 php -r 'echo config("xui.base_url");' 2>/dev/null)

if [ -n "$XUI_URL" ]; then
    echo "  XUI Base URL: ${XUI_URL}"
    
    if echo "$XUI_URL" | grep -q "^http://"; then
        print_vuln "Comunicacao Painel->XUI em HTTP plain text! API key e dados expostos"
    elif echo "$XUI_URL" | grep -q "^https://"; then
        print_safe "Comunicacao Painel->XUI usa HTTPS"
    fi
else
    print_info "Nao foi possivel ler config (rodar este teste no servidor)"
fi

# ============================================================================
# TESTE 11 — SQL Injection via Tickets (teste passivo)
# ============================================================================
print_header "TESTE 11: SQL Injection via Tickets (analise de codigo)"

print_vuln "CODIGO: XuiApiService usa addslashes() para sanitizar SQL — INSUFICIENTE"
print_vuln "CODIGO: TicketController.show() interpola \$id direto em SQL raw"
print_vuln "CODIGO: createTicket/replyTicket enviam SQL raw via mysql_query do XUI"
print_info "Para testar: criar ticket com titulo contendo aspas simples"
print_info "Payload: test'); SELECT password FROM users WHERE is_admin=1; --"

# ============================================================================
# TESTE 12 — IDOR em Tickets (analise de codigo)
# ============================================================================
print_header "TESTE 12: IDOR em Tickets (analise de codigo)"

print_vuln "CODIGO: reply() e close() nao verificam se ticket pertence ao user"
print_info "Um revendedor pode chamar POST /tickets/999/reply para responder ticket alheio"
print_info "Um revendedor pode chamar POST /tickets/999/close para fechar ticket alheio"

# ============================================================================
# TESTE 13 — Webhook sem validacao (analise de codigo)
# ============================================================================
print_header "TESTE 13: Webhook Asaas processa sem validar token"

print_vuln "CODIGO: WebhookController nao rejeita request quando token invalido"
print_info "Linha 44-50: apenas Log::warning, nao retorna 401"
print_info "Se webhook_auth_token vazio, aceita qualquer request"

# ============================================================================
# RESUMO
# ============================================================================
print_header "RESUMO FINAL"

echo ""
echo -e "  ${RED}Vulnerabilidades: ${VULN_COUNT}${NC}"
echo -e "  ${GREEN}Seguros: ${SAFE_COUNT}${NC}"
echo ""

if [ $VULN_COUNT -gt 0 ]; then
    echo -e "  ${RED}ATENCAO: Vulnerabilidades detectadas no painel!${NC}"
    echo ""
    echo "  Prioridades de correcao:"
    echo "  1. [CRITICA] SQL Injection nos tickets — migrar para prepared statements"
    echo "  2. [CRITICA] API key hardcoded — remover default de config/xui.php"
    echo "  3. [ALTA] /clear-cache — adicionar middleware auth+admin"
    echo "  4. [ALTA] IDOR tickets — verificar ownership antes de reply/close"
    echo "  5. [ALTA] Webhook — rejeitar requests com token invalido"
    echo "  6. [ALTA] SSRF img-proxy — bloquear IPs privados"
fi

echo ""
echo "  Relatorio completo: SECURITY_AUDIT_PAINEL.md"
echo "  Teste concluido em $(date)"
echo ""
