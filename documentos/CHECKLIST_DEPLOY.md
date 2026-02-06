# ✅ CHECKLIST DE PRÉ-DEPLOY - PAINEL XUI

## 📋 ANTES DE FAZER DEPLOY

### 1. Preparação do Código
- [ ] Código testado localmente
- [ ] Todas as funcionalidades funcionando
- [ ] Sem erros no console do navegador
- [ ] Sem erros nos logs do Laravel
- [ ] Migrations testadas
- [ ] Seeds funcionando (se houver)

### 2. Configuração do Ambiente
- [ ] Arquivo `.env.example` atualizado
- [ ] Variáveis de ambiente documentadas
- [ ] Credenciais de banco de dados preparadas
- [ ] URL do servidor XUI configurada
- [ ] Chaves de API configuradas (se houver)

### 3. Segurança
- [ ] `APP_DEBUG=false` no `.env` de produção
- [ ] `APP_ENV=production` configurado
- [ ] Senhas fortes para banco de dados
- [ ] Firewall configurado na VPS
- [ ] SSL/HTTPS configurado
- [ ] Backup automático configurado

### 4. Performance
- [ ] Caches otimizados (`config:cache`, `route:cache`, `view:cache`)
- [ ] Composer com `--optimize-autoloader --no-dev`
- [ ] Assets compilados para produção
- [ ] Queries otimizadas (sem N+1)
- [ ] Índices de banco de dados criados

### 5. Monitoramento
- [ ] Logs configurados (`LOG_CHANNEL=daily`)
- [ ] Supervisor configurado para queues
- [ ] Cron configurado para scheduler
- [ ] Fail2Ban instalado
- [ ] Backups automáticos funcionando

---

## 🚀 DURANTE O DEPLOY

### Passo a Passo
1. [ ] Conectar na VPS via SSH
2. [ ] Fazer backup do banco de dados atual
3. [ ] Ativar modo de manutenção (`php artisan down`)
4. [ ] Atualizar código (git pull ou upload)
5. [ ] Instalar dependências (`composer install`)
6. [ ] Executar migrations (`php artisan migrate --force`)
7. [ ] Limpar e recriar caches
8. [ ] Ajustar permissões
9. [ ] Reiniciar serviços (Nginx, PHP-FPM, Supervisor)
10. [ ] Desativar modo de manutenção (`php artisan up`)
11. [ ] Testar aplicação

---

## 🧪 PÓS-DEPLOY

### Testes Essenciais
- [ ] Página inicial carrega
- [ ] Login funciona
- [ ] Dashboard carrega
- [ ] Listagem de clientes funciona
- [ ] Criação de cliente funciona
- [ ] Renovação funciona
- [ ] Exportações funcionam
- [ ] Busca funciona
- [ ] Paginação funciona
- [ ] SSL/HTTPS ativo
- [ ] Sem erros no console
- [ ] Sem erros nos logs

### Monitoramento
- [ ] Verificar logs: `tail -f storage/logs/laravel.log`
- [ ] Verificar uso de CPU/RAM: `htop`
- [ ] Verificar espaço em disco: `df -h`
- [ ] Verificar status dos serviços
- [ ] Testar backup automático

---

## 🔧 COMANDOS ÚTEIS

### Verificar Status
```bash
# Status dos serviços
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo supervisorctl status

# Logs em tempo real
tail -f /var/www/painel-xui/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Uso de recursos
htop
df -h
```

### Manutenção
```bash
# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Recriar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar serviços
sudo systemctl restart nginx php8.2-fpm
sudo supervisorctl restart all
```

### Backup Manual
```bash
# Banco de dados
mysqldump -u painel_user -p painel_xui > backup_$(date +%Y%m%d).sql

# Arquivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/painel-xui
```

---

## 🆘 TROUBLESHOOTING

### Erro 500
1. Verificar logs: `tail -f storage/logs/laravel.log`
2. Verificar permissões: `sudo chmod -R 775 storage bootstrap/cache`
3. Limpar caches: `php artisan cache:clear`
4. Verificar `.env`

### Site Lento
1. Verificar uso de recursos: `htop`
2. Otimizar Laravel: `php artisan optimize`
3. Verificar queries lentas no MySQL
4. Aumentar recursos da VPS se necessário

### Erro de Conexão MySQL
1. Verificar credenciais no `.env`
2. Testar conexão: `mysql -u painel_user -p painel_xui`
3. Verificar se MySQL está rodando: `sudo systemctl status mysql`
4. Verificar logs: `tail -f /var/log/mysql/error.log`

### SSL Não Funciona
1. Verificar certificado: `sudo certbot certificates`
2. Renovar certificado: `sudo certbot renew`
3. Verificar configuração Nginx: `sudo nginx -t`
4. Reiniciar Nginx: `sudo systemctl restart nginx`

---

## 📞 CONTATOS DE EMERGÊNCIA

- **Provedor VPS:** [Link do suporte]
- **Documentação Laravel:** https://laravel.com/docs
- **Documentação Nginx:** https://nginx.org/en/docs/
- **Stack Overflow:** https://stackoverflow.com/questions/tagged/laravel

---

## 📊 MÉTRICAS DE SUCESSO

### Performance
- [ ] Tempo de carregamento < 2 segundos
- [ ] Uso de CPU < 70%
- [ ] Uso de RAM < 80%
- [ ] Uptime > 99.9%

### Segurança
- [ ] SSL A+ no SSL Labs
- [ ] Sem vulnerabilidades conhecidas
- [ ] Backups diários funcionando
- [ ] Firewall ativo

### Funcionalidade
- [ ] Todas as features funcionando
- [ ] Sem erros críticos nos logs
- [ ] Usuários conseguem acessar
- [ ] Integrações funcionando

---

**Última atualização:** 04/02/2026
**Versão:** 1.0
