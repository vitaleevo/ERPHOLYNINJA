#!/bin/bash

# Script de configuração do ambiente de desenvolvimento MedAngola (Ubuntu)
# Este script configura o ambiente completo para executar o projeto

set -e

echo "========================================="
echo "  Configuração do Ambiente MedAngola"
echo "========================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Função para verificar comando
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# 1. Atualizar sistema
echo -e "${GREEN}[1/8]${NC} Atualizando sistema..."
sudo apt update && sudo apt upgrade -y

# 2. Instalar dependências do sistema
echo -e "${GREEN}[2/8]${NC} Instalando dependências do sistema..."
sudo apt install -y \
    curl \
    wget \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    postgresql-client \
    redis-tools

# 3. Instalar PHP e extensões
echo -e "${GREEN}[3/8]${NC} Instalando PHP 8.3 e extensões..."

# Adicionar repositório PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
    php8.3 \
    php8.3-cli \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-sqlite3 \
    php8.3-curl \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-redis \
    php8.3-imap \
    php8.3-ldap

# 4. Instalar Composer
echo -e "${GREEN}[4/8]${NC} Instalando Composer..."
if ! command_exists composer; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# 5. Instalar Node.js e npm
echo -e "${GREEN}[5/8]${NC} Instalando Node.js 20..."
if ! command_exists node; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi

echo "Node.js version: $(node -v)"
echo "npm version: $(npm -v)"

# 6. Configurar diretório do projeto
echo -e "${GREEN}[6/8]${NC} Configurando projeto..."

PROJECT_DIR="/var/www/html/medangola"
API_DIR="$PROJECT_DIR/Sodo clinica/apps/api"
WEB_DIR="$PROJECT_DIR/apps/web"

# Criar diretório
sudo mkdir -p "$PROJECT_DIR"
cd "$PROJECT_DIR"

# 7. Configurar API (Laravel)
echo -e "${GREEN}[7/8]${NC} Configurando API Laravel..."

cd "$API_DIR"

# Copiar arquivo de ambiente
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Gerar chave da aplicação
php artisan key:generate

# Criar banco de dados SQLite
touch database/database.sqlite

# Instalar dependências Composer
composer install --no-interaction --prefer-dist

# Executar migrações
php artisan migrate --force

# Executar seeders
php artisan db:seed --force

# 8. Configurar Frontend (Next.js)
echo -e "${GREEN}[8/8]${NC} Configurando Frontend Next.js..."

cd "$WEB_DIR"

# Instalar dependências npm
npm install

# Criar link simbólico para API (se necessário)
# cp -r "$API_DIR" "$PROJECT_DIR/Sodo clinica"

echo ""
echo "========================================="
echo -e "${GREEN}  Configuração concluída!${NC}"
echo "========================================="
echo ""
echo "Para iniciar os serviços:"
echo ""
echo "Frontend (Next.js):"
echo "  cd $WEB_DIR"
echo "  npm run dev"
echo "  -> Acesse: http://localhost:3000"
echo ""
echo "Backend (Laravel):"
echo "  cd $API_DIR"
echo "  php artisan serve --host=0.0.0.0 --port=8000"
echo "  -> API: http://localhost:8000"
echo ""
echo "Credenciais de teste:"
echo "  Email: admin@medangola.com"
echo "  Senha: password"
echo ""