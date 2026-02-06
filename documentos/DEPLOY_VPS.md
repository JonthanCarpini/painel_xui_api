# 🚀 GUIA COMPLETO DE DEPLOY - PAINEL XUI

## 📋 REQUISITOS MÍNIMOS DA VPS

### Especificações Recomendadas
- **CPU:** 2 vCPUs
- **RAM:** 4GB (mínimo 2GB)
- **Disco:** 40GB SSD
- **Banda:** 100 Mbps
- **SO:** Ubuntu 22.04 LTS

### Provedores Recomendados
- **DigitalOcean** - $24/mês (4GB RAM, 2 vCPUs)
- **Vultr** - $18/mês (4GB RAM, 2 vCPUs)
- **Linode** - $24/mês (4GB RAM, 2 vCPUs)
- **Contabo** - €8.99/mês (4GB RAM, 2 vCPUs) - Melhor custo-benefício

---

## 🔧 PASSO 1: PREPARAÇÃO DO SERVIDOR

### 1.1 Conectar via SSH
```bash
ssh root@SEU_IP_VPS
```

### 1.2 Atualizar Sistema
```bash
apt update && apt upgrade -y
```

### 1.3 Criar Usuário (Segurança)
```bash
adduser deploy
usermod -aG sudo deploy
su - deploy
```

### 1.4 Configurar Firewall
```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## 📦 PASSO 2: INSTALAR DEPENDÊNCIAS

### 2.1 Instalar Nginx
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 2.2 Instalar PHP 8.2
```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml \
php8.2-bcmath php8.2-intl php8.2-redis -y
```

### 2.3 Instalar Composer
```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

### 2.4 Instalar MySQL 8.0
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql

# Configurar MySQL
sudo mysql_secure_installation
```

**Configuração MySQL:**
- Remove anonymous users? **Y**
- Disallow root login remotely? **Y**
- Remove test database? **Y**
- Reload privilege tables? **Y**

### 2.5 Criar Banco de Dados
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE painel_xui CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'painel_user'@'localhost' IDENTIFIED BY 'SENHA_FORTE_AQUI';
GRANT ALL PRIVILEGES ON painel_xui.* TO 'painel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.6 Instalar Git
```bash
sudo apt install git -y
```

### 2.7 Instalar Node.js (para build de assets)
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
node --version
npm --version
```

---

## 📂 PASSO 3: DEPLOY DA APLICAÇÃO

### 3.1 Clonar Repositório
```bash
cd /var/www
sudo mkdir painel-xui
sudo chown -R deploy:deploy painel-xui
cd painel-xui

# Se usar Git
git clone https://github.com/SEU_USUARIO/painel-xui.git .

# Ou fazer upload via SFTP/SCP
```

### 3.2 Instalar Dependências PHP
```bash
composer install --optimize-autoloader --no-dev
```

### 3.3 Configurar Ambiente
```bash
cp .env.example .env
nano .env
```

