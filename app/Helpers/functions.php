<?php

/**
 * Função para verificar se o usuário está logado
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['userlogged']);
}

/**
 * Função para verificar se o usuário tem um papel específico
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['lvl']) && $_SESSION['lvl'] === $role;
}

/**
 * Função para verificar se o usuário tem uma permissão específica
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    if (!isset($_SESSION['lvl'])) return false;

    $userRole = $_SESSION['lvl'];

    $permissions = [
        'supervisor' => [
            'manage_users',
            'view_analytics',
            'generate_reports',
            'view_all_orders'
        ],
        'admin' => [
            'manage_orders',
            'manage_clients',
            'manage_deliveries',
            'view_own_orders'
        ],
        'user' => [
            'view_basic_info'
        ]
    ];

    return in_array($permission, $permissions[$userRole] ?? []);
}

/**
 * Retorna a URL base do aplicativo
 * @return string
 */
function baseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . $host;
}

/**
 * Função para exibir mensagens flash
 * @param string $type
 * @param string $message
 * @return void
 */
function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Função para obter mensagens flash
 * @param string $type
 * @return string|null
 */
function getFlash($type) {
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

/**
 * Função para formatar valores monetários
 * @param float $value
 * @return string
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Função para formatar datas
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Função para traduzir status
 * @param string $status
 * @return string
 */
function translateStatus($status) {
    $statusMap = [
        'pendente' => 'Pendente',
        'em_entrega' => 'Em Entrega',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
        'agendado' => 'Agendado'
    ];

    return $statusMap[$status] ?? $status;
}

/**
 * Gera URL para recursos estáticos
 * @param string $path Caminho relativo ao diretório de assets
 * @return string URL completa para o recurso
 */
function asset($path) {
    // Normaliza o caminho
    $path = ltrim($path, '/');

    // Detecta ambiente
    if (php_sapi_name() === 'cli-server') {
        // Servidor embutido - caminho direto para assets
        return '/public/assets/' . $path;
    } else {
        // Ambiente de produção - usar BASE_URL
        return (defined('BASE_URL') ? BASE_URL : '/aguaVIVA') . '/assets/' . $path;
    }
}
/**
 * Gera token CSRF
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se o token CSRF é válido
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}