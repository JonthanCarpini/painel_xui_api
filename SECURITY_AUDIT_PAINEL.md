# Auditoria de Segurança — Painel XUI (Laravel) + Comunicação com XUI

**Data:** 2026-02-24  
**Escopo:** Código fonte do painel Laravel (`app/`) e comunicação via `XuiApiService`

---

## Resumo Executivo

Foram identificadas **12 vulnerabilidades** no painel, sendo **3 críticas**, **5 altas** e **4 médias**. A falha mais grave é o **SQL Injection via mysql_query** no sistema de tickets, que permite que um revendedor execute queries SQL arbitrárias no banco de dados do XUI.

---

## 🔴 CRÍTICA 1 — SQL Injection nos Tickets via XUI mysql_query

**Arquivos:**
- `app/Services/XuiApiService.php` (linhas 395-466)
- `app/Http/Controllers/TicketController.php` (linhas 122, 169-171, 186)

**Descrição:** O sistema de tickets usa `addslashes()` para "sanitizar" input do usuário antes de inserir em queries SQL raw que são enviadas ao XUI via `mysql_query`. `addslashes()` é **insuficiente** contra SQL injection — não protege contra encoding multibyte (GBK), não escapa `%` e `_` em LIKE, e é bypassável.

**Vetor de ataque direto:** Um revendedor pode criar um ticket com título contendo SQL injection:
```
Título: test'); DROP TABLE tickets; --
Título: test\'); (SELECT password FROM users WHERE id=1) INTO OUTFILE '/tmp/leak'; --
```

**Código vulnerável:**
```php
// XuiApiService.php:425-430
$safeTitle   = addslashes($subject);  // INSUFICIENTE!
$safeContent = addslashes($content);  // INSUFICIENTE!
$this->runQuery("INSERT INTO tickets (member_id, title, ...) VALUES ({$memberId}, '{$safeTitle}', ...)");
```

**Impacto:** Execução de SQL arbitrário no banco do XUI — leitura de senhas, dados de clientes, manipulação de créditos, exclusão de dados.

**Correção:** Usar prepared statements ou, no mínimo, `mysqli_real_escape_string`. Idealmente, migrar tickets para banco local do Laravel com Eloquent.

---

## 🔴 CRÍTICA 2 — API Key do XUI hardcoded em config com default

**Arquivo:** `config/xui.php`

**Descrição:** A API key do XUI admin está hardcoded como valor default:
```php
'api_key' => env('XUI_API_KEY', '5EE3138A43E3190ED00F031B1107EA30'),
```

Se o `.env` não definir `XUI_API_KEY`, o código usa a key real de produção. Essa key está no git e dá acesso **admin total** ao XUI (criar/deletar linhas, usuários, queries SQL, etc).

**Impacto:** Qualquer pessoa com acesso ao repositório tem controle total do XUI.

**Correção:** Remover defaults sensíveis de `config/xui.php`, exigir que sejam definidos no `.env`.

---

## 🔴 CRÍTICA 3 — API Key trafega em URL (query string)

**Arquivo:** `app/Services/XuiApiService.php` (linhas 28-32, 43-47)

**Descrição:** A API key é enviada como query parameter em TODAS as requests:
```php
// GET: api_key é query param
Http::get($this->baseUrl, ['api_key' => $this->apiKey, ...]);

// POST: api_key na URL
Http::post($this->baseUrl . '?api_key=' . urlencode($this->apiKey) . '&action=...');
```

API keys em URLs são logadas em:
- Access logs do nginx (XUI e painel)
- Logs do Laravel
- Histórico de conexões
- Qualquer proxy intermediário

**Impacto:** Vazamento da API key admin em múltiplos locais.

**Correção:** Mover api_key para header HTTP customizado (ex: `X-Api-Key`). Requer modificação no lado do XUI também.

---

## 🟠 ALTA 4 — Rota /clear-cache sem autenticação

**Arquivo:** `routes/web.php` (linhas 13-23)

**Descrição:** A rota `/clear-cache` executa `Artisan::call('cache:clear')`, `config:clear`, `view:clear` e `route:clear` **sem nenhuma autenticação**.

**Impacto:** Qualquer pessoa pode:
- Limpar cache da aplicação (degradar performance)
- Forçar recarregamento de configurações
- Possível DoS se chamado repetidamente

**Correção:** Adicionar middleware `auth` + `admin`.

---

## 🟠 ALTA 5 — IDOR em Tickets (fechar/responder ticket de outro usuário)

**Arquivos:** `app/Http/Controllers/TicketController.php` (linhas 177-193, 195-204)

**Descrição:** Os métodos `reply()` e `close()` aceitam o ID do ticket na URL mas **não verificam** se o ticket pertence ao usuário logado. Um revendedor pode responder ou fechar tickets de outros revendedores.

**Código vulnerável:**
```php
public function reply(Request $request, $id)
{
    // NÃO valida se o ticket pertence ao usuário
    $this->api->replyTicket((int)$id, $request->message, $isAdminReply);
}

public function close($id)
{
    // NÃO valida se o ticket pertence ao usuário
    $this->api->closeTicket((int)$id);
}
```

**Impacto:** Revendedor pode fechar tickets de outros, responder como se fosse outro usuário.

