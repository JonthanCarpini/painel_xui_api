@echo off
echo ========================================
echo   PAINEL OFFICE IPTV - INSTALACAO
echo ========================================
echo.

echo [1/5] Copiando arquivo de configuracao...
if not exist .env (
    copy .env.example .env
    echo Arquivo .env criado!
) else (
    echo Arquivo .env ja existe!
)
echo.

echo [2/5] Gerando chave da aplicacao...
php artisan key:generate
echo.

echo [3/5] Limpando cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo [4/5] Verificando configuracoes...
echo IMPORTANTE: Edite o arquivo .env e configure:
echo - XUI_BASE_URL (URL do seu servidor XUI)
echo - XUI_API_KEY (Sua chave de API)
echo.

echo [5/5] Instalacao concluida!
echo.
echo ========================================
echo   PROXIMOS PASSOS
echo ========================================
echo 1. Edite o arquivo .env com suas credenciais XUI
echo 2. Execute: php artisan serve
echo 3. Acesse: http://localhost:8000
echo 4. Faca login com suas credenciais do XUI
echo.
echo Documentacao completa em: README_PAINEL_OFFICE.md
echo Guia rapido em: INICIO_RAPIDO.md
echo.
pause
