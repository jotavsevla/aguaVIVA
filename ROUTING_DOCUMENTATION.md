# Documentação do Sistema de Roteamento

## 📋 Visão Geral

O sistema aguaVIVA possui **dois** arquivos de roteamento que trabalham em diferentes contextos:

### 1. `/router.php` (Servidor Embutido PHP)
- **Uso:** `php -S localhost:8000 router.php`
- **Contexto:** Desenvolvimento local com servidor embutido PHP
- **Status:** ⚠️ **NÃO UTILIZADO atualmente** (sistema roda em Docker)

### 2. `/public/index.php` (Front Controller)
- **Uso:** Automático via Nginx/Apache
- **Contexto:** Docker (atual) e Produção
- **Status:** ✅ **EM USO**

## 🔄 Como o Roteamento Funciona Atualmente

### Fluxo de Requisição no Docker:

```
Requisição HTTP
    ↓
Nginx (porta 8080)
    ↓
/public/index.php (Front Controller)
    ↓
src/Core/Router.php (Router Class)
    ↓
Controller@method ou Closure
    ↓
View/Response
```

### Configuração Nginx

```nginx
# docker/nginx/default.conf
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**O que isso faz:** Todas as URLs que não correspondem a arquivos físicos são redirecionadas para `index.php`.

## 🎯 Detecção de Ambiente

### Arquivo: `/public/index.php` (linhas 7-28)

```php
function detectEnvironment() {
    // Detecta Docker pela presença do docker-compose.yml
    $dockerComposeExists = file_exists(dirname(__DIR__) . '/docker-compose.yml');

    if (php_sapi_name() === 'cli-server' ||
        getenv('DOCKER_ENV') === 'development' ||
        $dockerComposeExists) {
        define('ENVIRONMENT', 'development');
        define('BASE_URL', '');  // SEM PREFIXO
        return 'development';
    }

    // Produção (Apache com subdiretório)
    define('ENVIRONMENT', 'production');
    define('BASE_URL', '/aguaVIVA');  // COM PREFIXO
    return 'production';
}
```

### Como Funciona:

| Ambiente | Detecção | BASE_URL | Exemplo URL |
|----------|----------|----------|-------------|
| **Docker** (atual) | Existe `docker-compose.yml` | `""` (vazio) | `/login` |
| **Servidor Embutido** | `php_sapi_name() === 'cli-server'` | `""` (vazio) | `/login` |
| **Produção Apache** | Nenhuma das anteriores | `/aguaVIVA` | `/aguaVIVA/login` |

## 🛣️ Rotas Registradas

### Arquivo: `/public/index.php` (linhas 60-110)

#### Autenticação
```php
$router->get('/login', 'app\Controllers\AuthController@showLoginForm');
$router->post('/login', 'app\Controllers\AuthController@processLogin');
$router->get('/logout', 'app\Controllers\AuthController@logout');
```

#### Página Inicial
```php
$router->get('/', function() {
    header('Location: /login');
    exit;
});
```

#### Dashboards
```php
$router->get('/admin', function() {
    include BASE_PATH . '/resources/views/admin/dashboard.php';
});

