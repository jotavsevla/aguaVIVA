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

    // Detecta servidor embutido PHP ou Docker (desenvolvimento)
    $dockerComposeExists = file_exists(dirname(__DIR__) . '/docker-compose.yml');

    if (php_sapi_name() === 'cli-server' ||
        getenv('DOCKER_ENV') === 'development' ||
        $dockerComposeExists) {
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
$router = new src\Core\Router();

// Define routes
$router->get('/login', 'app\Controllers\AuthController@showLoginForm');
$router->post('/login', 'app\Controllers\AuthController@processLogin');
$router->get('/logout', 'app\Controllers\AuthController@logout');

$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Dashboard admin
$router->get('/admin', function() {
    include BASE_PATH . '/resources/views/admin/dashboard.php';
});

// Dashboard supervisor
$router->get('/supervisor', function() {
    include BASE_PATH . '/resources/views/supervisor/dashboard.php';
});

// Rotas para clientes (admin)
$router->get('/admin/clientes', 'app\Controllers\ClienteController@index');
$router->get('/admin/clientes/create', 'app\Controllers\ClienteController@create');
$router->post('/admin/clientes/store', 'app\Controllers\ClienteController@store');
$router->get('/admin/clientes/edit/{id}', 'app\Controllers\ClienteController@edit');
$router->post('/admin/clientes/update/{id}', 'app\Controllers\ClienteController@update');
$router->post('/admin/clientes/delete/{id}', 'app\Controllers\ClienteController@delete');

// Rotas para entregadores (admin)
$router->get('/admin/entregadores', 'app\Controllers\EntregadorController@index');
$router->get('/admin/entregadores/create', 'app\Controllers\EntregadorController@create');
$router->post('/admin/entregadores/store', 'app\Controllers\EntregadorController@store');
$router->get('/admin/entregadores/edit/{id}', 'app\Controllers\EntregadorController@edit');
$router->post('/admin/entregadores/update/{id}', 'app\Controllers\EntregadorController@update');
$router->post('/admin/entregadores/delete/{id}', 'app\Controllers\EntregadorController@delete');

// Rotas para entregas (admin)
$router->get('/admin/entregas', 'app\Controllers\EntregaController@index');
$router->get('/admin/entregas/create', 'app\Controllers\EntregaController@create');
$router->post('/admin/entregas/store', 'app\Controllers\EntregaController@store');
$router->post('/admin/entregas/{id}/atribuir', 'app\Controllers\EntregaController@atribuir');
$router->post('/admin/entregas/{id}/atribuir-auto', 'app\Controllers\EntregaController@atribuirAutomatico');
$router->post('/admin/entregas/{id}/iniciar', 'app\Controllers\EntregaController@iniciar');
$router->post('/admin/entregas/{id}/concluir', 'app\Controllers\EntregaController@concluir');
$router->post('/admin/entregas/{id}/cancelar', 'app\Controllers\EntregaController@cancelar');
$router->post('/admin/entregas/{id}/delete', 'app\Controllers\EntregaController@delete');

// API endpoints
$router->post('/api/entregadores/{id}/status', 'app\Controllers\EntregadorController@updateStatus');
$router->get('/api/entregadores/disponiveis', 'app\Controllers\EntregadorController@apiDisponiveis');
$router->get('/api/entregas/pendentes', 'app\Controllers\EntregaController@apiPendentes');

// Process the request
$router->dispatch();