**Configurar .env:**
```env
APP_NAME="Painel XUI"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://seudominio.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=painel_xui
DB_USERNAME=painel_user
DB_PASSWORD=SENHA_FORTE_AQUI

# Conexão XUI (banco externo)
DB_XUI_HOST=IP_DO_SERVIDOR_XUI
DB_XUI_PORT=3306
DB_XUI_DATABASE=xtream_iptvpro
DB_XUI_USERNAME=usuario_xui
DB_XUI_PASSWORD=senha_xui

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 3.4 Gerar Application Key
```bash
php artisan key:generate
```

### 3.5 Importar Banco de Dados
```bash
# Importar estrutura do painel
mysql -u painel_user -p painel_xui < documentos/backup_2026-02-04_00_38_02.sql
```

### 3.6 Executar Migrations (se houver)
```bash
php artisan migrate --force
```

### 3.7 Configurar Permissões
```bash
sudo chown -R deploy:www-data /var/www/painel-xui
sudo chmod -R 755 /var/www/painel-xui
sudo chmod -R 775 /var/www/painel-xui/storage
sudo chmod -R 775 /var/www/painel-xui/bootstrap/cache
```

### 3.8 Otimizar Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 PASSO 4: CONFIGURAR NGINX

### 4.1 Criar Configuração do Site
```bash
sudo nano /etc/nginx/sites-available/painel-xui
```

**Conteúdo:**
```nginx
server {
    listen 80;
    server_name seudominio.com www.seudominio.com;
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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 Ativar Site
```bash
sudo ln -s /etc/nginx/sites-available/painel-xui /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔒 PASSO 5: CONFIGURAR SSL (Let's Encrypt)

### 5.1 Instalar Certbot
```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 5.2 Obter Certificado SSL
```bash
sudo certbot --nginx -d seudominio.com -d www.seudominio.com
```

**Opções:**
- Email: seu@email.com
- Agree to terms: **Y**
- Share email: **N**
- Redirect HTTP to HTTPS: **2** (Sim)

### 5.3 Renovação Automática
```bash
sudo certbot renew --dry-run
```

O Certbot configura renovação automática via cron.

---

## ⚙️ PASSO 6: CONFIGURAÇÕES ADICIONAIS

### 6.1 Configurar PHP-FPM
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

**Ajustar:**
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

```bash
sudo systemctl restart php8.2-fpm
```

### 6.2 Configurar Cron (Scheduler)
```bash
crontab -e
```

**Adicionar:**
```
* * * * * cd /var/www/painel-xui && php artisan schedule:run >> /dev/null 2>&1
```

### 6.3 Configurar Supervisor (Queues)
```bash
sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/painel-xui-worker.conf
```

**Conteúdo:**
```ini
[program:painel-xui-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/painel-xui/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/painel-xui/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start painel-xui-worker:*
```

---

## 🔐 PASSO 7: SEGURANÇA ADICIONAL

### 7.1 Configurar Fail2Ban
```bash
sudo apt install fail2ban -y
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

**Configurar:**
```ini
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 3600
```

```bash
sudo systemctl restart fail2ban
```

### 7.2 Desabilitar Root Login SSH
```bash
sudo nano /etc/ssh/sshd_config
```

**Alterar:**
```
PermitRootLogin no
PasswordAuthentication no  # Usar apenas chaves SSH
```

```bash
sudo systemctl restart sshd
```

### 7.3 Configurar Backup Automático
```bash
sudo nano /usr/local/bin/backup-painel.sh
```

**Script:**
```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="/home/deploy/backups"
mkdir -p $BACKUP_DIR

# Backup banco de dados
mysqldump -u painel_user -pSENHA_FORTE_AQUI painel_xui > $BACKUP_DIR/db_$DATE.sql

# Backup arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/painel-xui

# Manter apenas últimos 7 dias
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup concluído: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/backup-painel.sh
crontab -e
```

**Adicionar:**
```
0 2 * * * /usr/local/bin/backup-painel.sh >> /var/log/backup-painel.log 2>&1
```

---

## 🚀 PASSO 8: DEPLOY CONTÍNUO (OPCIONAL)

### 8.1 Script de Deploy Automatizado
```bash
nano /var/www/painel-xui/deploy.sh
```

**Conteúdo:**
```bash
#!/bin/bash
set -e

echo "🚀 Iniciando deploy..."

# Entrar no diretório
cd /var/www/painel-xui

# Modo manutenção
php artisan down

# Atualizar código
git pull origin main

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Limpar e recriar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar migrations
php artisan migrate --force

# Reiniciar serviços
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
sudo supervisorctl restart painel-xui-worker:*

# Sair do modo manutenção
php artisan up

echo "✅ Deploy concluído com sucesso!"
```

```bash
chmod +x deploy.sh
```

**Executar deploy:**
```bash
./deploy.sh
```

---

## 📊 PASSO 9: MONITORAMENTO

### 9.1 Logs Importantes
```bash
# Logs Laravel
tail -f /var/www/painel-xui/storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Logs PHP-FPM
tail -f /var/log/php8.2-fpm.log

# Logs MySQL
tail -f /var/log/mysql/error.log
```

### 9.2 Monitorar Recursos
```bash
# CPU e RAM
htop

# Espaço em disco
df -h

# Processos PHP
ps aux | grep php

# Status dos serviços
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo supervisorctl status
```

---

## 🔧 TROUBLESHOOTING

### Erro 500
```bash
# Ver logs
tail -f /var/www/painel-xui/storage/logs/laravel.log

# Verificar permissões
sudo chown -R deploy:www-data /var/www/painel-xui
sudo chmod -R 775 /var/www/painel-xui/storage
```

### Erro de Conexão MySQL
```bash
# Testar conexão
mysql -u painel_user -p painel_xui

# Ver logs
tail -f /var/log/mysql/error.log
```

### Site Lento
```bash
# Otimizar Laravel
php artisan optimize

# Verificar uso de recursos
htop

# Limpar logs antigos
find /var/www/painel-xui/storage/logs -name "*.log" -mtime +30 -delete
```

---

## ✅ CHECKLIST FINAL

- [ ] Servidor atualizado
- [ ] Firewall configurado
- [ ] Nginx instalado e rodando
- [ ] PHP 8.2 instalado
- [ ] MySQL instalado e seguro
- [ ] Aplicação deployada
- [ ] SSL configurado (HTTPS)
- [ ] Permissões corretas
- [ ] Cron configurado
- [ ] Supervisor configurado
- [ ] Fail2Ban ativo
- [ ] Backup automático configurado
- [ ] Logs funcionando
- [ ] Teste completo do sistema

---

## 🎯 COMANDOS ÚTEIS

```bash
# Reiniciar todos os serviços
sudo systemctl restart nginx php8.2-fpm mysql
sudo supervisorctl restart all

# Ver status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# Limpar cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Backup manual
mysqldump -u painel_user -p painel_xui > backup_$(date +%Y%m%d).sql
```

---

## 📞 SUPORTE

Em caso de problemas, verifique:
1. Logs do Laravel: `/var/www/painel-xui/storage/logs/laravel.log`
2. Logs do Nginx: `/var/log/nginx/error.log`
3. Logs do PHP: `/var/log/php8.2-fpm.log`
4. Permissões dos arquivos
5. Configuração do `.env`

---

**Documentação criada em:** 04/02/2026
**Versão:** 1.0