$router->get('/supervisor', function() {
    include BASE_PATH . '/resources/views/supervisor/dashboard.php';
});
```

#### CRUD Clientes
```php
$router->get('/admin/clientes', 'app\Controllers\ClienteController@index');
$router->get('/admin/clientes/create', 'app\Controllers\ClienteController@create');
$router->post('/admin/clientes/store', 'app\Controllers\ClienteController@store');
$router->get('/admin/clientes/edit/{id}', 'app\Controllers\ClienteController@edit');
$router->post('/admin/clientes/update/{id}', 'app\Controllers\ClienteController@update');
$router->post('/admin/clientes/delete/{id}', 'app\Controllers\ClienteController@delete');
```

#### CRUD Entregadores
```php
$router->get('/admin/entregadores', 'app\Controllers\EntregadorController@index');
$router->get('/admin/entregadores/create', 'app\Controllers\EntregadorController@create');
$router->post('/admin/entregadores/store', 'app\Controllers\EntregadorController@store');
$router->get('/admin/entregadores/edit/{id}', 'app\Controllers\EntregadorController@edit');
$router->post('/admin/entregadores/update/{id}', 'app\Controllers\EntregadorController@update');
$router->post('/admin/entregadores/delete/{id}', 'app\Controllers\EntregadorController@delete');
```

#### Gerenciamento de Entregas
```php
$router->get('/admin/entregas', 'app\Controllers\EntregaController@index');
$router->get('/admin/entregas/create', 'app\Controllers\EntregaController@create');
$router->post('/admin/entregas/store', 'app\Controllers\EntregaController@store');
$router->post('/admin/entregas/{id}/atribuir', 'app\Controllers\EntregaController@atribuir');
$router->post('/admin/entregas/{id}/atribuir-auto', 'app\Controllers\EntregaController@atribuirAutomatico');
$router->post('/admin/entregas/{id}/iniciar', 'app\Controllers\EntregaController@iniciar');
$router->post('/admin/entregas/{id}/concluir', 'app\Controllers\EntregaController@concluir');
$router->post('/admin/entregas/{id}/cancelar', 'app\Controllers\EntregaController@cancelar');
$router->post('/admin/entregas/{id}/delete', 'app\Controllers\EntregaController@delete');
```

#### API Endpoints
```php
$router->post('/api/entregadores/{id}/status', 'app\Controllers\EntregadorController@updateStatus');
$router->get('/api/entregadores/disponiveis', 'app\Controllers\EntregadorController@apiDisponiveis');
$router->get('/api/entregas/pendentes', 'app\Controllers\EntregaController@apiPendentes');
```

## 🔧 Mudanças Recentes (Correção do Loop de Redirecionamento)

### Problema Corrigido

**Antes:** Sistema detectava ambiente Docker como "produção" e adicionava `/aguaVIVA` nas URLs.

**Depois:** Sistema detecta corretamente como "development" usando presença do `docker-compose.yml`.

### Arquivos Modificados:

1. **`/public/index.php`**
   - Função `detectEnvironment()` atualizada
   - Agora detecta Docker corretamente
   - Define `BASE_URL = ''` para Docker

2. **`/app/Controllers/AuthController.php`**
   - Construtor atualizado para respeitar `BASE_URL`
   - Redirecionamento inteligente para dashboards
   - Evita loop `/login` → `/` → `/login`

3. **`/src/Core/Router.php`**
   - Mantém lógica de remoção de `BASE_URL` da URI
   - Funciona tanto em development quanto production

## 📂 Classe Router (`src/Core/Router.php`)

### Métodos Principais:

```php
// Registrar rota GET
public function get($uri, $handler)

// Registrar rota POST
public function post($uri, $handler)

// Executar handler (Controller@method ou Closure)
private function executeHandler($handler)

// Processar requisição e encontrar rota correspondente
public function dispatch()
```

### Como Funciona o `dispatch()`:

1. Obtém URI e método HTTP
2. Remove query string (`?param=value`)
3. Remove `BASE_URL` se presente (produção)
4. Normaliza URI (`/path/`)
5. Procura rota registrada
6. Executa handler ou retorna 404

### Exemplo de Execução:

```php
// Requisição: GET /admin/clientes
$router->dispatch();
    ↓
URI normalizada: /admin/clientes
    ↓
Handler encontrado: app\Controllers\ClienteController@index
    ↓
