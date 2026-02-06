# 📚 Comandos Principais do Laravel

## 🚀 Servidor de Desenvolvimento

```bash
# Iniciar servidor (porta 8000 padrão)
php artisan serve

# Iniciar servidor em porta específica
php artisan serve --port=8080

# Iniciar servidor em host específico
php artisan serve --host=0.0.0.0 --port=8000
```

## 🗄️ Banco de Dados & Migrations

```bash
# Executar migrations (criar tabelas)
php artisan migrate

# Reverter última migration
php artisan migrate:rollback

# Reverter todas as migrations
php artisan migrate:reset

# Reverter e executar novamente todas as migrations
php artisan migrate:refresh

# Reverter, executar migrations e seeders
php artisan migrate:refresh --seed

# Criar nova migration
php artisan make:migration create_users_table
php artisan make:migration add_column_to_users_table

# Ver status das migrations
php artisan migrate:status

# Executar seeders (popular banco com dados)
php artisan db:seed
php artisan db:seed --class=UserSeeder
```

## 🏗️ Criação de Arquivos (Make)

```bash
# Controller
php artisan make:controller UserController
php artisan make:controller UserController --resource  # Com métodos CRUD

# Model
php artisan make:model User
php artisan make:model User -m  # Com migration
php artisan make:model User -mf  # Com migration e factory
php artisan make:model User -a  # Tudo (migration, factory, seeder, controller)

# Migration
php artisan make:migration create_users_table

# Seeder
php artisan make:seeder UserSeeder

# Middleware
php artisan make:middleware CheckAge

# Request (validação)
php artisan make:request StoreUserRequest

# Resource (API)
php artisan make:resource UserResource

# Policy (autorização)
php artisan make:policy UserPolicy

# Command (comando customizado)
php artisan make:command SendEmails

# Event
php artisan make:event UserRegistered

# Listener
php artisan make:listener SendWelcomeEmail

# Job (fila)
php artisan make:job ProcessPodcast

# Mail
php artisan make:mail WelcomeMail

# Notification
php artisan make:notification InvoicePaid
```

## 🧹 Cache & Otimização

```bash
# Limpar cache da aplicação
php artisan cache:clear

# Limpar cache de configuração
php artisan config:clear

# Limpar cache de rotas
php artisan route:clear

# Limpar cache de views compiladas
php artisan view:clear

# Limpar todos os caches
php artisan optimize:clear

# Criar cache de configuração (produção)
php artisan config:cache

# Criar cache de rotas (produção)
php artisan route:cache

# Criar cache de views
php artisan view:cache

# Otimizar aplicação para produção
php artisan optimize
```

## 🔑 Chaves & Segurança

```bash
# Gerar chave da aplicação (APP_KEY)
php artisan key:generate

# Criar link simbólico para storage público
php artisan storage:link
```

## 📋 Listagem & Informações

```bash
# Listar todas as rotas
php artisan route:list

# Listar rotas filtradas
php artisan route:list --path=api
php artisan route:list --name=user

# Listar todos os comandos disponíveis
php artisan list

# Ver informações sobre comando específico
php artisan help migrate
```

## 🔧 Manutenção

```bash
# Colocar aplicação em modo manutenção
php artisan down

# Colocar em manutenção com mensagem customizada
php artisan down --message="Estamos em manutenção" --retry=60

# Tirar do modo manutenção
php artisan up
```

## 🧪 Testes

```bash
# Executar todos os testes
php artisan test

# Executar testes com coverage
php artisan test --coverage

# Executar teste específico
php artisan test --filter=UserTest
```

## 📦 Composer (Gerenciador de Pacotes)

```bash
# Instalar dependências
composer install

# Atualizar dependências
composer update

# Adicionar novo pacote
composer require vendor/package

# Adicionar pacote de desenvolvimento
composer require --dev vendor/package

# Remover pacote
composer remove vendor/package

# Atualizar autoload
composer dump-autoload

# Verificar pacotes desatualizados
composer outdated
```

## 🔄 Filas (Queues)

