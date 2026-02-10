# Painelshark (Painel XUI) — Documentação Técnica Completa

> **Última atualização:** 10/02/2026
> **Repositório:** `JonthanCarpini/Painelshark` (GitHub privado)
> **Imagem Docker:** `carpini/painelshark:latest`

---

## 1. Objetivo do Projeto

O **Painelshark** é um painel web construído em **Laravel 11 + PHP 8.2** que funciona como interface de gerenciamento para servidores **Xtream UI (XUI)** de IPTV. Ele permite que **administradores** e **revendedores** gerenciem clientes, créditos, pacotes, canais e notificações via WhatsApp — tudo através de uma UI moderna com TailwindCSS.

### Público-alvo
- **Administrador (group_id=1):** Controle total — configurações, manutenção, canais, servidores, revendedores.
- **Revendedor (group_id=2):** Gerencia seus próprios clientes, sub-revendedores, créditos e WhatsApp.

### Modelo de Negócio
O painel opera como **SaaS Single-Tenant**: cada cliente (operador IPTV) recebe seu próprio container Docker isolado, com banco de dados separado, mas compartilhando a mesma imagem Docker.

---

## 2. Stack Tecnológica

| Camada | Tecnologia |
|--------|-----------|
| **Backend** | Laravel 11, PHP 8.2-FPM |
| **Frontend** | Blade Templates, TailwindCSS, Bootstrap Icons, Chart.js |
| **Banco Local** | MySQL 8.0 (banco `painel_plus`) |
| **Banco XUI** | MySQL (banco `painel_xui` — legado do Xtream UI) |
| **WhatsApp** | Evolution API v2.3.7 (standalone em `evo.onpanel.site`) |
| **Web Server** | Nginx (dentro do container) |
| **Containerização** | Docker, Docker Hub |
| **Proxy/SSL** | Traefik v2.10 (na VPS) |
| **CI/CD** | GitHub Actions → Docker Hub → VPS |

---

## 3. Arquitetura de Bancos de Dados

O sistema usa **duas conexões MySQL simultâneas**, configuradas em `config/database.php`:

### 3.1. Conexão `mysql` (Banco Próprio: `painel_plus`)

Banco controlado pelo Laravel com migrations. Armazena dados exclusivos do painel.

**Variáveis de ambiente:**
```
DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=painel_plus
DB_USERNAME=painel_user
DB_PASSWORD=...
```

### 3.2. Conexão `xui` (Banco Legado: `painel_xui`)

Banco do Xtream UI. **Somente leitura/escrita direta** — sem migrations do Laravel. O painel lê e escreve diretamente nas tabelas do XUI.

**Variáveis de ambiente:**
```
XUI_DB_HOST=...
XUI_DB_DATABASE=painel_xui
XUI_DB_USERNAME=painel_user
XUI_DB_PASSWORD=...
```

---

## 4. Tabelas do Banco Próprio (`painel_plus`)

Todas gerenciadas por migrations Laravel em `database/migrations/`.

| Tabela | Descrição | Campos-chave |
|--------|-----------|-------------|
| `panel_users` | Espelho local dos usuários XUI | `id`, `xui_id` (unique), `username`, `group_id`, `phone` |
| `user_preferences` | Preferências key-value por usuário | `panel_user_id` (FK), `key`, `value`, `type` |
| `app_settings` | Configurações globais do painel | `key` (unique), `value`, `description` |
| `announcements` | Anúncios com período de exibição | `title`, `message`, `type`, `is_active`, `starts_at`, `ends_at` |
| `notices` | Avisos do admin para revendedores | `title`, `message`, `type`, `color`, `is_active`, `priority` |
| `notice_reads` | Controle de leitura de avisos | `user_id`, `notice_id` |
| `client_details` | Dados extras de clientes (telefone) | `xui_client_id` (unique), `phone`, `notes` |
| `client_applications` | Apps IPTV configuráveis | `name`, `downloader_id`, `direct_link`, `is_active` |
| `dns_servers` | Servidores DNS disponíveis | `name`, `url`, `is_active` |
| `ticket_categories` | Categorias de tickets | `name`, `responsible`, `phone` |
| `ticket_extras` | Metadados extras de tickets XUI | `ticket_id`, `category_id` (FK) |
| `test_channels` | Canais para teste (sincronizados via M3U) | `name`, `group_title`, `stream_url`, `stream_id` |
| `whatsapp_settings` | Config WhatsApp por revendedor | `panel_user_id` (FK unique), `instance_name`, `notifications_enabled`, mensagens, `send_start_time`, `send_interval_seconds`, `connection_status` |
| `notification_logs` | Log de notificações WhatsApp enviadas | `whatsapp_setting_id` (FK), `xui_client_id`, `notification_type`, `sent_date`, `success` — unique composto |
| `users` | Tabela padrão Laravel (não usada para auth) | — |
| `cache`, `sessions`, `jobs` | Tabelas de infraestrutura Laravel | — |

---

## 5. Tabelas Principais do Banco XUI (`painel_xui`)

Tabelas do Xtream UI que o painel lê/escreve diretamente:

