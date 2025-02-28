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

// Process the request
$router->dispatch();