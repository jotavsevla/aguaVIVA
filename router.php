<?php
// aguaVIVA/router.php
// Use com: php -S localhost:8000 router.php

// Obtenha o caminho da URI requisitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Defina constante para identificar ambiente
define('ENVIRONMENT', 'development');

// Verificação estrita para phpMyAdmin
if (strpos($uri, '/phpmyadmin') === 0 || strpos($uri, '/public/phpmyadmin') === 0) {
    $phpMyAdminPath = __DIR__ . '/public/phpmyadmin';

    // Verificar se é uma requisição para router.php do phpMyAdmin
    if (strpos($uri, 'router.php') !== false) {
        // Verificar se o arquivo existe
        $phpMyAdminRouterPath = $phpMyAdminPath . '/router.php';
        if (file_exists($phpMyAdminRouterPath)) {
            // Incluir diretamente o router.php do phpMyAdmin
            error_log("Requisição para router.php do phpMyAdmin - incluindo diretamente");
            include $phpMyAdminRouterPath;
            return true;
        }
    }

    // Para outras requisições phpMyAdmin, deixar o servidor processar normalmente
    error_log("Requisição phpMyAdmin detectada: $uri - passando diretamente");
    return false;
}

// Resto do código permanece igual
// Permitir acesso direto ao arquivo logout-temp.php
if ($uri === '/logout-temp.php') {
    include __DIR__ . '/logout-temp.php';
    return true;
}

// Requisições para arquivos estáticos na pasta public são servidas diretamente
if (file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Verifica se é uma requisição de arquivo de imagem para assets
if (preg_match('~\.(jpe?g|png|gif|css|js)$~i', $uri)) {
    $file_path = __DIR__ . $uri;
    if (file_exists($file_path)) {
        // Define o tipo MIME adequado
        $ext = pathinfo($uri, PATHINFO_EXTENSION);
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }

        readfile($file_path);
        return true;
    }
}

// Caso especial para o URI raiz - redirecionar para o front controller
if ($uri === '/') {
    require_once __DIR__ . '/public/index.php';
    return true;
}

// Simular o comportamento do .htaccess na raiz:
// Redirecionar todas as outras requisições como se fossem para public/
require_once __DIR__ . '/public/index.php';