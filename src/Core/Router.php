<?php
// src/Core/Router.php
namespace Src\Core;

class Router
{
    private $routes = [];

    // Register a GET route
    public function get($uri, $handler)
    {
        $this->routes['GET'][$uri] = $handler;
    }

    // Register a POST route
    public function post($uri, $handler)
    {
        $this->routes['POST'][$uri] = $handler;
    }

    private function executeHandler($handler)
    {
        // Handle Controller@method format
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func([$controllerInstance, $method]);
                }
            }
            throw new \Exception("Handler {$handler} not found");
        }

        // Handle closure
        if (is_callable($handler)) {
            return call_user_func($handler);
        }
    }

    public function dispatch()
    {
        // Obtém URI e método
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        // Ignora requisições phpMyAdmin
        if (strpos($uri, '/phpmyadmin') === 0) {
            return;
        }

        // Remove a query string se presente
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove BASE_URL do URI se estiver em ambiente de produção
        if (defined('BASE_URL') && !empty(BASE_URL) && strpos($uri, BASE_URL) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
        }

        // Normaliza URI
        $uri = '/' . trim($uri, '/');

        // Log para depuração
        error_log("Router: Processando rota {$method} {$uri}");

        // Processa rota existente
        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            $this->executeHandler($handler);
            return;
        }

        // Log rota não encontrada
        error_log("Router: Rota não encontrada para {$method} {$uri}");
        error_log("Rotas disponíveis: " . print_r($this->routes, true));

        // 404 Not Found
        http_response_code(404);
        include BASE_PATH . '/resources/views/errors/404.php';
    }
}