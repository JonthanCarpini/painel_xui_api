#!/bin/bash
curl -s "http://109.205.178.143/fXvFkkfq/?api_key=5EE3138A43E3190ED00F031B1107EA30&action=get_users" \
  | python3 -c "
import sys, json
data = json.load(sys.stdin)
users = data.get('data', [])
for u in users:
    name = u.get('username','')
    if name in ['aline0009', 'carpiniadm']:
        print(json.dumps(u, indent=2))
        print()
"
