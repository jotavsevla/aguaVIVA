# Água Viva VRP

Sistema de roteamento de veículos (VRP) para distribuidora de água mineral.

## Status

🚧 Em desenvolvimento - FASE 1 (Fundação) concluída

Este repositório é o **protótipo público anterior** do case Água Viva apresentado no [portfólio](https://portfolio.jvapa.com.br/). O produto atual em Java/Python é privado e não deve ser confundido com este código. Aqui estão verificáveis a fundação Next.js/PostgreSQL, migrations, containers e o endpoint de status.

## Tecnologias

- Next.js 16 (Pages Router)
- React 19
- PostgreSQL 16
- Jest
- Docker Compose
- node-pg-migrate
- bcrypt

## Requisitos

- Node.js v22.12.0 (veja `.nvmrc`)
- npm
- Docker e Docker Compose
- PostgreSQL 16 (via Docker)

## Instalação

```bash
# Clona o repositório
git clone https://github.com/jotavsevla/aguaVIVA.git
cd aguaVIVA

# Usa a versão correta do Node
nvm install
nvm use

# Instala dependências
npm install

# Cria arquivo .env a partir do exemplo
cp .env.example .env

# Inicia os containers do PostgreSQL
docker-compose up -d

# Aguarda o banco inicializar e roda as migrations
npm run migrate:up
```

## Scripts

```bash
# Desenvolvimento
npm run dev              # servidor de desenvolvimento (http://localhost:3000)
npm run build            # build de produção
npm run start            # inicia produção

# Testes
npm test                 # roda testes
npm run test:watch       # testes em modo watch

# Formatação
npm run lint:check       # verifica formatação
npm run lint:fix         # corrige formatação

# Banco de dados
npm run migrate:up       # aplica migrations
npm run migrate:down     # reverte última migration
npm run migrate:create   # cria nova migration
```

## Estrutura do Projeto

```
agua-viva/
├── Docs/                    # Documentação do projeto
│   ├── docs/                # Regras de negócio, diagrama ER, queries
│   └── database/            # Migrations originais (referência)
├── infra/                   # Infraestrutura
│   ├── database.js          # Pool PostgreSQL (pg)
│   └── migrations/          # Migrations SQL (001-008)
├── models/                  # Models com regras de negócio (a criar)
├── pages/                   # Rotas e páginas (Next.js Pages Router)
│   ├── api/v1/status/       # Health check endpoint
│   └── index.js             # Página inicial
├── tests/                   # Testes
│   ├── integration/         # Testes de integração (API)
│   ├── unit/                # Testes unitários (models)
│   └── orchestrator.js      # Setup/teardown para testes
├── public/                  # Arquivos estáticos
├── styles/                  # CSS
├── docker-compose.yml       # PostgreSQL dev + test
├── .env.example             # Variáveis de ambiente
├── jest.config.js           # Configuração do Jest
└── jsconfig.json            # Path mapping
```

## Roadmap

### FASE 1 — FUNDAÇÃO ✅

- [x] Setup inicial (Next.js, Jest, Prettier)
- [x] Configuração do banco de dados (PostgreSQL + Docker)
- [x] infra/database.js (pool pg)
- [x] Migrations (8 arquivos SQL)
- [x] tests/orchestrator.js
- [x] GET /api/v1/status (health check)

### FASE 2 — AUTENTICAÇÃO (próximo)

- [ ] models/password.js + testes
- [ ] models/user.js + testes
- [ ] models/session.js + testes
- [ ] POST /api/v1/users
- [ ] POST /api/v1/sessions (login)
- [ ] DELETE /api/v1/sessions (logout)

### FASE 3 — DOMÍNIO

- [ ] models/cliente.js + testes
- [ ] models/pedido.js + testes
- [ ] models/vale.js + testes
- [ ] models/rota.js + testes
- [ ] Endpoints CRUD

### FASE 4 — SOLVER

- [ ] FastAPI + OR-Tools
- [ ] Integração com Next.js

## Arquitetura e fluxo de recursos

```text
requisição HTTP
  → API Route (Pages Router)
    → infra/database.js
      → pool PostgreSQL
        → query parametrizada
```

O pool é criado sob demanda e reutilizado no processo. Consultas comuns devolvem a conexão automaticamente; quando `getClient()` for usado para transações, o chamador deverá sempre executar `client.release()` em um bloco `finally`, caso contrário o pool pode se esgotar.

## Auditoria técnica

### Pontos positivos

- o endpoint consulta o banco com parâmetro (`$1`) onde há dado variável;
- desenvolvimento e testes usam bancos separados;
- migrations são a fonte versionada do schema;
- o pool pode ser encerrado explicitamente por `database.end()`;
- há teste de integração do contrato básico de `/api/v1/status`.

### Limitações atuais

- o projeto ainda não implementa autenticação, domínio de pedidos/rotas nem solver; são itens de roadmap;
- o endpoint não restringe método HTTP e ainda não padroniza resposta para indisponibilidade do banco;
- em teste, a contagem de conexões usa `POSTGRES_DB`, embora o pool use `POSTGRES_DB_TEST`; isso pode medir o banco errado;
- `clearDatabase()` remove todo o schema `public`; só deve rodar em banco de teste isolado;
- o teste de integração pressupõe servidor em `localhost:3000`, mas o comando `npm test` não o inicia;
- `rejectUnauthorized: false` em produção facilita conexão com alguns provedores, porém desativa a verificação completa do certificado. Prefira uma CA confiável quando disponível;
- `npm run lint:check` verifica formatação, não regras semânticas de lint.
- uma instalação limpa emitiu avisos de dependências transitivas obsoletas, incluindo `inflight` (marcada pelo mantenedor como propensa a vazamento) e versões antigas de `glob`. Use `npm ls inflight glob` para localizar a cadeia e atualize dependências com testes, sem presumir que alterar somente o lockfile resolve compatibilidade.

### Memória e concorrência

Node.js e `pg` gerenciam objetos por garbage collection, portanto não há `malloc/free` manual. Os recursos que exigem atenção são conexões e processos:

- conexões obtidas por `pool.connect()` precisam de `release()`;
- o pool deve permanecer singleton para evitar multiplicar até 20 conexões por instância;
- scripts de teste que executam processos filhos devem propagar falhas e encerrar recursos;
- limites de pool e timeouts precisam acompanhar o limite real do PostgreSQL e o número de instâncias da aplicação.

## Verificação recomendada

```bash
docker compose up -d
npm install
npm run migrate:up
npm run dev
# em outro terminal
npm test
npm run lint:check
npm run build
```

Na auditoria, o teste unitário e o build de produção passaram. A checagem global do Prettier encontrou formatação pendente em documentação e em um teste de integração preexistentes.

## Próximos passos técnicos

1. corrigir a variável de banco usada pelo status em ambiente de teste;
2. criar um script único que suba servidor, banco e integração;
3. validar métodos HTTP e padronizar erros sem expor detalhes internos;
4. cobrir rollback de migration, banco indisponível e exaustão do pool;
5. implementar autenticação com testes antes de divulgar as fases seguintes como concluídas.

## Licença

MIT
