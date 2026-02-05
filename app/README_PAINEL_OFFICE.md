# 🎯 Painel Office - Sistema de Revenda IPTV

Sistema completo de gestão de revenda IPTV desenvolvido em Laravel, integrado com a API XUI.ONE.

## 📋 Características

### ✨ Funcionalidades Principais

- **Dashboard Intuitivo**: Visão geral com estatísticas em tempo real
- **Gestão de Clientes**: Criação, edição, renovação e exclusão de clientes
- **Testes Gratuitos**: Geração rápida de contas de teste
- **Monitoramento Ao Vivo**: Visualização de conexões ativas em tempo real
- **Gestão de Revendedores**: (Admin) Criação e gerenciamento de revendedores
- **Sistema de Créditos**: Controle financeiro integrado
- **Links M3U**: Geração automática de links de acesso

### 🎨 Design

- Interface moderna com tema escuro
- Sidebar elegante e responsiva
- Cards informativos com gradientes
- Totalmente responsivo (Mobile-friendly)
- Inspirado em dashboards SaaS modernos

### 🔒 Segurança

- Autenticação customizada com API XUI
- Middleware de proteção de rotas
- Separação de permissões (Admin/Revendedor)
- Validação de dados em todas as requisições

## 🚀 Instalação

### Requisitos

- PHP 8.2 ou superior
- Composer
- Servidor Web (Apache/Nginx)
- Acesso à API XUI.ONE

### Passo a Passo

1. **Clone o repositório**
```bash
cd c:\Users\admin\Documents\Projetos\painel_xui\app
```

2. **Configure o ambiente**
```bash
cp .env.example .env
```

3. **Edite o arquivo .env**
```env
APP_NAME="Painel Office IPTV"
APP_URL=http://localhost:8000

# Configurações XUI
XUI_BASE_URL=http://192.168.100.209/kIzFSjQu/
XUI_API_KEY=DFE74ECCBA19D32DCD758C4D3D5AF0F6
XUI_TIMEOUT=30

# Streaming
XUI_STREAM_PROTOCOL=http
XUI_STREAM_SERVER=192.168.100.209
XUI_STREAM_PORT=80
```

4. **Gere a chave da aplicação**
```bash
php artisan key:generate
```

5. **Execute as migrações** (se necessário)
```bash
php artisan migrate
```

6. **Inicie o servidor**
```bash
php artisan serve
```

7. **Acesse o sistema**
```
http://localhost:8000
```

## 📖 Uso

### Login

Use as credenciais de um usuário existente no XUI:
- **Usuário**: Seu username do XUI
- **Senha**: Sua senha do XUI

### Níveis de Acesso

#### 👤 Revendedor (Group ID: 2)
- Criar e gerenciar clientes
- Gerar testes gratuitos
- Monitorar conexões
- Visualizar dashboard

#### 👑 Administrador (Group ID: 1)
- Todas as permissões de revendedor
- Criar e gerenciar revendedores
- Recarregar créditos
- Acesso total ao sistema

## 🗂️ Estrutura do Projeto

```
app/
├── app/
│   ├── Auth/
│   │   ├── XuiUser.php              # Modelo de usuário customizado
│   │   └── XuiUserProvider.php      # Provider de autenticação
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php   # Autenticação
│   │   │   ├── DashboardController.php
│   │   │   ├── ClientController.php # Gestão de clientes
│   │   │   ├── ResellerController.php # Gestão de revendas
│   │   │   └── MonitorController.php # Monitoramento
│   │   └── Middleware/
│   │       └── AdminMiddleware.php  # Proteção admin
│   └── Services/
│       └── XuiApiService.php        # Serviço de API
├── config/
│   ├── auth.php                     # Configuração de auth
│   └── xui.php                      # Configuração XUI
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php        # Layout principal
│       ├── auth/
│       │   └── login.blade.php      # Tela de login
│       ├── dashboard/
│       │   └── index.blade.php      # Dashboard
│       ├── clients/                 # Views de clientes
│       ├── resellers/               # Views de revendedores
│       └── monitor/                 # Views de monitoramento
└── routes/
    └── web.php                      # Rotas da aplicação
```

## 🔧 Configuração Avançada

### Blacklist de Buquês

Edite `config/xui.php` para adicionar IDs de buquês que não devem aparecer:

```php
'bouquet_blacklist' => [34, 35, 10],
```

### Timeout da API

Ajuste o timeout das requisições em `config/xui.php`:

```php
'timeout' => env('XUI_TIMEOUT', 30),
```

### Cache

O sistema usa cache para pacotes e buquês (1 hora). Para limpar:

```bash
php artisan cache:clear
```

## 📊 Endpoints da API XUI Utilizados

- `get_users` - Autenticação e listagem de usuários
- `get_user` - Detalhes de usuário específico
- `create_user` - Criar revendedor
- `edit_user` - Editar usuário/recarregar créditos
- `get_lines` - Listar clientes
- `get_line` - Detalhes de cliente
- `create_line` - Criar cliente/teste
- `edit_line` - Editar/renovar cliente
- `delete_line` - Excluir cliente
- `get_packages` - Listar pacotes
- `get_bouquets` - Listar buquês (canais)
- `live_connections` - Conexões ativas
- `kill_connection` - Derrubar conexão

## 🎨 Customização

### Cores do Tema

Edite as variáveis CSS em `resources/views/layouts/app.blade.php`:

```css
:root {
    --bg-dark: #1a1d29;
    --bg-darker: #13151f;
    --bg-card: #252836;
    --accent-orange: #ff6b35;
    --accent-gold: #ffa500;
}
```

### Logo

Substitua o ícone na sidebar editando:

```html
<div class="logo-icon">
    <i class="bi bi-tv"></i> <!-- Altere aqui -->
</div>
```

## 🐛 Solução de Problemas

### Erro de Autenticação

- Verifique se a `XUI_BASE_URL` e `XUI_API_KEY` estão corretas
- Confirme que o usuário existe no XUI e está ativo

### Erro 500 ao Criar Cliente

- Verifique o formato da data (deve ser Y-m-d H:i)
- Confirme que os bouquet_ids são enviados como JSON string

### Conexões não aparecem no Monitor

- Verifique se o endpoint `live_connections` está disponível
- Confirme que há clientes realmente conectados

## 📝 Notas Importantes

### Formato de Data

A API XUI exige datas no formato `YYYY-MM-DD HH:MM` (string), não timestamps Unix.

### Bouquet IDs

Devem ser enviados como string JSON: `"[1,2,3]"`, não como array PHP.

### Member ID

Sempre use o ID do usuário logado (`member_id`) ao criar clientes para garantir o débito correto de créditos.

## 🔄 Atualizações Futuras

- [ ] Relatórios financeiros
- [ ] Histórico de transações
- [ ] Notificações de vencimento
- [ ] API REST para integração
- [ ] Aplicativo mobile

## 📄 Licença

Este projeto foi desenvolvido para uso interno. Todos os direitos reservados.

## 🤝 Suporte

Para suporte técnico, consulte a documentação da API XUI.ONE ou entre em contato com o desenvolvedor.

---

**Desenvolvido com ❤️ usando Laravel e Bootstrap**
