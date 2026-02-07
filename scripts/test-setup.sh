#!/bin/bash

# Script para testar o setup da FASE 1 do Agua Viva VRP

set -e

echo "🧪 Testando setup do Agua Viva VRP - FASE 1"
echo "=============================================="
echo ""

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Verificar se Docker está rodando
echo "1️⃣  Verificando Docker..."
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker não está rodando${NC}"
    echo "   Abra o Docker Desktop e tente novamente"
    exit 1
fi
echo -e "${GREEN}✅ Docker está rodando${NC}"
echo ""

# 2. Subir containers
echo "2️⃣  Subindo containers do PostgreSQL..."
docker-compose up -d
echo -e "${GREEN}✅ Containers iniciados${NC}"
echo ""

# 3. Aguardar PostgreSQL estar pronto
echo "3️⃣  Aguardando PostgreSQL ficar pronto..."
for i in {1..30}; do
    if docker exec agua-viva-postgres pg_isready -U postgres > /dev/null 2>&1; then
        echo -e "${GREEN}✅ PostgreSQL está pronto${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}❌ PostgreSQL não ficou pronto em 30 segundos${NC}"
        exit 1
    fi
    echo -n "."
    sleep 1
done
echo ""

# 4. Rodar migrations
echo "4️⃣  Rodando migrations..."
npm run migrate:up
echo -e "${GREEN}✅ Migrations aplicadas${NC}"
echo ""

# 5. Verificar tabelas criadas
echo "5️⃣  Verificando tabelas criadas..."
TABLES=$(docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE';")
VIEWS=$(docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -t -c "SELECT COUNT(*) FROM information_schema.views WHERE table_schema = 'public';")

echo "   Tabelas: $TABLES"
echo "   Views: $VIEWS"

if [ $TABLES -ge 8 ]; then
    echo -e "${GREEN}✅ Tabelas criadas com sucesso${NC}"
else
    echo -e "${RED}❌ Esperado 9+ tabelas, encontrado: $TABLES${NC}"
    exit 1
fi
echo ""

# 6. Listar estrutura do banco
echo "6️⃣  Estrutura do banco:"
docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -c "\dt"
echo ""

# 7. Instruções finais
echo "=============================================="
echo -e "${GREEN}🎉 Setup concluído com sucesso!${NC}"
echo ""
echo "Próximos passos:"
echo "  1. Em um terminal, rode: ${YELLOW}npm run dev${NC}"
echo "  2. Teste o endpoint: ${YELLOW}curl http://localhost:3000/api/v1/status${NC}"
echo "  3. Ou abra no navegador: ${YELLOW}http://localhost:3000/api/v1/status${NC}"
echo "  4. Rode os testes: ${YELLOW}npm test${NC}"
echo ""
echo "Comandos úteis:"
echo "  ${YELLOW}docker-compose logs -f${NC}     # Ver logs dos containers"
echo "  ${YELLOW}docker-compose down${NC}        # Parar containers"
echo "  ${YELLOW}npm run migrate:down${NC}       # Reverter última migration"
echo ""
