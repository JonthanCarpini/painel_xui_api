# 🚀 COMANDOS PARA DEPLOY - COPIE E COLE NA ORDEM

## CREDENCIAIS VPS
```
IP: 5.189.164.31
Usuário: root
Senha: c11560011
```

---

## PASSO 1: CONECTAR NA VPS

### Opção A: Via Terminal do Windsurf
```bash
ssh root@5.189.164.31
# Quando pedir senha: c11560011
# Quando pedir confirmação: yes
```

### Opção B: Via Remote Explorer do Windsurf
1. Clique em "Remote Explorer" (ícone na barra lateral)
2. Clique em "SSH (Windsurf)"
3. Clique no "+" para adicionar conexão
4. Digite: `ssh root@5.189.164.31`
5. Digite a senha quando solicitado: `c11560011`

---

## PASSO 2: ATUALIZAR SISTEMA (Execute na VPS)

```bash
# Atualizar sistema
apt update && apt upgrade -y
```

---

## PASSO 3: INSTALAR DEPENDÊNCIAS (Execute na VPS)

```bash
# Instalar Nginx, PHP 8.2, MySQL e outras dependências
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y nginx mysql-server \
php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl \
php8.2-xml php8.2-bcmath php8.2-intl \
git curl supervisor ufw fail2ban unzip
```

---

## PASSO 4: INSTALAR COMPOSER (Execute na VPS)

```bash
# Baixar e instalar Composer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verificar instalação
composer --version
```

---

## PASSO 5: CONFIGURAR FIREWALL (Execute na VPS)

```bash
# Configurar UFW
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
ufw status
```

---

## PASSO 6: CONFIGURAR MYSQL (Execute na VPS)

```bash
# Criar banco de dados e usuário
mysql -e "CREATE DATABASE painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'painel_user'@'localhost' IDENTIFIED BY 'Painel@2026#Strong';"
mysql -e "GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "✅ Banco de dados criado!"
echo "Database: painel_xui"
echo "User: painel_user"
echo "Password: Painel@2026#Strong"
```

---

## PASSO 7: CRIAR USUÁRIO DEPLOY (Execute na VPS)

```bash
# Criar usuário deploy (mais seguro que usar root)
adduser deploy --disabled-password --gecos ""
usermod -aG sudo deploy
echo "deploy ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

echo "✅ Usuário deploy criado!"
```

---

## PASSO 8: PREPARAR DIRETÓRIO DA APLICAÇÃO (Execute na VPS)

```bash
# Criar diretório
mkdir -p /var/www/painel-xui
chown -R deploy:deploy /var/www/painel-xui

echo "✅ Diretório criado: /var/www/painel-xui"
```

---

## PASSO 9: FAZER UPLOAD DO CÓDIGO

### Opção A: Via SCP (Execute no seu PC Windows - PowerShell)
```powershell
# Navegar até a pasta do projeto
cd C:\Users\admin\Documents\Projetos\painel_xui

# Fazer upload (vai pedir senha: c11560011)
scp -r app/* root@5.189.164.31:/var/www/painel-xui/
```

### Opção B: Via Git (Execute na VPS)
```bash
# Se você tiver o código no GitHub
cd /var/www/painel-xui
git clone https://github.com/SEU_USUARIO/painel-xui.git .
```

### Opção C: Via Remote Explorer do Windsurf (MAIS FÁCIL)
1. Conecte na VPS via SSH no Windsurf
2. Abra a pasta `/var/www/painel-xui`
3. Arraste os arquivos da pasta `app` local para a pasta remota

---

## PASSO 10: CONFIGURAR APLICAÇÃO (Execute na VPS)

```bash
cd /var/www/painel-xui

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Criar arquivo .env
cat > .env << 'EOF'
APP_NAME="Painel XUI"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://5.189.164.31

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=painel_xui
DB_USERNAME=painel_user
DB_PASSWORD=Painel@2026#Strong

# Conexão XUI (AJUSTE COM SEUS DADOS)
DB_XUI_HOST=IP_DO_SERVIDOR_XUI
DB_XUI_PORT=3306
DB_XUI_DATABASE=xtream_iptvpro
DB_XUI_USERNAME=usuario_xui
DB_XUI_PASSWORD=senha_xui

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
EOF

# Gerar chave da aplicação
php artisan key:generate

# Ajustar permissões
chown -R deploy:www-data /var/www/painel-xui
chmod -R 755 /var/www/painel-xui
chmod -R 775 /var/www/painel-xui/storage
chmod -R 775 /var/www/painel-xui/bootstrap/cache

echo "✅ Aplicação configurada!"
```