| Tabela | Model | Descrição |
|--------|-------|-----------|
| `users` | `XuiUser` | Administradores e revendedores. `member_group_id`: 1=Admin, 2=Revenda |
| `lines` | `Line` | Clientes IPTV (linhas). `exp_date` em Unix timestamp. `member_id` = dono |
| `lines_live` | `LineLive` | Conexões ativas em tempo real. `date_end=null` = online |
| `users_packages` | `Package` | Pacotes de assinatura (duração, créditos, bouquets) |
| `bouquets` | `Bouquet` | Grupos de canais (JSON de IDs) |
| `streams` | `Stream` | Canais/filmes/séries. `type`: 1=Live, 2=Movie, 3=Created Live |
| `streams_series` | — | Séries (consulta direta via DB) |
| `streams_servers` | — | Status dos streams por servidor (pid, stream_status) |
| `servers` | `Server` | Servidores de streaming (load balancers) |
| `users_credits_logs` | `CreditLog` | Histórico de movimentação de créditos |
| `users_logs` | `UserLog` | Log de ações (criação, renovação de linhas) |
| `login_logs` | `LoginLog` | Histórico de logins |
| `tickets` | `Ticket` | Sistema de tickets de suporte |
| `tickets_replies` | `TicketReply` | Respostas dos tickets |
| `settings` | — | Configurações globais do XUI (consulta direta) |

---

## 6. Estrutura de Pastas

```
painel_xui/
├── .github/workflows/
│   └── docker-publish.yml      # CI/CD: Build + Push para Docker Hub
├── app/                         # ← RAIZ DO LARAVEL
│   ├── app/
│   │   ├── Auth/                # Autenticação customizada
│   │   │   ├── XuiDatabaseUserProvider.php  # Provider que autentica via banco XUI
│   │   │   ├── XuiUser.php                  # Objeto Authenticatable (legado, não usado)
│   │   │   └── XuiUserProvider.php          # Provider via API (legado, não usado)
│   │   ├── Console/Commands/
│   │   │   └── SendExpiryNotifications.php  # Cron: envia notificações WhatsApp
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── ChannelController.php    # CRUD de canais (admin)
│   │   │   │   │   └── ServerController.php     # Gestão de servidores (admin)
│   │   │   │   ├── AuthController.php           # Login/Logout
│   │   │   │   ├── ChannelTestController.php    # Teste de canais ao vivo
│   │   │   │   ├── ClientController.php         # CRUD completo de clientes
│   │   │   │   ├── CreditLogController.php      # Histórico de créditos
│   │   │   │   ├── DashboardController.php      # Dashboard com stats
│   │   │   │   ├── MaintenanceController.php    # Modo manutenção
│   │   │   │   ├── MonitorController.php        # Conexões ao vivo
│   │   │   │   ├── NoticeController.php         # Avisos para revendedores
│   │   │   │   ├── ProfileController.php        # Perfil e preferências
│   │   │   │   ├── ResellerController.php       # CRUD de revendedores
│   │   │   │   ├── SettingsController.php       # Configurações globais (admin)
│   │   │   │   ├── TicketController.php         # Sistema de tickets
│   │   │   │   ├── TicketCategoryController.php # Categorias de tickets
│   │   │   │   ├── UpdatesController.php        # Changelog/atualizações
│   │   │   │   └── WhatsappController.php       # Módulo WhatsApp completo
│   │   │   └── Middleware/
│   │   │       ├── AdminMiddleware.php          # Bloqueia não-admins
│   │   │       └── CheckMaintenance.php         # Modo manutenção
│   │   ├── Models/              # 27 models (ver seções 4 e 5)
│   │   ├── Providers/
│   │   │   └── AppServiceProvider.php  # Registra auth provider + view composers
│   │   └── Services/
│   │       ├── ChannelService.php       # Sync canais via M3U
│   │       ├── EvolutionService.php     # Client da Evolution API (WhatsApp)
│   │       ├── LineService.php          # Criação/renovação de linhas
│   │       └── XuiApiService.php        # Client da API REST do XUI
│   ├── bootstrap/app.php        # Registro de middlewares
│   ├── config/
│   │   ├── auth.php             # Guard 'web' usa provider 'xui_db'
│   │   ├── database.php         # Conexões 'mysql' e 'xui'
│   │   ├── services.php         # Config Evolution API
│   │   └── xui.php              # Config API XUI (base_url, api_key, bouquet_blacklist)
│   ├── database/migrations/     # 19 migrations
│   ├── resources/views/         # Blade templates
│   │   ├── layouts/app.blade.php  # Layout principal + sidebar
│   │   ├── auth/                  # Login
│   │   ├── dashboard/             # Dashboard
│   │   ├── clients/               # CRUD clientes
│   │   ├── whatsapp/              # Módulo WhatsApp (connection, settings, notifications)
│   │   ├── settings/              # Configurações admin
│   │   └── ...
│   ├── routes/
│   │   ├── web.php              # Todas as rotas web
│   │   └── console.php          # Comandos agendados (cron)
│   └── .env.example             # Template de variáveis de ambiente
├── docker/
│   ├── entrypoint.sh            # Script de inicialização do container
│   └── nginx/default.conf       # Config Nginx
├── Dockerfile                   # Build da imagem Docker
├── docker-compose.saas.yml      # Infra SaaS (Traefik + MySQL + PHPMyAdmin)
├── update_containers.sh         # Script de atualização de todos os containers
├── spawn_client.sh              # Provisionamento de novo cliente SaaS
└── *.md                         # Documentações diversas
```

