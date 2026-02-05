# 🚀 Início Rápido - Painel Office IPTV

## ⚡ Instalação em 5 Minutos

### 1️⃣ Configure o Ambiente

```bash
# Copie o arquivo de configuração
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 2️⃣ Edite o .env

Abra o arquivo `.env` e configure:

```env
# Configurações XUI (OBRIGATÓRIO)
XUI_BASE_URL=http://SEU_SERVIDOR/SEU_ACCESS_CODE/
XUI_API_KEY=SUA_API_KEY
XUI_TIMEOUT=30

# Streaming
XUI_STREAM_PROTOCOL=http
XUI_STREAM_SERVER=SEU_SERVIDOR
XUI_STREAM_PORT=80
```

**Exemplo Real:**
```env
XUI_BASE_URL=http://192.168.100.209/kIzFSjQu/
XUI_API_KEY=DFE74ECCBA19D32DCD758C4D3D5AF0F6
```

### 3️⃣ Inicie o Servidor

```bash
php artisan serve
```

### 4️⃣ Acesse o Sistema

Abra seu navegador em: **http://localhost:8000**

### 5️⃣ Faça Login

Use suas credenciais do XUI:
- **Usuário**: Seu username do painel XUI
- **Senha**: Sua senha do painel XUI

---

## 📱 Primeiros Passos

### Criar Seu Primeiro Cliente

1. Acesse **Dashboard**
2. Clique em **"Criar Novo Cliente"**
3. Preencha os dados:
   - Usuário e senha
   - Selecione o pacote
   - Escolha os canais (buquês)
   - Defina a duração
4. Clique em **"Criar Cliente"**

### Gerar um Teste Grátis

1. No Dashboard, clique em **"Gerar Teste Rápido"**
2. Preencha usuário e senha
3. Selecione a duração (3h, 6h, 12h, etc)
4. Escolha os canais
5. Clique em **"Gerar Teste"**

### Monitorar Conexões

1. Acesse o menu **"Monitoramento"**
2. Visualize quem está assistindo em tempo real
3. Se necessário, derrube conexões suspeitas

---

## 🎯 Funcionalidades Principais

### 📊 Dashboard
- Visualize estatísticas em tempo real
- Saldo de créditos sempre visível
- Cards com total de clientes, ativos, vencidos e online

### 👥 Gestão de Clientes
- **Criar**: Clientes oficiais com débito de créditos
- **Renovar**: Adicione dias ao vencimento
- **M3U**: Gere links de acesso automaticamente
- **Excluir**: Remova clientes quando necessário

### ⏱️ Testes Gratuitos
- Duração de 3 a 72 horas
- Não consome créditos
- Apenas 1 conexão simultânea
- Ideal para demonstração

### 📡 Monitoramento
- Conexões ativas em tempo real
- Informações de IP e duração
- Derrubar conexões suspeitas
- Atualização automática a cada 30 segundos

### 🏪 Revendedores (Apenas Admin)
- Criar sub-revendedores
- Definir créditos iniciais
- Recarregar saldo
- Bloquear/desbloquear acesso

---

## 🔑 Diferenças entre Admin e Revendedor

### 👑 Administrador (Group ID: 1)
✅ Criar e gerenciar clientes  
✅ Gerar testes gratuitos  
✅ Monitorar conexões  
✅ **Criar revendedores**  
✅ **Recarregar créditos**  
✅ **Acesso total**

### 👤 Revendedor (Group ID: 2)
✅ Criar e gerenciar clientes  
✅ Gerar testes gratuitos  
✅ Monitorar conexões  
❌ Criar revendedores  
❌ Recarregar créditos de outros  
❌ Acesso administrativo

---

## 📋 Checklist de Configuração

- [ ] Arquivo `.env` configurado com credenciais XUI
- [ ] Chave da aplicação gerada (`php artisan key:generate`)
- [ ] Servidor iniciado (`php artisan serve`)
- [ ] Login realizado com sucesso
- [ ] Dashboard carregando corretamente
- [ ] Pacotes e buquês aparecendo nos formulários

---

## ⚠️ Problemas Comuns

### ❌ Erro ao fazer login
**Solução**: Verifique se `XUI_BASE_URL` e `XUI_API_KEY` estão corretos no `.env`

### ❌ Pacotes não aparecem
**Solução**: Limpe o cache com `php artisan cache:clear`

### ❌ Erro ao criar cliente
**Solução**: Verifique se você tem créditos suficientes

### ❌ Página em branco
**Solução**: Verifique os logs em `storage/logs/laravel.log`

---

## 🎨 Interface

O sistema possui:
- **Tema escuro** moderno e elegante
- **Sidebar** com navegação intuitiva
- **Cards** com gradientes e animações
- **Responsivo** para mobile e tablet
- **Ícones** do Bootstrap Icons

---

## 📞 Suporte

Para dúvidas técnicas:
1. Consulte o `README_PAINEL_OFFICE.md`
2. Verifique os logs do Laravel
3. Revise a documentação da API XUI

---

## 🎉 Pronto!

Seu Painel Office está configurado e pronto para uso!

**Próximos passos:**
1. Crie seu primeiro cliente
2. Gere um teste para demonstração
3. Monitore as conexões
4. (Admin) Crie revendedores se necessário

**Boas vendas! 💰**
