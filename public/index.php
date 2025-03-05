<?php
// public/index.php
/**
 * Detecta o ambiente atual e configura constantes relevantes
 * @return string O ambiente detectado ('development' ou 'production')
 */
function detectEnvironment() {
    // Verifica se a constante já foi definida (pelo router.php)
    if (defined('ENVIRONMENT')) {
        return ENVIRONMENT;
    }

    // Detecta servidor embutido PHP
    if (php_sapi_name() === 'cli-server') {
        define('ENVIRONMENT', 'development');
        define('BASE_URL', '');
        return 'development';
    }

    // Ambiente de produção (Apache)
    define('ENVIRONMENT', 'production');
    define('BASE_URL', '/aguaVIVA'); // Ajuste conforme necessário
    return 'production';
}

// Detecta ambiente e configura constantes
$environment = detectEnvironment();

// Enable errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Simple autoloader
spl_autoload_register(function($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic configuration
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Helpers/functions.php';

// Create router
$router = new Src\Core\Router();

// Define routes
$router->get('/login', 'App\Controllers\AuthController@showLoginForm');
$router->post('/login', 'App\Controllers\AuthController@processLogin');
$router->get('/logout', 'App\Controllers\AuthController@logout');
$router->get('/logout-temp.php', function() {
    include BASE_PATH . '/logout-temp.php';
});
$router->get('/', function() {
    echo "Welcome to the home page!";
});
// Rota para o dashboard do admin
$router->get('/admin', function() {
    include BASE_PATH . '/resources/views/admin/dashboard.php';
});

// Rota para o dashboard do supervisor
$router->get('/supervisor', function() {
    include BASE_PATH . '/resources/views/supervisor/dashboard.php';
});

// Rotas para clientes (admin)
$router->get('/admin/clientes', function() {
    include BASE_PATH . '/resources/views/admin/clientes/index.php';
});

$router->get('/admin/clientes/create', function() {
    include BASE_PATH . '/resources/views/admin/clientes/create.php';
});

$router->get('/admin/clientes/edit', function() {
    include BASE_PATH . '/resources/views/admin/clientes/edit.php';
});

// Rotas para pedidos (admin)
$router->get('/admin/pedidos', function() {
    include BASE_PATH . '/resources/views/admin/pedidos/index.php';
});

$router->get('/admin/pedidos/create', function() {
    include BASE_PATH . '/resources/views/admin/pedidos/create.php';
});

$router->get('/admin/pedidos/edit', function() {
    include BASE_PATH . '/resources/views/admin/pedidos/edit.php';
});

// Rotas para usuários (supervisor)
$router->get('/supervisor/users', function() {
    include BASE_PATH . '/resources/views/supervisor/users/index.php';
});
// Process the request
$router->dispatch();