---

## 7. Sistema de Autenticação

O painel **não usa** o sistema de autenticação padrão do Laravel. Em vez disso, implementa um **User Provider customizado** que autentica diretamente contra a tabela `users` do banco XUI.

### Fluxo de Login

1. Usuário submete `username` + `password` no formulário de login.
2. O guard `web` usa o provider `xui_db` (registrado em `AppServiceProvider`).
3. `XuiDatabaseUserProvider::retrieveByCredentials()` busca o usuário na tabela `users` do banco `xui`.
4. `validateCredentials()` verifica a senha usando **dois métodos** (compatibilidade):
   - `password_verify()` (bcrypt)
   - `md5()` (legado do XUI)
5. Se válido, o Laravel cria a sessão com o model `XuiUser` (Eloquent).
6. No login, o `AuthController` também:
   - Cria/atualiza um `PanelUser` local (espelho) via `updateOrCreate`.
   - Registra um `LoginLog` no banco XUI.
   - Atualiza `last_login` e `ip` do usuário XUI.

### Configuração (`config/auth.php`)

```php
'guards' => ['web' => ['driver' => 'session', 'provider' => 'xui_users']],
'providers' => ['xui_users' => ['driver' => 'xui_db']],
```

### Middlewares

| Middleware | Alias | Descrição |
|-----------|-------|-----------|
| `AdminMiddleware` | `admin` | Bloqueia acesso se `Auth::user()->isAdmin()` retorna `false` |
| `CheckMaintenance` | `maintenance` | Bloqueia revendedores se modo manutenção ativo em `AppSetting` |

---

## 8. Funcionalidades Principais

### 8.1. Dashboard (`DashboardController`)

- **Admin:** Total de clientes, ativos, expirados, online agora, revendedores, canais (live/filmes/séries), status do servidor principal (CPU, RAM, disco, rede), top 5 revendedores, últimas recargas.
- **Revendedor:** Clientes totais/ativos/expirados/vencendo hoje, vendas do dia/mês, testes do dia/mês, online agora, sub-revendedores, top 5 sub-revendas.
- **Gráficos:** Chart.js com dados de 7 e 30 dias (clientes, testes, revendedores, recargas).
- **Clientes vencendo:** Lista dos próximos 7 dias com scroll.
- **Modal de Teste Rápido:** Criação de teste diretamente do dashboard.

### 8.2. Gestão de Clientes (`ClientController`)

- **Listagem:** Paginação, busca por username/senha/telefone, filtros por status (ativo/expirado/bloqueado), tipo (cliente/teste), revendedor, filtros rápidos (hoje/7d/30d).
- **Criação:** Oficial (consome créditos) ou Teste (duração customizada). Salva no XUI + `ClientDetail` local.
- **Edição:** Atualiza username, senha, pacote, bouquets, telefone, status.
- **Renovação:** Normal (escolhe pacote/duração) ou "Em Confiança" (usa pacote pré-configurado pelo admin).
- **Sincronização:** Importa clientes do XUI para tabela local `client_details`.
- **Geração M3U:** URLs M3U e HLS usando DNS do revendedor ou IP principal.
- **Mensagem do Cliente:** Template configurável com dados do cliente + apps.
- **Envio WhatsApp:** Envia mensagem direta via Evolution API.
- **Exportação:** Exportação de dados de clientes.

### 8.3. Gestão de Revendedores (`ResellerController`)

- Listagem de revendedores com hierarquia (árvore de sub-revendas via `getAllSubResellerIds()`).
- Criação de sub-revendedores.
- Recarga de créditos (com log em `users_credits_logs`).
- Visualização de detalhes e clientes de cada revendedor.

### 8.4. Sistema de Tickets (`TicketController`)

- Criação de tickets com categorias (`TicketCategory`).
- Respostas em thread (`TicketReply`).
- Status: aberto, em andamento, fechado.
- Metadados extras via `TicketExtra`.
- Contagem de tickets não lidos compartilhada via View Composer.

### 8.5. Monitoramento (`MonitorController`)

- Conexões ativas em tempo real (tabela `lines_live`).
- Filtros por revendedor e status.

### 8.6. Teste de Canais (`ChannelTestController`)

- Lista de canais sincronizados de uma "revenda fantasma" (M3U).
- Player integrado para teste ao vivo (HLS.js para `.m3u8`, nativo para `.mp4`).
- Sincronização automática via comando agendado.
- **URLs opacas:** As URLs de stream são geradas sem credenciais do fantasma. Em vez de `https://xui.domain/live/user/pass/6.m3u8`, o browser recebe `https://xui.domain/stream/live/6.m3u8`. O proxy Nginx (ver seção 8.11) injeta as credenciais internamente.
- **`buildOpaqueStreamUrl()`:** Método privado que gera URLs no formato `/stream/{type}/{id}.{ext}` usando o `stream_id` e extensão extraída da URL original.

### 8.7. Configurações Admin (`SettingsController`)

