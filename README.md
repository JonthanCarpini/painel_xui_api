# 📘 Manual Técnico do Sistema - Painelshark (Painel Office)

![Build Status](https://github.com/JonthanCarpini/Painelshark/actions/workflows/docker-publish.yml/badge.svg)

> **Versão do Documento:** 1.0  
> **Data:** 06/02/2026  
> **Status:** Em Desenvolvimento Ativo

Este documento serve como guia definitivo para a arquitetura, funcionalidades, peculiaridades e manutenção do sistema Painelshark. Ele deve ser consultado antes de qualquer implementação de nova feature ou correção crítica.

---

## 1. 🏗️ Visão Geral e Arquitetura

O **Painelshark** é um gerenciador de revendas e clientes IPTV desenvolvido para funcionar como uma interface robusta e amigável sobre o sistema **XUI.ONE**.

Diferente de painéis simples que apenas consomem API, o Painelshark utiliza uma **Arquitetura Híbrida de Dados**:
1.  **Leitura de Alta Performance:** Conecta-se DIRETAMENTE ao banco de dados MySQL do XUI para listar clientes, calcular estatísticas e gerar relatórios. Isso evita o gargalo de requisições HTTP para a API em listagens grandes.
2.  **Escrita Segura:** Utiliza a **API do XUI** para operações de modificação (Criar, Editar, Deletar, Renovar). Isso garante que o XUI processe os eventos internos (reload de configs, derrubar conexões, etc).

### 🛠️ Stack Tecnológica
*   **Framework Backend:** Laravel 12 (PHP 8.2+)
*   **Frontend:** Blade Templates + Tailwind CSS + Bootstrap Icons
*   **Banco de Dados:** MySQL / MariaDB (Multiconexão)
*   **Servidor Web:** Nginx + PHP-FPM
*   **SO:** Linux (Ubuntu/Debian) na VPS

---

## 2. 🗄️ Banco de Dados e Conexões

O sistema opera com **duas conexões de banco de dados simultâneas**. É CRÍTICO entender a diferença para não corromper o servidor XUI.

### A. Conexão Local (`mysql` / `painel_plus`)
Banco de dados exclusivo do Painelshark. Armazena dados que o XUI não suporta ou que precisamos estender.
*   **Tabelas Principais:**
    *   `client_details`: Armazena o telefone local e notas extras dos clientes (vínculo via `xui_client_id`).
    *   `notices`: Avisos e "Sticky Notes" do dashboard.
    *   `test_channels`: Lista curada para o Teste de Canais (com categorização por tipo).
    *   `tickets`, `ticket_messages`: Sistema de suporte.
    *   `user_logs`: Logs detalhados de auditoria do painel.
*   **Migrações:** Todas as migrações em `database/migrations` devem afetar APENAS esta conexão.

### B. Conexão Remota (`xui` / `painel_xui`)
Conexão direta ao banco do XUI.ONE.
*   **Permissão:** **SOMENTE LEITURA (Recomendado)** ou Escrita via Models controlados.
*   **Models:** `Line`, `XuiUser`, `Package`.
*   **Regra de Ouro:** NUNCA crie migrações para alterar tabelas deste banco. A estrutura é gerenciada pelo software XUI.

---

## 3. 🧩 Funcionalidades e Peculiaridades

### 3.1. Gestão de Clientes (Híbrido)
*   **Listagem:** Feita via Eloquent (`Line::on('xui')`). Permite filtros avançados de vencimento, status e pesquisa rápida.
*   **Telefone:** O XUI muitas vezes perde ou sobrescreve o campo de contato. Por isso, a "Fonte da Verdade" para o telefone do cliente é a tabela local `client_details`.
    *   Lógica de Exportação: `client_details.phone` > `lines.admin_notes` > `lines.contact`.
*   **Vencimentos:** O Dashboard filtra "Vencendo em 7 dias" excluindo automaticamente contas de Teste (`is_trial = 0`).

### 3.2. Teste de Canais (Player Web)
*   Utiliza a biblioteca **HLS.js** para reproduzir streams diretamente no navegador.
*   **Peculiaridade:** As categorias (Canais, Filmes, Séries) não vêm do XUI, mas sim da tabela local `test_channels`, onde definimos manualmente o `group_title` e o novo campo `type`.
*   **Organização:** Abas superiores filtram por `type` (`live`, `movie`, `series`).

### 3.3. Avisos (Sticky Notes)
*   Sistema visual estilo "Post-it".
*   **Cores:** O admin pode definir cores Hexadecimais ou Classes Tailwind específicas. O sistema tem um fallback automático baseado no tipo (Info, Warning, Danger, Success).

### 3.4. Sidebar e Navegação
*   Estrutura categorizada (Visão Geral, Clientes, Ferramentas, etc).
*   **Permissões:** Itens como "Revendas" e "Logs de Revendas" aparecem apenas para Admins ou Revendedores que possuem sub-revendas (`sub_resellers_count > 0` ou `member_group_id == 2`).

### 3.5. Logs Financeiros e de Ações
*   **Legado:** `credit_logs` (tabela local) usada para movimentações de saldo.
*   **Novo:** `user_logs` (tabela local) registra ações detalhadas (quem criou quem, renovações com duração exata, etc).
*   O sistema mantém compatibilidade gravando em ambos quando necessário.

---

## 4. ⚠️ Pontos Críticos e de Atenção

1.  **Criação de Usuários/Linhas:**
    *   **SEMPRE** use o `XuiApiService` (caminho: `app/Services/XuiApiService.php`).
    *   **NUNCA** faça `Line::create([...])` diretamente no banco XUI sem passar pela API, pois o servidor de streaming não saberá que o usuário existe até ser reiniciado.

2.  **Deploy e Cache:**
    *   O Laravel em produção cacheia agressivamente as configurações e rotas.
    *   Após qualquer alteração no código (`.env`, `routes`, `config`), é obrigatório rodar:
        ```bash
        php artisan optimize:clear
        php artisan view:clear
        ```
    *   O script `upload-zip.ps1` já faz isso automaticamente.

3.  **Encoding de Caracteres:**
    *   O banco XUI às vezes usa collations diferentes. Ao exibir nomes de clientes ou buquês com acentos, use funções de escape do Blade (`{{ }}`) que já tratam UTF-8, mas fique atento a "dupla codificação" em exports CSV.

4.  **Hierarquia de Revenda:**
    *   O sistema suporta Níveis. Um Revendedor pode ter Sub-revendedores.
    *   Ao filtrar clientes para um revendedor, use o método `$user->getAllSubResellerIds()` para garantir que ele veja os clientes dos seus sub-revendedores também.

---

## 5. 🚀 Processo de Deploy

O deploy é automatizado via script PowerShell (`app/upload-zip.ps1`) para ambientes Windows.

**O que o script faz:**
1.  Lista arquivos modificados manualmente no array `$files`.
2.  Cria um arquivo `.zip`.
3.  Envia via SCP para `/tmp/deploy.zip`.
4.  Acessa via SSH, descompacta em `/var/www/painel-xui`.
5.  Executa comandos de limpeza (`optimize:clear`) e migrações (`migrate --force`).
6.  Recarrega o serviço PHP-FPM (`systemctl reload php*-fpm`).

**Como adicionar arquivos ao deploy:**
Edite o arquivo `app/upload-zip.ps1` e adicione o caminho relativo na lista `$files`.

---

## 6. 🔮 Guia para Novas Implementações

Ao criar uma nova funcionalidade:

1.  **Precisa de dados novos?**
    *   Se for dado do XUI (ex: Servidores), crie um Model apontando para `protected $connection = 'xui'`.
    *   Se for dado do Painel (ex: Chat, Gamificação), crie a migration no banco padrão (`painel_plus`).

2.  **Precisa modificar dados do XUI?**
    *   Verifique se existe método no `XuiApiService`. Se não, adicione o endpoint correspondente consultando a documentação da API do XUI.

3.  **Frontend:**
    *   Use componentes Blade existentes em `resources/views/layouts` para manter a consistência visual.
    *   Use Tailwind CSS para estilização.

---

## 7. 📂 Estrutura de Arquivos e Pastas

Abaixo está a estrutura dos diretórios principais do projeto, focando onde a lógica de negócio reside.

```
painel_xui/
├── app/                                # Código Fonte Principal (Laravel)
│   ├── app/
│   │   ├── Http/Controllers/           # Controladores (Lógica de Requisição)
│   │   │   ├── Admin/                  # Controladores exclusivos de Admin
│   │   │   ├── ChannelTestController.php # Lógica do Teste de Canais
│   │   │   ├── ClientController.php    # Gestão de Clientes (CRUD Híbrido)
│   │   │   ├── DashboardController.php # Lógica do Dashboard e Gráficos
│   │   │   └── ...
│   │   ├── Models/                     # Modelos Eloquent
│   │   │   ├── Line.php                # Modelo da Linha (Conexão: xui)
│   │   │   ├── XuiUser.php             # Modelo de Usuário XUI (Conexão: xui)
│   │   │   ├── ClientDetail.php        # Detalhes Locais (Conexão: painel_plus)
│   │   │   ├── Notice.php              # Avisos (Conexão: painel_plus)
│   │   │   └── ...
│   │   └── Services/                   # Lógica de Negócio e Integrações
│   │       ├── XuiApiService.php       # Wrapper para todas as chamadas API do XUI
│   │       └── LineService.php         # Regras para criação/renovação de linhas
│   ├── config/                         # Arquivos de Configuração
│   │   ├── database.php                # Configuração das conexões (xui vs mysql)
│   │   └── ...
│   ├── database/
│   │   └── migrations/                 # Migrações para o banco local (painel_plus)
│   ├── resources/
│   │   └── views/                      # Templates Blade (Frontend)
│   │       ├── channel-test/           # Views do Teste de Canais
│   │       ├── clients/                # Views de Gestão de Clientes
│   │       ├── dashboard/              # View do Dashboard
│   │       ├── layouts/                # Layout base (Sidebar, Header)
│   │       ├── notices/                # View dos Avisos (Sticky Notes)
│   │       └── ...
│   ├── routes/
│   │   └── web.php                     # Definição de Rotas Web
│   └── upload-zip.ps1                  # Script de Deploy Automatizado (Legado/VPS Única)
├── docker/                             # Configurações de Containerização
│   ├── nginx/                          # Config do Nginx para Containers
│   └── entrypoint.sh                   # Script de inicialização do Container
├── documentos/                         # Documentação do Projeto
│   ├── MANUAL_TECNICO_DO_SISTEMA.md    # Este arquivo
│   └── ...
├── docker-compose.saas.yml             # Orchestrador da Infra (Traefik + MySQL)
├── spawn_client.sh                     # Script de Provisionamento de Novos Clientes
├── Dockerfile                          # Definição da Imagem do Painel
└── ...
```

---

## 8. 🐳 Arquitetura SaaS (Docker & Infraestrutura)

O Painelshark foi arquitetado para rodar em modo **SaaS Single-Tenant**. Isso significa que cada cliente possui seu próprio container isolado, mas compartilha a infraestrutura de rede e banco de dados centralizado.

### 8.1. Componentes da Infraestrutura
1.  **Traefik (Gateway):** Atua como Proxy Reverso. Ele recebe requisições `painel.cliente.com`, gera SSL automático (Let's Encrypt) e encaminha para o container correto.
2.  **MySQL Central:** Um único container MySQL armazena os bancos de todos os clientes. Cada cliente tem um schema isolado (`painel_clienteA`, `painel_clienteB`).
3.  **Containers de Aplicação:** Cada cliente roda uma instância do Painelshark (PHP-FPM + Nginx) baseada na imagem Docker oficial do projeto.

### 8.2. Fluxo de Provisionamento (Venda -> Ativação)
O processo é automatizado pelo script `spawn_client.sh` presente na raiz.

1.  **Venda Aprovada:** O sistema gerenciador chama o script de provisionamento.
2.  **Criação de Banco:** O script cria `painel_{cliente}` e usuário `user_{cliente}` no MySQL Central.
3.  **Spawn do Container:** Um novo container docker é iniciado com variáveis de ambiente injetadas:
    *   `DB_HOST`: Aponta para `mysql_central`.
    *   `APP_URL`: Domínio do cliente.
    *   `XUI_HOST`: URL do painel XUI do cliente (externo).
4.  **Migração:** O container roda `php artisan migrate --force` ao iniciar para criar as tabelas locais.

### 8.3. White Label e Customização
Como a imagem Docker é imutável (igual para todos), a personalização (Logo, Nome, Cores) **NÃO** fica no código ou `.env`.
*   **Armazenamento:** Configurações visuais ficam na tabela `app_settings` dentro do banco `painel_plus` do cliente.
*   **Injeção:** O Laravel carrega essas configs via `AppServiceProvider` e injeta nas Views.

### 8.4. Processo de Atualização em Massa
Para atualizar todos os 100+ clientes simultaneamente:
1.  Commitar alterações no Git (`main`).
2.  Gerar nova build Docker: `docker build -t carpini/painelshark:latest .`
3.  Subir para o Hub: `docker push carpini/painelshark:latest`.
4.  No servidor, rodar script de update que faz `docker pull` e reinicia os containers com a nova imagem.

---

**Painelshark Team**  
*Documento vivo - Atualize sempre que houver mudanças arquiteturais.*
