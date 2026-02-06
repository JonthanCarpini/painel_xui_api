# 🔧 Troubleshooting - Conexão com Banco XUI

## 📊 Status Atual

❌ **Problema:** Não conseguimos conectar ao banco de dados MySQL do XUI One.

**Testes realizados:**
- ❌ `192.168.100.210:3306` - Timeout (sem conectividade de rede)
- ❌ `127.0.0.1:3306` - Conexão recusada (MySQL não está rodando localmente)

## 🔍 Diagnóstico

### Cenário 1: XUI One está em servidor remoto (192.168.100.210)

**Problema:** Firewall ou configuração de rede bloqueando acesso.

**Soluções:**

#### A) No servidor XUI (192.168.100.210), via SSH:

```bash
# 1. Verificar se MySQL está rodando
sudo systemctl status mysql

# 2. Verificar se está escutando na porta 3306
sudo netstat -tlnp | grep 3306

# 3. Editar configuração do MySQL para aceitar conexões remotas
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Procure e comente ou altere:
# bind-address = 127.0.0.1
# Para:
bind-address = 0.0.0.0

# 4. Reiniciar MySQL
sudo systemctl restart mysql

# 5. Verificar firewall
sudo ufw status
sudo ufw allow 3306/tcp

# 6. Verificar se o usuário tem permissão remota
mysql -u root -p
```

Dentro do MySQL:
```sql
SELECT user, host FROM mysql.user WHERE user='painel_office';

-- Se não existir ou host for 'localhost', criar/atualizar:
CREATE USER 'painel_office'@'%' IDENTIFIED BY 'Flamengo@2015';
GRANT ALL PRIVILEGES ON xui.* TO 'painel_office'@'%';
FLUSH PRIVILEGES;
```

#### B) Testar conexão da sua máquina Windows:

```powershell
# Testar conectividade
Test-NetConnection -ComputerName 192.168.100.210 -Port 3306

# Testar com MySQL Client (se instalado)
mysql -h 192.168.100.210 -u painel_office -p xui
```

### Cenário 2: XUI One está no mesmo servidor Windows (localhost)

**Problema:** MySQL não está instalado ou não está rodando.

**Soluções:**

#### A) Verificar se MySQL/MariaDB está instalado:

```powershell
# Verificar serviços MySQL
Get-Service | Where-Object {$_.Name -like "*mysql*"}
Get-Service | Where-Object {$_.Name -like "*mariadb*"}

# Verificar processos
Get-Process | Where-Object {$_.Name -like "*mysql*"}
```

#### B) Se MySQL não estiver instalado, instalar:

**Opção 1: MySQL Community Server**
- Download: https://dev.mysql.com/downloads/mysql/
- Instalar e configurar porta 3306

**Opção 2: XAMPP (mais fácil)**
- Download: https://www.apachefriends.org/
- Inclui MySQL/MariaDB pré-configurado

**Opção 3: Laragon**
- Download: https://laragon.org/
- Ambiente completo com MySQL

#### C) Iniciar o serviço MySQL:

```powershell
# Se for serviço Windows
Start-Service MySQL80  # ou nome do seu serviço

# Se for XAMPP
# Abrir XAMPP Control Panel e iniciar MySQL
```

### Cenário 3: Usar Docker para MySQL (Recomendado para desenvolvimento)

```powershell
# Criar container MySQL com dados do XUI
docker run -d `
  --name xui-mysql `
  -p 3306:3306 `
  -e MYSQL_ROOT_PASSWORD=root `
  -e MYSQL_DATABASE=xui `
  -e MYSQL_USER=painel_office `
  -e MYSQL_PASSWORD=Flamengo@2015 `
  mysql:8.0

# Importar dump do banco
docker exec -i xui-mysql mysql -upainel_office -pFlamengo@2015 xui < c:\Users\admin\Documents\Projetos\painel_xui\documentos\backup_2026-02-04_00_38_02.sql
```

Depois atualizar `.env`:
```env
XUI_DB_HOST=127.0.0.1
XUI_DB_PORT=3306
XUI_DB_DATABASE=xui
XUI_DB_USERNAME=painel_office
XUI_DB_PASSWORD=Flamengo@2015
```

## 🎯 Próximos Passos

**Por favor, me informe:**

1. **Onde está o XUI One?**
   - [ ] Servidor remoto (192.168.100.210)
   - [ ] Mesma máquina Windows (localhost)
   - [ ] Outro local

2. **Você tem acesso SSH ao servidor 192.168.100.210?**
   - [ ] Sim
   - [ ] Não

3. **Prefere usar Docker para desenvolvimento local?**
   - [ ] Sim (mais fácil)
   - [ ] Não (usar MySQL existente)

## 🔄 Configurações Alternativas

### Se o banco estiver em outro IP/porta:

Edite `c:\Users\admin\Documents\Projetos\painel_xui\app\.env`:

```env
XUI_DB_HOST=SEU_IP_AQUI
XUI_DB_PORT=3306
XUI_DB_DATABASE=xui
XUI_DB_USERNAME=painel_office
XUI_DB_PASSWORD=Flamengo@2015
```

Depois:
```powershell
cd c:\Users\admin\Documents\Projetos\painel_xui\app
php artisan config:clear
php test-db-connection.php
```

## 📞 Suporte

Se nenhuma das soluções funcionar, forneça:
- Localização do servidor XUI One
- Saída do comando: `Test-NetConnection -ComputerName 192.168.100.210 -Port 3306`
- Se tem acesso SSH ao servidor
- Logs de erro completos
