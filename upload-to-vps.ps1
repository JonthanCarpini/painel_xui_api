# Script para fazer upload do código para VPS
$VPS_IP = "5.189.164.31"
$VPS_USER = "root"
$LOCAL_PATH = "C:\Users\admin\Documents\Projetos\painel_xui\app"
$REMOTE_PATH = "/var/www/painel-xui"

Write-Host "📤 Fazendo upload do código para VPS..." -ForegroundColor Green
Write-Host ""

# Verificar se a pasta existe
if (-not (Test-Path $LOCAL_PATH)) {
    Write-Host "❌ Erro: Pasta $LOCAL_PATH não encontrada!" -ForegroundColor Red
    exit 1
}

# Criar arquivo TAR
Write-Host "📦 Compactando arquivos..." -ForegroundColor Cyan
Set-Location $LOCAL_PATH
tar -czf painel-xui.tar.gz *

if (-not (Test-Path "painel-xui.tar.gz")) {
    Write-Host "❌ Erro ao criar arquivo compactado!" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Arquivo compactado criado!" -ForegroundColor Green

# Enviar para VPS
Write-Host "`n📤 Enviando para VPS..." -ForegroundColor Cyan
Write-Host "Senha: c11560011" -ForegroundColor Yellow
scp painel-xui.tar.gz "${VPS_USER}@${VPS_IP}:/tmp/"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Upload concluído!" -ForegroundColor Green
    
    # Descompactar na VPS
    Write-Host "`n📦 Descompactando na VPS..." -ForegroundColor Cyan
    ssh "${VPS_USER}@${VPS_IP}" "cd /var/www/painel-xui && tar -xzf /tmp/painel-xui.tar.gz && rm /tmp/painel-xui.tar.gz && ls -la"
    
    Write-Host "`n✅ Código enviado com sucesso!" -ForegroundColor Green
    
    # Limpar arquivo local
    Remove-Item painel-xui.tar.gz
} else {
    Write-Host "❌ Erro no upload!" -ForegroundColor Red
    Remove-Item painel-xui.tar.gz -ErrorAction SilentlyContinue
    exit 1
}

Write-Host "`n🎯 Próximo passo: Configurar aplicação" -ForegroundColor Yellow
