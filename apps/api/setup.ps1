Write-Host "🚀 Iniciando setup do MedAngola Cloud API..." -ForegroundColor Green
Write-Host ""

# Verificar se está no diretório correto
if (-not (Test-Path "composer.json")) {
    Write-Host "❌ Execute este script na pasta apps/api" -ForegroundColor Red
    exit 1
}

# Instalar dependências PHP
Write-Host "📦 Instalando dependências Composer..." -ForegroundColor Cyan
composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erro ao instalar dependências Composer" -ForegroundColor Red
    exit 1
}

# Copiar .env
if (-not (Test-Path ".env")) {
    Write-Host "📝 Criando arquivo .env..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
}

# Gerar chave da aplicação
Write-Host "🔑 Gerando APP_KEY..." -ForegroundColor Cyan
php artisan key:generate

# Configurar banco SQLite para desenvolvimento rápido
$useSqlite = Read-Host "Usar SQLite para desenvolvimento rápido? (s/n)"
if ($useSqlite -eq "s") {
    Write-Host "📊 Configurando SQLite..." -ForegroundColor Cyan
    if (-not (Test-Path "database/database.sqlite")) {
        New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
    }
    
    $envContent = Get-Content ".env"
    $envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=sqlite"
    $envContent = $envContent -replace "# DB_HOST=.*", "# DB_CONNECTION=sqlite`n# DB_DATABASE=database/database.sqlite"
    Set-Content ".env" $envContent
} else {
    Write-Host "💡 Configure manualmente o PostgreSQL no arquivo .env" -ForegroundColor Yellow
}

# Rodar migrations
Write-Host "🗄️  Rodando migrations..." -ForegroundColor Cyan
php artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Erro ao rodar migrations. Verifique a configuração do banco de dados." -ForegroundColor Yellow
}

# Popular banco com seeders
$runSeeds = Read-Host "Deseja popular o banco com dados de teste? (s/n)"
if ($runSeeds -eq "s") {
    Write-Host "🌱 Rodando seeders..." -ForegroundColor Cyan
    php artisan db:seed
    if ($LASTEXITCODE -ne 0) {
        Write-Host "⚠️  Erro ao rodar seeders" -ForegroundColor Yellow
    }
}

# Instalar dependências NPM
Write-Host "📦 Instalando dependências NPM..." -ForegroundColor Cyan
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Erro ao instalar dependências NPM" -ForegroundColor Yellow
}

# Build dos assets
Write-Host "🔨 Buildando assets..." -ForegroundColor Cyan
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Erro ao buildar assets" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ Setup concluído!" -ForegroundColor Green
Write-Host ""
Write-Host "📌 Próximos passos:" -ForegroundColor Yellow
Write-Host "   1. Configure o banco de dados no .env (se não usou SQLite)"
Write-Host "   2. Execute: php artisan serve"
Write-Host "   3. Acesse: http://localhost:8000"
Write-Host ""
Write-Host "📚 Usuário de teste (se rodou seeders):" -ForegroundColor Green
Write-Host "   Email: teste@clinica.com"
Write-Host "   Senha: password"
Write-Host "   Clinic ID: 1"
Write-Host ""
