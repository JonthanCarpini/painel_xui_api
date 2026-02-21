#!/bin/bash
API_URL="http://109.205.178.143/fXvFkkfq/"
API_KEY="5EE3138A43E3190ED00F031B1107EA30"

echo "=== 1. Testando get_users para aline0009 (JSON Bruto) ==="
curl -s "${API_URL}?api_key=${API_KEY}&action=get_users" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    users = data.get('data', [])
    found = False
    for u in users:
        if u.get('username') == 'aline0009':
            print(json.dumps(u, indent=2))
            found = True
            break
    if not found:
        print('Usuário aline0009 não encontrado na lista')
except Exception as e:
    print('Erro ao processar JSON:', e)
"

echo ""
echo "=== 2. Testando action=login (tentativa de validar senha) ==="
# Tentar endpoints comuns de login do XUI/Xtream
curl -s "${API_URL}?api_key=${API_KEY}&action=login&username=aline0009&password=Caps@1261"
echo ""