```bash
# Processar filas
php artisan queue:work

# Processar fila específica
php artisan queue:work --queue=emails

# Processar apenas 1 job e parar
php artisan queue:work --once

# Ver jobs falhados
php artisan queue:failed

# Reprocessar job falhado
php artisan queue:retry 1

# Reprocessar todos os jobs falhados
php artisan queue:retry all

# Limpar jobs falhados
php artisan queue:flush
```

## 📅 Agendamento (Schedule)

```bash
# Executar tarefas agendadas manualmente
php artisan schedule:run

# Ver lista de tarefas agendadas
php artisan schedule:list
```

## 🐛 Debug & Desenvolvimento

```bash
# Modo interativo (Tinker)
php artisan tinker

# Exemplo de uso do Tinker:
>>> User::all()
>>> User::find(1)
>>> User::create(['name' => 'John', 'email' => 'john@example.com'])

# Ver informações da aplicação
php artisan about

# Ver informações do ambiente
php artisan env
```

## 🎨 Frontend (se usar Laravel Mix/Vite)

```bash
# Compilar assets (desenvolvimento)
npm run dev

# Compilar assets (produção)
npm run build

# Watch mode (recompila ao salvar)
npm run watch
```

## 📝 Comandos Customizados do Projeto

```bash
# Se você criar comandos customizados, eles aparecerão aqui
# Exemplo:
php artisan app:send-daily-report
php artisan app:cleanup-old-logs
```

## 🔍 Comandos Úteis Específicos

### Verificar Conexão com Banco de Dados
```bash
php artisan db:show
php artisan db:table users
```

### Limpar Sessões Expiradas
```bash
php artisan session:gc
```

### Publicar Assets de Pacotes
```bash
php artisan vendor:publish
php artisan vendor:publish --provider="Vendor\Package\ServiceProvider"
```

## 🎯 Fluxo de Trabalho Comum

### Desenvolvimento Diário
```bash
# 1. Iniciar servidor
php artisan serve

# 2. Se fez mudanças no .env
php artisan config:clear

# 3. Se criou novas rotas
php artisan route:clear

# 4. Se mudou views
php artisan view:clear
```

### Após Atualizar Código (git pull)
```bash
composer install
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Deploy para Produção
```bash
# 1. Atualizar dependências
composer install --optimize-autoloader --no-dev

# 2. Executar migrations
php artisan migrate --force

# 3. Criar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Otimizar
php artisan optimize

# 5. Compilar assets
npm run build
```

### Criar Nova Feature
```bash
# 1. Criar migration
php artisan make:migration create_posts_table

# 2. Criar model com factory e seeder
php artisan make:model Post -fs

# 3. Criar controller
php artisan make:controller PostController --resource

# 4. Criar request de validação
php artisan make:request StorePostRequest

# 5. Executar migration
php artisan migrate
```

## 🆘 Resolução de Problemas

```bash
# Erro de permissão
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Limpar tudo
php artisan optimize:clear
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Recriar banco de dados do zero
php artisan migrate:fresh --seed
```

## 📚 Documentação Oficial

- [Laravel Docs](https://laravel.com/docs)
- [Laravel API](https://laravel.com/api/11.x/)
- [Laracasts](https://laracasts.com)

## 💡 Dicas

1. **Use `--help`** para ver opções de qualquer comando:
   ```bash
   php artisan migrate --help
   ```

2. **Autocomplete** no terminal (bash/zsh):
   ```bash
   php artisan completion bash > /etc/bash_completion.d/artisan
   ```

3. **Alias úteis** (adicione no `.bashrc` ou `.zshrc`):
   ```bash
   alias pa="php artisan"
   alias pas="php artisan serve"
   alias pam="php artisan migrate"
   alias pac="php artisan cache:clear"
   ```

4. **Modo de desenvolvimento** sempre use:
   ```bash
   APP_DEBUG=true
   APP_ENV=local
   ```

5. **Modo de produção** sempre use:
   ```bash
   APP_DEBUG=false
   APP_ENV=production
   ```