**Correção:** Verificar `member_id == user->xui_id` antes de reply/close.

---

## 🟠 ALTA 6 — TicketController.show faz SQL injection via $id

**Arquivo:** `app/Http/Controllers/TicketController.php` (linhas 169-171)

**Descrição:** O parâmetro `$id` da rota é interpolado diretamente em queries SQL raw:
```php
$this->api->runQuery("UPDATE tickets SET admin_read = 1 WHERE id = {$id}");
```

Embora o Laravel passe `$id` como string, se não houver validação de tipo, um atacante poderia injetar: `/tickets/1 OR 1=1`.

**Impacto:** SQL injection no banco do XUI.

**Correção:** Cast `(int)$id` já é feito em `getTicket()`, mas `show()` usa `$id` raw.

---

## 🟠 ALTA 7 — Webhook processa pagamento sem validar token (Asaas)

**Arquivo:** `app/Http/Controllers/WebhookController.php` (linhas 44-50)

**Descrição:** Quando o `webhook_auth_token` da Asaas está vazio, `validateAsaasAuthToken()` retorna `true` (aceita qualquer request). E mesmo quando o token é inválido, o webhook **continua processando** (apenas loga warning):

```php
if (!$this->validateAsaasAuthToken($request, $gateway)) {
    Log::warning('...'); // Apenas loga, NÃO rejeita!
}
$result = $this->asaas->processWebhookPayment($payload); // Processa mesmo assim
```

**Impacto:** Atacante pode forjar webhooks de pagamento e creditar revendedores indevidamente.

**Correção:** Retornar 401 se token inválido, exigir token configurado.

---

## 🟠 ALTA 8 — Image Proxy como SSRF (Server-Side Request Forgery)

**Arquivo:** `app/Http/Controllers/ImageProxyController.php`

**Descrição:** O endpoint `/img-proxy?url=...` faz requisição HTTP para qualquer URL fornecida. Embora protegido por `auth`, qualquer revendedor logado pode usar para:
- Escanear rede interna (127.0.0.1, 10.x.x.x, 192.168.x.x)
- Acessar serviços internos (Redis, DB, etc)
- Exfiltrar dados via DNS rebinding

```php
$response = Http::timeout(10)->get($url); // Busca qualquer URL
```

**Impacto:** SSRF — acesso a serviços internos da VPS.

**Correção:** Validar que a URL aponta para IP público, bloquear IPs privados/localhost.

---

## 🟡 MÉDIA 9 — Senhas de clientes trafegam e são exibidas em plain text

**Descrição:** O painel busca senhas de clientes XUI e as exibe em plain text na interface. As senhas trafegam entre painel e XUI em HTTP (não HTTPS).

**Impacto:** Interceptação de senhas em trânsito, exposição visual.

---

## 🟡 MÉDIA 10 — Comunicação Painel→XUI em HTTP (sem TLS)

**Arquivo:** `config/xui.php` — `XUI_BASE_URL` usa `http://`

**Descrição:** Toda comunicação entre painel e XUI é em HTTP plain text, incluindo API key, senhas de usuários, queries SQL.

**Impacto:** Man-in-the-middle pode interceptar API key, dados de clientes, credenciais.

**Correção:** Configurar HTTPS entre painel e XUI.

---

## 🟡 MÉDIA 11 — Autenticação aceita senhas MD5 e plain text

**Arquivo:** `app/Auth/XuiDatabaseUserProvider.php` (linhas 48-56)

**Descrição:** O provider de autenticação aceita 3 formatos de senha:
1. MD5 (`if strlen == 32 && ctype_xdigit → md5 compare`)
2. bcrypt (`password_verify`)
3. **Plain text** (`$password === $hashedPassword`)

**Impacto:** Se a senha for armazenada em plain text, qualquer acesso ao DB expõe credenciais.

---

## 🟡 MÉDIA 12 — authenticateUser busca TODOS os usuários do XUI

**Arquivo:** `app/Services/XuiApiService.php` (linhas 299-333)

**Descrição:** `authenticateUser()` chama `getUsers()` que busca 100.000 registros e itera sobre todos para encontrar um username. Além do impacto em performance, todos os dados de todos os usuários trafegam pela rede a cada login.

**Impacto:** Performance ruim + exposição massiva de dados.

---

## Mapa de Ataque — Painel

```
Internet → Painel (Laravel)
  │
  ├── /clear-cache ← SEM AUTH! Qualquer pessoa limpa caches
  │
  ├── /login (POST) ← Brute force (sem rate limiting próprio)
  │
  ├── /webhook/asaas/{secret} ← Forjar pagamentos (token opcional)
  │
  ├── [AUTH required] ──────────────────────────────────
  │   ├── /img-proxy?url= ← SSRF (scanear rede interna)
  │   │
  │   ├── /tickets/create ← SQL INJECTION via título/conteúdo
  │   ├── /tickets/{id}/reply ← IDOR + SQL injection
  │   ├── /tickets/{id}/close ← IDOR
  │   │
  │   └── [Comunicação Painel→XUI] ──────────────────
  │       ├── HTTP plain text (sem TLS)
  │       ├── API key em query string (vazamento em logs)
  │       ├── mysql_query (SQL raw do painel → XUI)
  │       └── Todas as senhas em plain text no tráfego
```
