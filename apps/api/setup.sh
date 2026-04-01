#!/bin/bash

echo "🚀 Iniciando setup do MedAngola Cloud API..."

# Verificar se está no diretório correto
if [ ! -f "composer.json" ]; then
    echo "❌ Execute este script na pasta apps/api"
    exit 1
fi

# Instalar dependências PHP
echo "📦 Instalando dependências Composer..."
composer install --no-interaction --prefer-dist

# Copiar .env
if [ ! -f ".env" ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
fi

# Gerar chave da aplicação
echo "🔑 Gerando APP_KEY..."
php artisan key:generate

# Configurar banco SQLite para desenvolvimento rápido
read -p "Usar SQLite para desenvolvimento rápido? (s/n): " use_sqlite
if [ "$use_sqlite" = "s" ]; then
    echo "📊 Configurando SQLite..."
    touch database/database.sqlite
    sed -i.bak 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i.bak 's/# DB_HOST.*/# DB_CONNECTION=sqlite\n# DB_DATABASE=database\/database.sqlite/' .env
    rm .env.bak 2>/dev/null || true
else
    echo "💡 Configure manualmente o PostgreSQL no arquivo .env"
fi

# Rodar migrations
echo "🗄️  Rodando migrations..."
php artisan migrate --force

# Popular banco com seeders
read -p "Deseja popular o banco com dados de teste? (s/n): " run_seeds
if [ "$run_seeds" = "s" ]; then
    echo "🌱 Rodando seeders..."
    php artisan db:seed
fi

# Instalar dependências NPM
echo "📦 Instalando dependências NPM..."
npm install

# Build dos assets
echo "🔨 Buildando assets..."
npm run build

echo ""
echo "✅ Setup concluído!"
echo ""
echo "📌 Próximos passos:"
echo "   1. Configure o banco de dados no .env (se não usou SQLite)"
echo "   2. Execute: php artisan serve"
echo "   3. Acesse: http://localhost:8000"
echo ""
echo "📚 Usuário de teste (se rodou seeders):"
echo "   Email: teste@clinica.com"
echo "   Senha: password"
echo "   Clinic ID: 1"
echo ""
