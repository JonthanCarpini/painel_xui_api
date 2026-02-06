# Configuração da Conexão com Banco de Dados XUI

## ⚠️ AÇÃO NECESSÁRIA

A migração para conexão direta ao banco de dados foi **implementada com sucesso**, mas precisa das **credenciais corretas** do banco MySQL do XUI One.

## 📋 Status da Implementação

✅ **Concluído:**
- Conexão MySQL configurada em `config/database.php`
- 7 Models Eloquent criados (XuiUser, Line, CreditLog, LineLive, Package, Bouquet, LoginLog)
- Sistema de autenticação reescrito (XuiDatabaseUserProvider)
- Todos os controladores migrados para queries diretas:
  - ✅ AuthController (com registro de login_logs)
  - ✅ DashboardController (Admin e Revenda com hierarquia)
  - ✅ ClientController (CRUD completo com transações)
  - ✅ ResellerController (CRUD com transferência de saldo)
  - ✅ CreditLogController (auditoria financeira)
  - ✅ MonitorController (conexões ao vivo)

## 🔧 Configuração Necessária

### 1. Editar o arquivo `.env`

Localize as linhas no arquivo `.env` e ajuste com as credenciais corretas:

```env
# XUI Database Connection
XUI_DB_HOST=192.168.100.210
XUI_DB_PORT=3306
XUI_DB_DATABASE=xui
XUI_DB_USERNAME=root
XUI_DB_PASSWORD=SUA_SENHA_AQUI
```

### 2. Informações que você precisa obter

**Do servidor XUI One (192.168.100.210):**

```bash
# Conecte via SSH e execute:
mysql -u root -p

# Dentro do MySQL, verifique:
SHOW DATABASES;
SELECT user, host FROM mysql.user;
```

**Credenciais comuns do XUI One:**
- Usuário: `root` ou `user_iptvpanel`
- Senha: Geralmente definida durante instalação do XUI
- Banco: `xui` (padrão)
- Porta: `3306` (padrão MySQL)

### 3. Verificar acesso remoto ao MySQL

O MySQL precisa aceitar conexões remotas. No servidor XUI, edite:

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Procure e comente a linha:
```
# bind-address = 127.0.0.1
```

Ou altere para:
```
bind-address = 0.0.0.0
```

Depois reinicie o MySQL:
```bash
sudo systemctl restart mysql
```

### 4. Criar usuário com permissões remotas (se necessário)

```sql
CREATE USER 'painel_office'@'%' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON xui.* TO 'painel_office'@'%';
FLUSH PRIVILEGES;
```

## 🧪 Testar a Conexão

Após configurar as credenciais, execute:

```bash
cd c:\Users\admin\Documents\Projetos\painel_xui\app
php test-db-connection.php
```

Se a conexão funcionar, você verá:
```
✓ Conexão estabelecida com sucesso!
✓ Total de usuários: X
✓ Total de linhas: X
✓ Todos os testes passaram!
```

## 🚀 Após Configurar

1. Limpe os caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

2. Acesse o painel: `http://localhost` (ou seu domínio)

3. Faça login com um usuário existente do banco XUI

## 📊 Estrutura Implementada

### Models Eloquent
- `XuiUser` → Tabela `users` (Admin/Revenda)
- `Line` → Tabela `lines` (Clientes IPTV)
- `CreditLog` → Tabela `users_credits_logs` (Auditoria)
- `LineLive` → Tabela `lines_live` (Conexões ativas)
- `Package` → Tabela `users_packages` (Planos)
- `Bouquet` → Tabela `bouquets` (Canais)
- `LoginLog` → Tabela `login_logs` (Histórico de acesso)

### Funcionalidades Implementadas

**Autenticação:**
- Login direto no banco com suporte a MD5/Crypt
- Registro automático em `login_logs`
- Diferenciação Admin (group_id=1) e Revenda (group_id=2)

**Dashboard Admin:**
- Total de revendas
- Clientes ativos/expirados
- Conexões online
- Estatísticas globais

**Dashboard Revenda:**
- Clientes da árvore hierárquica (incluindo sub-revendas)
- Vencendo hoje
- Minhas revendas
- Online agora

**Gestão de Clientes:**
- Criação com débito automático de créditos
- Renovação com transação segura
- Testes grátis (sem débito)
- Geração de M3U/HLS
- Transações com rollback em caso de erro

**Gestão de Revendas:**
- Criação com transferência de saldo
- Recarga/Remoção de créditos
- Logs automáticos de todas transações
- Hierarquia (owner_id)

**Logs de Crédito:**
- Auditoria completa
- Filtro por usuário (Admin vê tudo, Revenda vê só seus)
- Resumo financeiro
- Join com usernames

## ⚠️ Importante

- **Backup:** O sistema agora opera diretamente no banco XUI. Faça backups regulares!
- **Transações:** Todas operações financeiras usam DB Transactions para garantir integridade
- **Performance:** Queries otimizadas com índices e eager loading
- **Segurança:** Senhas em MD5 (padrão XUI), validações em todas operações

## 🔄 Código Legado Removido

- ❌ `XuiApiService` (não é mais usado)
- ❌ `XuiUserProvider` (API-based)
- ❌ Todas chamadas HTTP para API XUI

## 📞 Próximos Passos

1. Configure as credenciais do banco no `.env`
2. Teste a conexão com `php test-db-connection.php`
3. Acesse o painel e faça login
4. Verifique se todas funcionalidades estão operando corretamente
5. Remova o arquivo `test-db-connection.php` após confirmar que tudo funciona