- **Servidor XUI:** Nome, timezone, prebuffer, flood limit, API key.
- **Aplicativos:** CRUD de apps IPTV (nome, link, código de ativação).
- **DNS:** CRUD de servidores DNS para geração de URLs.
- **Avisos:** CRUD de notices para revendedores.
- **Mensagem do Cliente:** Template global com variáveis.
- **Revenda Fantasma:** Credenciais para sync de canais de teste. Ao salvar novas credenciais, notifica o SaaS via HTTP para atualizar o proxy Nginx (`notifySaasGhostUpdate()`).
- **Pacote de Confiança:** Pacote padrão para renovação rápida.

### 8.8. Manutenção (`MaintenanceController`)

- Toggle de modo manutenção (bloqueia revendedores).
- Toggle de bloqueio de testes.
- Desabilitar criação de trials no XUI.
- Visualização de servidores e streams.
- Execução manual de rotação de ghost.

### 8.9. Perfil (`ProfileController`)

- Visão geral do usuário (dados do XUI).
- Preferências key-value (tema, idioma, etc).

### 8.10. Logs de Crédito (`CreditLogController`)

- Histórico completo de movimentações de créditos.
- Filtros por período e tipo.

### 8.11. Proxy XUI para Streams HTTPS

O XUI serve streams via HTTP no IP direto (ex: `http://109.205.178.143/live/user/pass/6.m3u8`). Isso causa **Mixed Content** quando o painel é acessado via HTTPS. Além disso, o XUI valida o IP TCP do cliente, impedindo proxy server-side via Laravel.

**Solução:** Um container **Nginx proxy** (`xui_proxy_{id}`) por instância, com SSL via Traefik:

```
Browser (HTTPS) → Traefik → xui_proxy_{id} (Nginx) → XUI (HTTP)
                   ↑ SSL                              ↑ proxy_pass
```

**Domínio:** `xui.{domain}` (ex: `xui.genial.vp1.officex.site`)

**Funcionalidades do proxy:**
- **`location /stream/`** — URLs opacas sem credenciais. O Nginx injeta `user/pass` do fantasma internamente via `proxy_pass`.
- **`location /`** — Fallback para proxy direto (tokens, auth, HLS chunks após redirect).
- **`proxy_redirect`** — Reescreve redirects 302 do XUI de `http://IP` para `https://xui.domain`.

**Ciclo de vida:**
- Criado automaticamente pelo SaaS ao provisionar/atualizar instância (`DockerService::ensureXuiProxy()`).
- Removido ao deletar instância (`DockerService::removeXuiProxy()`).
- Credenciais atualizadas quando o fantasma rotaciona (via API do SaaS).

**Fluxo de atualização de credenciais:**
```
ghost:rotate (ou SettingsController)
  → Salva nova senha no banco local
  → POST {SAAS_API_URL}/api/instance/{INSTANCE_TOKEN}/ghost-credentials
  → SaaS reescreve config Nginx com novas credenciais
  → docker exec xui_proxy_{id} nginx -s reload
```

---

## 9. Relação com o Servidor XUI

O painel se comunica com o XUI de **duas formas**:

### 9.1. Acesso Direto ao Banco de Dados

A maioria das operações usa **queries diretas** na conexão `xui`:
- Leitura/escrita de `lines`, `users`, `bouquets`, `users_packages`, etc.
- Consultas de `streams`, `servers`, `settings`.
- Logs de crédito e login.

### 9.2. API REST do XUI (`XuiApiService`)

Usada para operações que requerem processamento do lado do XUI:
- Autenticação de usuários (método alternativo, legado).
- Operações que disparam hooks internos do XUI.

**Configuração (`config/xui.php`):**
```php
'base_url' => env('XUI_API_BASE_URL'),
'api_key'  => env('XUI_API_KEY'),
'timeout'  => env('XUI_API_TIMEOUT', 30),
'bouquet_blacklist' => [/* IDs de bouquets a esconder */],
```

### 9.3. Fluxo de Criação de Linha (`LineService::createLine`)

1. Valida se username já existe no XUI.
2. Se oficial: verifica créditos do revendedor, deduz créditos, registra log.
3. Calcula `exp_date` baseado no pacote/duração.
4. Insere na tabela `lines` do XUI com bouquets em JSON.
5. Registra `UserLog` no XUI.
6. Cria `ClientDetail` no banco local.

### 9.4. Fluxo de Renovação (`LineService::renewLine`)

1. Busca a linha existente.
2. Verifica créditos do revendedor (se não admin).
3. Calcula nova `exp_date` (a partir de agora se expirado, ou soma ao existente).
4. Atualiza a linha no XUI.
5. Deduz créditos e registra log.

---

## 10. Módulo WhatsApp

### 10.1. Arquitetura

O módulo usa a **Evolution API** (standalone em `evo.onpanel.site`) para envio de mensagens WhatsApp. Cada revendedor pode ter sua própria instância/sessão.

**Componentes:**
- `WhatsappController` — Rotas web (conexão, configurações, painel de notificações).
- `EvolutionService` — Client HTTP para a Evolution API.
- `WhatsappSetting` — Configurações por revendedor.
- `NotificationLog` — Controle de envios (evita duplicatas).
- `SendExpiryNotifications` — Comando agendado.

### 10.2. Fluxo de Conexão

