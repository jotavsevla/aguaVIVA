# Correção do Loop de Redirecionamento

## 🐛 Problema Identificado

Erro: `ERR_TOO_MANY_REDIRECTS` ao acessar a página de login

### Causas do Problema:

1. **Detecção incorreta do ambiente**
   - Sistema estava detectando como "produção" mesmo rodando no Docker
   - Estava adicionando `/aguaVIVA` nas URLs, mas Nginx esperava rotas sem prefixo

2. **Loop de redirecionamento**
   - Quando usuário logado acessava `/login`, era redirecionado para `/`
   - A rota `/` redirecionava de volta para `/login`
   - Resultado: loop infinito

3. **Sessões antigas**
   - Sessões criadas antes das correções estavam causando problemas

## ✅ Correções Aplicadas

### 1. Detecção de Ambiente Corrigida ([public/index.php](public/index.php#L7-L28))

```php
function detectEnvironment() {
    // Detecta servidor embutido PHP ou Docker (desenvolvimento)
    $dockerComposeExists = file_exists(dirname(__DIR__) . '/docker-compose.yml');

    if (php_sapi_name() === 'cli-server' ||
        getenv('DOCKER_ENV') === 'development' ||
        $dockerComposeExists) {
        define('ENVIRONMENT', 'development');
        define('BASE_URL', '');  // SEM PREFIXO para Docker
        return 'development';
    }

    // Ambiente de produção (Apache)
    define('ENVIRONMENT', 'production');
    define('BASE_URL', '/aguaVIVA');
    return 'production';
}
```

**Mudança:** Agora detecta se o arquivo `docker-compose.yml` existe para identificar ambiente Docker.

### 2. AuthController Atualizado ([app/Controllers/AuthController.php](app/Controllers/AuthController.php#L11-L29))

```php
public function __construct() {
    $this->authService = new AuthService();

    // Usa BASE_URL se definido, senão detecta
    if (defined('BASE_URL')) {
        $this->basePath = BASE_URL;
    } else {
        $dockerComposeExists = file_exists(dirname(__DIR__, 2) . '/docker-compose.yml');
        if (php_sapi_name() === 'cli-server' || $dockerComposeExists) {
            $this->basePath = '';
        } else {
            $this->basePath = '/aguaVIVA';
        }
    }
}
```

**Mudança:** AuthController agora respeita a constante `BASE_URL` definida no index.php.

### 3. Redirecionamento Inteligente ([app/Controllers/AuthController.php](app/Controllers/AuthController.php#L31-L62))

```php
public function showLoginForm() {
    // Se já está logado, redireciona para dashboard correto
    if (isset($_SESSION['userlogged']) && $_SESSION['userlogged'] === true) {
        // Redireciona baseado no nível de acesso
        $redirectPath = '/admin'; // padrão
        if (isset($_SESSION['lvl'])) {
            switch ($_SESSION['lvl']) {
                case 'admin':
                    $redirectPath = '/admin';
                    break;
                case 'supervisor':
                    $redirectPath = '/supervisor';
                    break;
            }
        }
        $this->redirect($redirectPath);
        exit;
    }

    // Renderiza formulário de login...
}
```

**Mudança:** Usuário logado é redirecionado para `/admin` ou `/supervisor` diretamente, não para `/`.

### 4. Sessões Limpas

Criado script [clear_sessions.php](clear_sessions.php) para remover sessões antigas:

```bash
docker exec aguaviva_php php /var/www/html/clear_sessions.php
```

### 5. Containers Reiniciados

```bash
docker restart aguaviva_php aguaviva_nginx
```

## 🧪 Como Testar

### Passo 1: Limpe os Cookies do Navegador

**Chrome/Edge:**
1. Abra DevTools (F12)
2. Application → Cookies → Selecione o domínio
3. Clique com botão direito → Clear

**Firefox:**
1. Abra DevTools (F12)
2. Storage → Cookies → Selecione o domínio
3. Clique com botão direito → Delete All

### Passo 2: Acesse o Sistema

```
URL: http://localhost:8080/login
ou
URL: https://glorious-pancake-v4r5776qwxqfwvgr-8080.app.github.dev/login
```

### Passo 3: Faça Login

```
Usuário: admin
Senha: admin123
```

### Passo 4: Verifique o Dashboard

Após login, você deve ser redirecionado para:
```
http://localhost:8080/admin
```

## 🔍 Verificação de Logs

Se ainda houver problemas, verifique os logs:

```bash
# Ver últimas 50 linhas de log
docker logs aguaviva_php --tail 50

# Acompanhar logs em tempo real
docker logs -f aguaviva_php
```

Procure por:
- ✅ `AuthController construído com caminho base: ` (deve estar vazio)
- ✅ `Redirecionando para: /admin` (sem /aguaVIVA)
- ❌ `Redirecionando para: /aguaVIVA/...` (não deve aparecer)

## 📊 URLs Corretas Agora

| Rota | URL Esperada | Status |
|------|-------------|--------|
| Login | `/login` | ✅ |
| Dashboard Admin | `/admin` | ✅ |
| Dashboard Supervisor | `/supervisor` | ✅ |
| Clientes | `/admin/clientes` | ✅ |
| Entregadores | `/admin/entregadores` | ✅ |
| Entregas | `/admin/entregas` | ✅ |
| Logout | `/logout` | ✅ |

## 🛠️ Scripts Úteis

### Limpar Sessões
```bash
docker exec aguaviva_php php /var/www/html/clear_sessions.php
```

### Testar Ambiente
```bash
docker exec aguaviva_php php -r "
require '/var/www/html/public/index.php';
echo 'Environment: ' . ENVIRONMENT . PHP_EOL;
echo 'BASE_URL: ' . BASE_URL . PHP_EOL;
"
```

### Reiniciar Containers
```bash
docker restart aguaviva_php aguaviva_nginx
```

## ⚠️ Notas Importantes

1. **Sempre limpe os cookies** após fazer alterações nas rotas
2. **GitHub Codespaces** pode ter cache agressivo, use Ctrl+Shift+R para recarregar
3. Se o problema persistir, feche completamente o navegador e abra novamente
4. O sistema agora detecta automaticamente se está em Docker (desenvolvimento)

## 🎯 Status

**Status: ✅ CORRIGIDO**

- [x] Detecção de ambiente corrigida
- [x] Loop de redirecionamento resolvido
- [x] AuthController atualizado
- [x] Sessões antigas limpas
- [x] Containers reiniciados
- [ ] **Aguardando teste do usuário após limpar cookies**
