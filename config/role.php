<?php
const ROLE_ADMIN = 'admin';
const ROLE_SUPERVISOR = 'supervisor';
const ROLE_USER = 'user';

// Definição das permissões por papel
const ROLE_PERMISSIONS = [
    'supervisor' => [
        'manage_users',      // Gerenciar usuários
        'view_analytics',    // Ver análises
        'generate_reports',  // Gerar relatórios
        'view_all_orders'    // Ver todos os pedidos
    ],
    'admin' => [
        'manage_orders',     // Gerenciar pedidos
        'manage_clients',    // Gerenciar clientes
        'manage_deliveries', // Gerenciar entregas
        'view_own_orders'    // Ver próprios pedidos
    ],
    'user' => [
        'view_basic_info'    // Ver informações básicas
    ]
];

/**
 * Verifica se o usuário tem determinada permissão
 * @param string $permission
 * @return bool
 */
function hasPermission($permission) {
    if (!isset($_SESSION['lvl'])) return false;

    $userRole = $_SESSION['lvl'];
    return in_array($permission, ROLE_PERMISSIONS[$userRole] ?? []);
}

/**
 * Verifica se o usuário tem determinado papel
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['lvl']) && $_SESSION['lvl'] === $role;
}