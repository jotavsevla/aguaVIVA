# Água Viva VRP

Sistema de roteamento de veículos (VRP) para distribuidora de água mineral.

## Status

🚧 Em desenvolvimento - FASE 1 (Fundação) concluída

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
git clone https://github.com/seu-usuario/agua-viva.git
cd agua-viva

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

## Licença

MIT