---

## PASSO 11: IMPORTAR BANCO DE DADOS (Execute na VPS)

```bash
# Fazer upload do backup SQL (execute no Windows PowerShell)
scp C:\Users\admin\Documents\Projetos\painel_xui\documentos\backup_2026-02-04_00_38_02.sql root@5.189.164.31:/tmp/

# Importar (execute na VPS)
mysql -u painel_user -pPainel@2026#Strong painel_xui < /tmp/backup_2026-02-04_00_38_02.sql

# Executar migrations (se houver)
cd /var/www/painel-xui
php artisan migrate --force

echo "✅ Banco de dados importado!"
```

---

## PASSO 12: CONFIGURAR NGINX (Execute na VPS)

```bash
# Criar configuração do site
cat > /etc/nginx/sites-available/painel-xui << 'EOF'
server {
    listen 80;
    server_name 5.189.164.31;
    root /var/www/painel-xui/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Ativar site
ln -s /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Testar configuração
nginx -t

# Reiniciar Nginx
systemctl restart nginx

echo "✅ Nginx configurado!"
```

---

## PASSO 13: OTIMIZAR LARAVEL (Execute na VPS)

```bash
cd /var/www/painel-xui

# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Criar caches otimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoloader
composer dump-autoload --optimize

echo "✅ Laravel otimizado!"
```

---

## PASSO 14: CONFIGURAR CRON (Execute na VPS)

```bash
# Adicionar cron do Laravel
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/painel-xui && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo "✅ Cron configurado!"
```

---

## PASSO 15: VERIFICAR STATUS (Execute na VPS)

```bash
# Verificar serviços
echo "=== STATUS DOS SERVIÇOS ==="
systemctl is-active nginx && echo "✅ Nginx: Ativo" || echo "❌ Nginx: Inativo"
systemctl is-active php8.2-fpm && echo "✅ PHP-FPM: Ativo" || echo "❌ PHP-FPM: Inativo"
systemctl is-active mysql && echo "✅ MySQL: Ativo" || echo "❌ MySQL: Inativo"

echo ""
echo "=== TESTE DE ACESSO ==="
echo "Acesse no navegador: http://5.189.164.31"
echo ""
echo "Se aparecer a página do Laravel, está funcionando!"
```

---

## PASSO 16: VER LOGS (Se houver erro)

```bash
# Ver logs do Laravel
tail -f /var/www/painel-xui/storage/logs/laravel.log

# Ver logs do Nginx
tail -f /var/log/nginx/error.log

# Ver logs do PHP
tail -f /var/log/php8.2-fpm.log
```

---

## 🎉 DEPLOY CONCLUÍDO!

Acesse: **http://5.189.164.31**

### Próximos Passos (Opcional):
1. Configurar domínio próprio
2. Instalar SSL (HTTPS) com Let's Encrypt
3. Configurar backup automático

---

## 🔐 CREDENCIAIS IMPORTANTES

### VPS
- IP: 5.189.164.31
- Usuário: root
- Senha: c11560011

### Banco de Dados
- Database: painel_xui
- Usuário: painel_user
- Senha: Painel@2026#Strong

### Aplicação
- URL: http://5.189.164.31
- Pasta: /var/www/painel-xui

---

## 🆘 COMANDOS DE EMERGÊNCIA

```bash
# Reiniciar tudo
systemctl restart nginx php8.2-fpm mysql

# Ver status
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql

# Limpar cache Laravel
cd /var/www/painel-xui
php artisan cache:clear
php artisan config:clear

# Ver logs
tail -f storage/logs/laravel.log
```

---

**IMPORTANTE:** Anote essas credenciais em local seguro!
