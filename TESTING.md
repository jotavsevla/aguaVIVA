# Guia de Testes - Água Viva VRP

## Setup Inicial

1. **Certifique-se que o Docker Desktop está rodando**

2. **Suba os containers do PostgreSQL:**
```bash
docker-compose up -d
```

3. **Aplique as migrations:**
```bash
npm run migrate:up
```

4. **Inicie o servidor:**
```bash
npm run dev
```

## Testes Básicos

### Verificar banco de dados
```bash
# Ver containers
docker-compose ps

# Ver tabelas criadas
docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -c "\dt"

# Ver views
docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -c "\dv"

# Ver configurações
docker exec agua-viva-postgres psql -U postgres -d agua_viva_dev -c "SELECT * FROM configuracoes;"
```

### Testar endpoint
```bash
# Via curl
curl http://localhost:3000/api/v1/status

# Via navegador
open http://localhost:3000/api/v1/status
```

Resposta esperada:
```json
{
  "updated_at": "2026-02-07T04:15:23.456Z",
  "dependencies": {
    "database": {
      "version": "16.x",
      "max_connections": 100,
      "opened_connections": 1
    }
  }
}
```

### Rodar testes automatizados
```bash
npm test
```

## Comandos Úteis

```bash
# Parar containers
docker-compose down

# Reiniciar containers
docker-compose restart

# Ver logs
docker-compose logs -f

# Reverter migrations
npm run migrate:down
```

## Troubleshooting

**Docker não conecta:** Abra o Docker Desktop

**Porta em uso:** Algum serviço está usando 5432 ou 5433

**Tabelas não existem:** Execute `npm run migrate:up`

**Testes falham:** Certifique-se que `npm run dev` está rodando