1. Revendedor acessa "WhatsApp > Conexão".
2. Cria instância na Evolution API via `EvolutionService::createInstance()`.
3. Exibe QR Code para scan.
4. Polling de status até `connected`.
5. Status salvo em `WhatsappSetting::connection_status`.

### 10.3. Notificações Automáticas de Vencimento

O comando `notifications:send-expiry` (agendado a cada 5 minutos):

1. Busca todos os `WhatsappSetting` com `notifications_enabled=true` e `connection_status=connected`.
2. Para cada configuração:
   - Verifica se está dentro do horário permitido (`send_start_time`).
   - Busca linhas que vencem em 1, 3 e 7 dias.
   - Para cada linha, verifica se já existe `NotificationLog` para evitar duplicata.
   - Envia mensagem personalizada via `EvolutionService::sendText()`.
   - Registra em `NotificationLog`.
   - Aplica delay entre envios (`send_interval_seconds`).

### 10.4. Mensagens Personalizáveis

Templates configuráveis por revendedor com variáveis:
- `{nome}` — Username do cliente
- `{dias}` — Dias até vencimento
- `{vencimento}` — Data de vencimento formatada
- `{plano}` — Nome do pacote

### 10.5. Configuração da Evolution API

**Variáveis de ambiente:**
```
EVOLUTION_API_URL=https://evo.onpanel.site
EVOLUTION_API_KEY=evo_standalone_key_2026
```

**Endpoints usados (`EvolutionService`):**
- `POST /instance/create` — Criar instância
- `DELETE /instance/delete/{name}` — Deletar instância
- `GET /instance/connectionState/{name}` — Status da conexão
- `GET /instance/connect/{name}` — Obter QR Code
- `POST /instance/logout/{name}` — Desconectar
- `POST /instance/restart/{name}` — Reiniciar
- `GET /instance/fetchInstances` — Listar instâncias
- `POST /message/sendText/{name}` — Enviar mensagem de texto

---

## 11. Comandos Agendados (Cron)

Definidos em `routes/console.php`:

| Comando | Frequência | Descrição |
|---------|-----------|-----------|
| `ghost:rotate` | Diário (configurável) | Rotaciona credenciais da revenda fantasma, re-sincroniza canais de teste e notifica o SaaS para atualizar o proxy Nginx |
| `notifications:send-expiry` | A cada 5 minutos | Envia notificações WhatsApp de vencimento |

**Importante:** O scheduler do Laravel deve estar configurado no cron do container:
```bash
* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1
```

---

## 12. Relação com o Painel SaaS

O Painelshark opera como produto SaaS com a seguinte arquitetura:

### 12.1. Infraestrutura Central (`docker-compose.saas.yml`)

```
┌─────────────────────────────────────────────┐
│                    VPS                       │
│                                              │
│  ┌──────────┐  ┌──────────┐  ┌───────────┐  │
│  │ Traefik  │  │  MySQL   │  │ PHPMyAdmin │  │
│  │ (proxy)  │  │ (central)│  │  (debug)   │  │
│  └────┬─────┘  └────┬─────┘  └───────────┘  │
│       │              │                        │
│  ┌────┴──────────────┴──────────────────┐    │
│  │         Docker Network (web)          │    │
│  ├──────────┬──────────┬──────────┐      │    │
│  │Container │Container │Container │ ...  │    │
│  │Cliente A │Cliente B │Cliente C │      │    │
│  │(painel)  │(painel)  │(painel)  │      │    │
│  └──────────┴──────────┴──────────┘      │    │
└─────────────────────────────────────────────┘
```

