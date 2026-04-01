@echo off
REM Script de Instalação do Módulo de Telemedicina - Windows
REM MedAngola Cloud

echo ======================================
echo Instalacao do Modulo de Telemedicina
echo ======================================
echo.

REM Verificar se esta no diretorio correto
if not exist "artisan" (
    echo Erro: Execute este script no diretorio apps/api/
    pause
    exit /b 1
)

echo 1. Executando migrations...
call php artisan migrate

if %ERRORLEVEL% EQU 0 (
    echo OK - Migrations executadas com sucesso!
) else (
    echo ERRO - Erro ao executar migrations
    pause
    exit /b 1
)

echo.
echo 2. Limpando cache...
call php artisan cache:clear
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

echo OK - Cache limpo!

echo.
echo 3. Verificando configuracao...

REM Verificar se .env existe
if not exist ".env" (
    echo Aviso: .env nao encontrado. Copie .env.example
    copy .env.example .env
    call php artisan key:generate
)

echo.
echo 4. Rodando testes do modulo...
call php artisan test --filter TelemedicineTest

echo.
echo 5. Listando rotas de telemedicina...
call php artisan route:list --path=telemedicine

echo.
echo ======================================
echo Instalacao concluida!
echo ======================================
echo.
echo Proximos passos:
echo 1. Configure as variaveis de ambiente no .env
echo 2. Opcional: Instale Laravel Reverb para chat em tempo real
echo    composer require laravel/reverb
echo    php artisan reverb:install
echo.
echo 3. Para iniciar o servidor Reverb:
echo    php artisan reverb:start
echo.
echo 4. Acesse a documentacao completa em:
echo    TELEMEDICINA_README.md
echo.
echo ======================================
pause
