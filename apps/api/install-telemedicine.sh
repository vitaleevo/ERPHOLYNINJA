#!/bin/bash

# Script de Instalação do Módulo de Telemedicina
# MedAngola Cloud

echo "======================================"
echo "Instalação do Módulo de Telemedicina"
echo "======================================"
echo ""

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo -e "${RED}Erro: Execute este script no diretório apps/api/${NC}"
    exit 1
fi

echo -e "${YELLOW}1. Executando migrations...${NC}"
php artisan migrate

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations executadas com sucesso!${NC}"
else
    echo -e "${RED}✗ Erro ao executar migrations${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}2. Limpando cache...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo -e "${GREEN}✓ Cache limpo!${NC}"

echo ""
echo -e "${YELLOW}3. Verificando configuração...${NC}"

# Verificar se .env existe
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}Aviso: .env não encontrado. Copie .env.example${NC}"
    cp .env.example .env
    php artisan key:generate
fi

# Verificar configurações de telemedicina
if grep -q "VIDEOCONFERENCE_PROVIDER" .env; then
    echo -e "${GREEN}✓ Configurações de telemedicina encontradas${NC}"
else
    echo -e "${YELLOW}Adicione as seguintes linhas ao seu .env:${NC}"
    echo ""
    echo "# Telemedicina Configuration"
    echo "VIDEOCONFERENCE_PROVIDER=jitsi"
    echo "VIDEOCONFERENCE_BASE_URL=https://meet.jit.si"
    echo "VIDEOCONFERENCE_API_KEY="
    echo "VIDEOCONFERENCE_API_SECRET="
    echo ""
fi

echo ""
echo -e "${YELLOW}4. Rodando testes do módulo...${NC}"
php artisan test --filter TelemedicineTest

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Testes passaram!${NC}"
else
    echo -e "${YELLOW}⚠ Alguns testes falharam ou não foram encontrados${NC}"
fi

echo ""
echo -e "${YELLOW}5. Listando rotas de telemedicina...${NC}"
php artisan route:list --path=telemedicine

echo ""
echo "======================================"
echo -e "${GREEN}Instalação concluída!${NC}"
echo "======================================"
echo ""
echo "Próximos passos:"
echo "1. Configure as variáveis de ambiente no .env"
echo "2. (Opcional) Instale Laravel Reverb para chat em tempo real:"
echo "   composer require laravel/reverb"
echo "   php artisan reverb:install"
echo ""
echo "3. Para iniciar o servidor Reverb:"
echo "   php artisan reverb:start"
echo ""
echo "4. Acesse a documentação completa em:"
echo "   TELEMEDICINA_README.md"
echo ""
echo "======================================"
