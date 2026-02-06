# 🚀 Como Atualizar o Painel XUI

Agora que o sistema está configurado, atualizar é **MUITO FÁCIL**. Não precisa refazer todo aquele processo.

O fluxo de trabalho é simples: **Você edita no seu PC, envia para o GitHub, e a VPS baixa as novidades.**

---

##  Passo 1: Fazer as alterações no seu PC

Edite os arquivos que precisar no VS Code / Windsurf.

## Passo 2: Enviar para o GitHub

No terminal do seu PC (PowerShell):

```powershell
# 1. Adicionar as mudanças
git add .

# 2. Salvar com uma mensagem
git commit -m "Descreva o que mudou aqui"

# 3. Enviar para a nuvem
git push origin main
```

## Passo 3: Atualizar a VPS (O Mágico!)

Eu criei um script automático na VPS que faz tudo sozinho.

1. Conecte na VPS:
   ```powershell
   ssh root@5.189.164.31
   ```
   (Senha: `c11560011`)

2. Execute o comando de atualização:
   ```bash
   cd /var/www/painel-xui
   ./update.sh
   ```

**PRONTO!** 🎉
O script vai automaticamente:
- Baixar o código novo do GitHub
- Sincronizar os arquivos corretamente (usando rsync)
- Instalar novas dependências (se houver)
- Atualizar o banco de dados (se houver)
- Limpar e recriar os caches
- Ajustar permissões

---

## 🛠️ O que tem dentro do `update.sh`?

Para sua curiosidade, este é o script que está rodando na VPS:

```bash
#!/bin/bash
cd /var/www/painel-xui

echo '🚀 Baixando atualizações...'
git config --global --add safe.directory /var/www/painel-xui
git pull origin main

echo '🔄 Sincronizando arquivos...'
# Copia TUDO da pasta 'app' do repositório para a raiz (incluindo código PHP, Views, etc)
rsync -av app/ ./

echo '📦 Instalando dependências...'
composer install --optimize-autoloader --no-dev

echo '💾 Atualizando banco de dados...'
php artisan migrate --force || true

echo '🧹 Limpando caches...'
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo '⚡ Otimizando...'
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo '🔒 Ajustando permissões...'
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo '✅ Atualização concluída com sucesso!'
```
