#!/bin/bash
API_URL="http://109.205.178.143/fXvFkkfq/"
API_KEY="5EE3138A43E3190ED00F031B1107EA30"

echo "=== Testando get_user&id=2 (aline0009) ==="
curl -s "${API_URL}?api_key=${API_KEY}&action=get_user&id=2" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print(json.dumps(data, indent=2))
except Exception as e:
    print('Erro:', e)
"
