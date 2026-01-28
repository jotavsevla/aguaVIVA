# Água VIVA

Sistema de gerenciamento para distribuidora de água mineral.

## Status

🚧 Em desenvolvimento

## Tecnologias

- Next.js 16
- React 19
- PostgreSQL (em breve)
- Jest

## Requisitos

- Node.js v22.12.0 (veja `.nvmrc`)
- npm

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
```

## Scripts

```bash
npm run dev         # servidor de desenvolvimento
npm run build       # build de produção
npm run start       # inicia produção
npm test            # roda testes
npm run test:watch  # testes em modo watch
npm run lint:check  # verifica formatação
npm run lint:fix    # corrige formatação
```

## Estrutura do Projeto

```
agua-viva/
├── infra/              # Infraestrutura (banco, migrations)
├── models/             # Regras de negócio
├── pages/              # Rotas e páginas (Next.js)
│   └── api/            # API REST
├── tests/
│   ├── integration/    # Testes de integração
│   └── unit/           # Testes unitários
└── styles/             # CSS
```

## Roadmap

- [x] Setup inicial (Next.js, Jest, Prettier)
- [ ] Configuração do banco de dados
- [ ] Autenticação (JWT)
- [ ] CRUD de usuários
- [ ] CRUD de clientes
- [ ] CRUD de pedidos
- [ ] Dashboard

## Licença

MIT