**Serviços centrais:**
- **Traefik v2.10:** Reverse proxy com SSL automático (Let's Encrypt). Roteia domínios para containers.
- **MySQL 8.0:** Banco compartilhado com schemas isolados por cliente.
- **PHPMyAdmin:** Acesso de debug ao banco.

### 12.2. Provisionamento de Clientes (`spawn_client.sh`)

O script automatiza a criação de novos clientes:

1. **Cria banco de dados** no MySQL central:
   ```bash
   mysql -u root -p -e "CREATE DATABASE painel_CLIENTE;"
   mysql -u root -p -e "GRANT ALL ON painel_CLIENTE.* TO 'painel_user'@'%';"
   ```

2. **Sobe container Docker** com variáveis específicas:
   ```bash
   docker run -d \
     --name painelshark_CLIENTE \
     --network web \
     -e DB_DATABASE=painel_CLIENTE \
     -e XUI_DB_HOST=... \
     -e XUI_DB_DATABASE=... \
     -l "traefik.http.routers.CLIENTE.rule=Host(\`dominio.com\`)" \
     -l "traefik.http.routers.CLIENTE.tls.certresolver=letsencrypt" \
     carpini/painelshark:latest
   ```

3. O `entrypoint.sh` do container:
   - Inicia Nginx
   - Ajusta permissões
   - Executa `php artisan migrate --force`
   - Executa `php artisan optimize`
   - Inicia PHP-FPM

### 12.3. Isolamento

Cada container:
- Tem seu **próprio banco** `painel_plus` (schema isolado no MySQL central).
- Conecta ao **banco XUI do cliente** (pode ser remoto).
- Tem suas **próprias variáveis de ambiente** (API keys, URLs).
- É acessível via **domínio próprio** (roteado pelo Traefik).

---

## 13. Deploy

### 13.1. CI/CD via GitHub Actions (`.github/workflows/docker-publish.yml`)

**Trigger:** Push na branch `main` ou dispatch manual.

**Fluxo:**
1. Checkout do código.
2. Login no Docker Hub (`DOCKER_USERNAME` / `DOCKER_PASSWORD` como secrets).
3. Build da imagem Docker multi-plataforma.
4. Push para `carpini/painelshark:latest`.

**Secrets necessários no GitHub:**
- `DOCKER_USERNAME` — Usuário do Docker Hub
- `DOCKER_PASSWORD` — Token/senha do Docker Hub

### 13.2. Dockerfile

```dockerfile
FROM php:8.2-fpm

# Instala extensões PHP: pdo_mysql, mbstring, xml, zip, gd, bcmath, redis
# Instala Nginx e Composer
# Copia código para /var/www
# Executa composer install --no-scripts
# Configura permissões (www-data)
# Expõe porta 80
# Entrypoint: docker/entrypoint.sh
```

**Pontos importantes:**
- `composer install --no-scripts` — Evita erro de `artisan package:discover` sem banco.
- Otimizações (`config:cache`, `route:cache`) rodam no `entrypoint.sh`, não no build.

### 13.3. Atualização de Containers em Produção (`update_containers.sh`)

Script para atualizar **todos** os containers de clientes:

1. `docker pull carpini/painelshark:latest`
2. Para cada container `painelshark_*`:
   - Salva variáveis de ambiente e labels Traefik.
   - Para e remove o container antigo.
   - Recria com a nova imagem preservando env vars e labels.
   - Ajusta permissões.
   - Executa `php artisan migrate --force`.
   - Executa `php artisan optimize`.

### 13.4. Deploy Manual (Git na VPS)

Alternativa sem Docker Hub:

```bash
# Na VPS
cd /var/www/painel
git pull origin main
docker compose -f docker-compose.saas.yml up -d --build
# Para cada container:
docker exec -it painelshark_CLIENTE php artisan migrate --force
docker exec -it painelshark_CLIENTE php artisan optimize
```

---

## 14. Variáveis de Ambiente

Referência completa baseada no `.env.example`:

### Aplicação Laravel
| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `APP_NAME` | Nome da aplicação | `PainelShark` |
| `APP_ENV` | Ambiente | `production` |
| `APP_KEY` | Chave de criptografia | `base64:...` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | URL base | `https://painel.dominio.com` |

### Banco Local (Painel Plus)
| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `DB_CONNECTION` | Driver | `mysql` |
| `DB_HOST` | Host do MySQL | `mysql_central` (Docker) |
| `DB_PORT` | Porta | `3306` |
| `DB_DATABASE` | Nome do banco | `painel_plus` |
| `DB_USERNAME` | Usuário | `painel_user` |
| `DB_PASSWORD` | Senha | `***` |

### Banco XUI (Xtream UI)
| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `XUI_DB_HOST` | Host do banco XUI | `192.168.100.209` |
| `XUI_DB_PORT` | Porta | `3306` |
| `XUI_DB_DATABASE` | Nome do banco | `painel_xui` |
| `XUI_DB_USERNAME` | Usuário | `painel_user` |
| `XUI_DB_PASSWORD` | Senha | `***` |

### API XUI
| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `XUI_API_BASE_URL` | URL base da API XUI | `http://192.168.100.209/kIzFSjQu/` |
| `XUI_API_KEY` | Chave da API | `DFE74ECCBA19D32DCD758C4D3D5AF0F6` |
| `XUI_API_TIMEOUT` | Timeout em segundos | `30` |

### Evolution API (WhatsApp)
| Variável | Descrição | Exemplo |
|----------|-----------|--------|
| `EVOLUTION_API_URL` | URL da Evolution API | `https://evo.onpanel.site` |
| `EVOLUTION_API_KEY` | API Key | `evo_standalone_key_2026` |

### SaaS API (Comunicação com o Painel SaaS)
| Variável | Descrição | Exemplo |
|----------|-----------|--------|
| `SAAS_API_URL` | URL base da API do SaaS | `https://admin.onpanel.site` |
| `INSTANCE_TOKEN` | Token único da instância (gerado pelo SaaS) | `abc123...` |

> Essas variáveis são injetadas automaticamente pelo `DockerService` do SaaS ao criar/atualizar containers. São usadas pelo `ghost:rotate` e `SettingsController` para notificar o SaaS quando as credenciais do fantasma mudam, permitindo que o proxy Nginx seja atualizado com as novas credenciais.

### Cache e Sessão
| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `CACHE_STORE` | Driver de cache | `database` |
| `SESSION_DRIVER` | Driver de sessão | `database` |
| `SESSION_LIFETIME` | Duração da sessão (min) | `120` |

---

## 15. Pontos Sensíveis

### 15.1. Segurança

- **Credenciais em texto:** As senhas dos clientes XUI são armazenadas em texto plano na tabela `lines` (legado do XUI). O painel não tem controle sobre isso.
- **MD5 para autenticação:** O `XuiDatabaseUserProvider` aceita MD5 como fallback de autenticação. Isso é necessário para compatibilidade com o XUI legado, mas é criptograficamente fraco.
- **API Keys no `.env`:** As chaves da API XUI e Evolution API ficam em variáveis de ambiente. **Nunca** commitar o `.env` no Git.
- **Acesso ao banco XUI:** O painel tem acesso de leitura/escrita total ao banco do XUI. Um bug pode corromper dados críticos.
- **Sem rate limiting:** Não há rate limiting nas rotas de API/AJAX. Considerar implementar para produção.

### 15.2. Dados Sensíveis

- **Telefones de clientes:** Armazenados em `client_details.phone` e potencialmente em `lines.admin_notes`.
- **Créditos:** Movimentações financeiras em `users_credits_logs`. Erros podem causar perda/ganho indevido.
- **Sessões WhatsApp:** As instâncias da Evolution API mantêm sessões ativas. Perda do container da Evolution = perda de todas as sessões.

### 15.3. Dependências Externas

- **Evolution API:** Se o serviço cair, todas as notificações WhatsApp param. O painel não tem fallback.
- **Banco XUI remoto:** Se o servidor XUI ficar offline, o painel inteiro para (autenticação depende dele).
- **Docker Hub:** Se o Docker Hub ficar indisponível, novos deploys falham.

---

## 16. Pontos Críticos

### 16.1. Performance

- **Queries N+1:** O `DashboardController::getResellerStats()` faz queries individuais para cada sub-revendedor no loop de `topResellers`. Considerar otimizar com subquery ou join.
- **Cache agressivo:** Muitos dados são cacheados por 60s-3600s. Alterações podem não refletir imediatamente.
- **`getAllSubResellerIds()`:** Método recursivo que busca toda a árvore de sub-revendedores. Para hierarquias profundas, pode ser lento.

### 16.2. Consistência de Dados

- **Dois bancos:** Dados de um mesmo cliente existem em dois bancos diferentes (`lines` no XUI e `client_details` no local). Podem ficar dessincronizados.
- **Sincronização manual:** O método `ClientController::sync()` importa do XUI para o local, mas não é automático. Clientes criados diretamente no XUI não terão `client_details`.
- **Créditos:** A dedução de créditos e criação de linha não são atômicas (não há transação cross-database). Uma falha entre as duas operações pode causar inconsistência.

### 16.3. Escalabilidade

- **Container por cliente:** Cada cliente SaaS roda em um container separado. Com muitos clientes, o consumo de RAM na VPS cresce linearmente.
- **MySQL compartilhado:** Todos os bancos `painel_plus` ficam no mesmo MySQL. Sob carga alta, pode ser gargalo.
- **Evolution API centralizada:** Todas as instâncias WhatsApp de todos os clientes passam pela mesma Evolution API.

### 16.4. Manutenção

- **Migrations cross-database:** O Laravel não gerencia migrations do banco XUI. Alterações no schema do XUI (atualizações do Xtream UI) podem quebrar o painel.
- **Atualização de containers:** O `update_containers.sh` para e recria containers. Há downtime durante a atualização.
- **Sem health checks:** Os containers não têm health checks configurados. Se o PHP-FPM morrer, o container continua "rodando" mas sem servir requisições.

---

## 17. Mapa de Rotas

### Rotas Públicas
| Método | URI | Controller | Descrição |
|--------|-----|-----------|-----------|
| GET | `/login` | `AuthController@showLogin` | Página de login |
| POST | `/login` | `AuthController@login` | Processar login |
| POST | `/logout` | `AuthController@logout` | Logout |

### Rotas Autenticadas (middleware `auth` + `maintenance`)
| Método | URI | Controller | Descrição |
|--------|-----|-----------|-----------|
| GET | `/dashboard` | `DashboardController@index` | Dashboard |
| GET | `/clients` | `ClientController@index` | Lista de clientes |
| GET | `/clients/create` | `ClientController@create` | Form criar cliente |
| POST | `/clients` | `ClientController@store` | Salvar cliente |
| GET | `/clients/create-trial` | `ClientController@createTrial` | Form criar teste |
| POST | `/clients/trial` | `ClientController@storeTrial` | Salvar teste |
| GET | `/clients/{id}/edit` | `ClientController@edit` | Form editar cliente |
| PUT | `/clients/{id}` | `ClientController@update` | Atualizar cliente |
| POST | `/clients/{id}/renew` | `ClientController@renew` | Renovar cliente |
| POST | `/clients/{id}/renew-trust` | `ClientController@renewTrust` | Renovar em confiança |
| POST | `/clients/sync` | `ClientController@sync` | Sincronizar XUI→Local |
| GET | `/clients/{id}/m3u` | `ClientController@generateM3u` | Gerar URLs M3U |
| GET | `/clients/{id}/m3u-data` | `ClientController@getM3uData` | URLs M3U (JSON) |
| GET | `/clients/{id}/message` | `ClientController@getMessage` | Mensagem do cliente |
| POST | `/clients/send-whatsapp` | `ClientController@sendWhatsapp` | Enviar WhatsApp |
| GET | `/resellers` | `ResellerController@index` | Lista revendedores |
| GET | `/tickets` | `TicketController@index` | Lista tickets |
| GET | `/monitor` | `MonitorController@index` | Conexões ao vivo |
| GET | `/channel-test` | `ChannelTestController@index` | Teste de canais |
| GET | `/credit-logs` | `CreditLogController@index` | Logs de crédito |
| GET | `/profile` | `ProfileController@index` | Perfil |

### Rotas WhatsApp (middleware `auth`)
| Método | URI | Controller | Descrição |
|--------|-----|-----------|-----------|
| GET | `/whatsapp/connection` | `WhatsappController@connection` | Página de conexão |
| POST | `/whatsapp/create-instance` | `WhatsappController@createInstance` | Criar instância |
| GET | `/whatsapp/qr-code` | `WhatsappController@getQrCode` | Obter QR Code |
| GET | `/whatsapp/status` | `WhatsappController@getStatus` | Status da conexão |
| POST | `/whatsapp/confirm-scan` | `WhatsappController@confirmScan` | Confirmar scan |
| POST | `/whatsapp/disconnect` | `WhatsappController@disconnect` | Desconectar |
| DELETE | `/whatsapp/delete` | `WhatsappController@deleteInstance` | Deletar instância |
| GET | `/whatsapp/settings` | `WhatsappController@settings` | Configurações |
| POST | `/whatsapp/settings` | `WhatsappController@updateSettings` | Salvar configurações |
| GET | `/whatsapp/notifications` | `WhatsappController@notifications` | Painel de notificações |

### Rotas Admin (middleware `auth` + `admin`)
| Método | URI | Controller | Descrição |
|--------|-----|-----------|-----------|
| GET | `/settings` | `SettingsController@index` | Configurações globais |
| POST | `/settings` | `SettingsController@update` | Salvar configurações |
| GET | `/settings/maintenance` | `MaintenanceController@index` | Manutenção |
| POST | `/settings/maintenance` | `MaintenanceController@update` | Salvar manutenção |

---

## 18. Apêndices

### A. Gerar APP_KEY

```bash
docker exec -it painelshark_CLIENTE php artisan key:generate --show
```

### B. Executar Migrations Manualmente

```bash
docker exec -it painelshark_CLIENTE php artisan migrate --force
```

### C. Limpar Cache

```bash
docker exec -it painelshark_CLIENTE php artisan optimize:clear
docker exec -it painelshark_CLIENTE php artisan optimize
```

### D. Verificar Logs

```bash
docker exec -it painelshark_CLIENTE tail -f /var/www/storage/logs/laravel.log
```

### E. Acessar Tinker (Debug)

```bash
docker exec -it painelshark_CLIENTE php artisan tinker
```

### F. Testar Conexão com Banco XUI

```bash
docker exec -it painelshark_CLIENTE php artisan tinker
>>> DB::connection('xui')->table('users')->count()
```

### G. Forçar Envio de Notificações

```bash
docker exec -it painelshark_CLIENTE php artisan notifications:send-expiry
```

### H. Forçar Rotação de Ghost

```bash
docker exec -it painelshark_CLIENTE php artisan ghost:rotate
```

### I. Criar Novo Cliente SaaS

```bash
# Na VPS
bash spawn_client.sh NOME_CLIENTE dominio.com IP_XUI BANCO_XUI USER_XUI SENHA_XUI
```

### J. Atualizar Todos os Containers

```bash
# Na VPS
bash update_containers.sh
```

### K. Estrutura de Permissões (Roles)

| `member_group_id` | Role | Pode ver | Pode fazer |
|-------------------|------|----------|-----------|
| `1` | Admin | Tudo | Tudo (configurações, manutenção, servidores) |
| `2` | Revendedor | Seus clientes + sub-revendas | Criar/renovar clientes, gerenciar sub-revendas |

### L. Formato de Datas no XUI

O XUI usa **Unix timestamps** (inteiros) para datas:
- `exp_date` — Expiração da linha
- `created_at` — Criação da linha
- `date_registered` — Registro do usuário
- `last_login` — Último login
- `date` (credit logs) — Data da transação

Para converter: `date('d/m/Y H:i', $timestamp)`

### M. Bouquet Blacklist

Configurada em `config/xui.php` → `bouquet_blacklist`. IDs de bouquets que **não devem** aparecer para revendedores na criação/edição de clientes. Útil para esconder bouquets internos ou de teste.

---

## 19. Checklist para Manutenção

- [ ] Antes de alterar models que usam conexão `xui`, verificar se a tabela existe no banco XUI do cliente.
- [ ] Novas tabelas no banco próprio **devem** ter migration.
- [ ] Nunca alterar diretamente o schema do banco XUI via migration Laravel.
- [ ] Testar com `APP_ENV=local` antes de fazer push para `main`.
- [ ] Após push, verificar se o GitHub Actions buildou com sucesso.
- [ ] Após atualizar containers, verificar logs de cada container.
- [ ] Manter o `.env.example` atualizado com novas variáveis.
- [ ] Ao adicionar nova rota admin, aplicar middleware `admin`.
- [ ] Ao adicionar nova funcionalidade visível na sidebar, atualizar `layouts/app.blade.php`.

---

> **Documento gerado automaticamente com base na análise completa do código-fonte do projeto Painelshark.**