Executa: (new ClienteController())->index()
```

## 🗑️ Arquivos Temporários/Auxiliares na Raiz

| Arquivo | Propósito | Status | Ação Recomendada |
|---------|-----------|--------|------------------|
| `router.php` | Servidor embutido PHP | ⚠️ Não usado | **Manter** (útil para desenvolvimento sem Docker) |
| `generate_password.php` | Gerar hash de senhas | ✅ Concluído | **PODE REMOVER** |
| `seed_demo_data.php` | Popular banco com dados demo | ✅ Executado | **Manter** (útil para recriar dados) |
| `test_system.php` | Testar conexões e models | ✅ Útil | **Manter** (diagnóstico) |
| `clear_sessions.php` | Limpar sessões PHP | ✅ Útil | **Manter** (manutenção) |

## 🎯 Fluxo de Autenticação

### Login com Sucesso:

```
POST /login
    ↓
AuthController@processLogin
    ↓
AuthService->authenticate()
    ↓
Define $_SESSION['userlogged'] = true
Define $_SESSION['lvl'] = 'admin'
    ↓
Redireciona para /admin
```

### Usuário Já Logado Tenta Acessar /login:

```
GET /login
    ↓
AuthController@showLoginForm
    ↓
Verifica: $_SESSION['userlogged'] === true
    ↓
Redireciona para /admin (não para /)
```

**Isso evita o loop que estava acontecendo antes!**

## 📝 Arquivos de Rotas Deletados

Estes arquivos estavam no git status como deletados:

- ~~`routes/web.php`~~ (substituído por rotas em `/public/index.php`)
- ~~`routes/api.php`~~ (API endpoints agora em `/public/index.php`)

**Motivo:** Sistema foi simplificado para usar um único arquivo de rotas.

## 🔍 Como Debugar Rotas

### Ver Logs do Router:

```bash
docker logs -f aguaviva_php | grep "Router:"
```

### Output Esperado:

```
Router: Processando rota GET /login
Router: Processando rota POST /login
Router: Processando rota GET /admin
```

### Se Rota Não Encontrada:

```
Router: Rota não encontrada para GET /alguma-rota
Rotas disponíveis: Array(...)
```

## 🏗️ Arquitetura de Roteamento

```
┌─────────────────────────────────────┐
│         Nginx (Docker)              │
│     ou Apache (Produção)            │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│      /public/index.php              │
│  (Front Controller)                 │
│  - Detecta ambiente                 │
│  - Define BASE_URL                  │
│  - Inicia sessão                    │
│  - Carrega autoloader               │
│  - Registra rotas                   │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│    src/Core/Router.php              │
│  (Router Class)                     │
│  - Normaliza URI                    │
│  - Remove BASE_URL                  │
│  - Encontra handler                 │
│  - Executa controller/closure       │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│      Controller                     │
│  - Processa lógica                  │
│  - Interage com Models              │
│  - Renderiza Views                  │
└─────────────────────────────────────┘
```

## ✅ Checklist de Funcionamento

- [x] Detecção de ambiente funcionando (development)
- [x] BASE_URL vazio em Docker
- [x] Rotas sem prefixo funcionando
- [x] Login redirecionando corretamente
- [x] Dashboard admin acessível
- [x] CRUD de clientes funcionando
- [x] CRUD de entregadores funcionando
- [x] Gerenciamento de entregas funcionando
- [x] API endpoints registrados
- [x] Página 404 funcionando

## 🚀 Comandos Úteis

```bash
# Ver todas as rotas registradas
docker exec aguaviva_php php -r "
require '/var/www/html/public/index.php';
// Rotas já impressas no log
"

# Testar uma rota específica
curl -I http://localhost:8080/login

# Limpar sessões
docker exec aguaviva_php php /var/www/html/clear_sessions.php

# Reiniciar containers
docker restart aguaviva_php aguaviva_nginx
```

## 📚 Referências

- [public/index.php](public/index.php) - Front Controller e registro de rotas
- [src/Core/Router.php](src/Core/Router.php) - Classe Router
- [app/Controllers/AuthController.php](app/Controllers/AuthController.php) - Autenticação
- [docker/nginx/default.conf](docker/nginx/default.conf) - Configuração Nginx
