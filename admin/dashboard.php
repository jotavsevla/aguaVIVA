// config/roles.php
<?php
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPERVISOR', 'supervisor');
define('ROLE_USER', 'user');

// Array associativo com as permissões de cada papel
const ROLE_PERMISSIONS = [
    'admin' => [
        'create_order',
        'view_order',
        'edit_order',
        'delete_order',
        'view_client',
        'create_client',
        'edit_client',
        'delete_client',
        'view_delivery_zones',
        'edit_delivery_zones',
    ],
    'supervisor' => [
        'view_order_history',
        'view_analytics',
        'create_user',
        'edit_user',
        'delete_user',
        'view_user',
    ],
    'user' => [
        'view_order',
        'view_client',
    ]
];

function hasPermission($permission) {
    if (!isset($_SESSION['lvl'])) return false;
    $userRole = $_SESSION['lvl'];
    return in_array($permission, ROLE_PERMISSIONS[$userRole] ?? []);
}

// config/session_and_template.php

// supervisor/manage_users.php
<?php
require_once '../config/config.php';
require_once '../config/session_and_template.php';

if (!hasPermission('create_user')) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

class UserManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUsers() {
        $stmt = $this->conn->query("SELECT id, user, access_level FROM users ORDER BY user");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $password, $access_level = 'user') {
        if ($_SESSION['lvl'] !== ROLE_SUPERVISOR) {
            throw new Exception("Apenas supervisores podem criar usuários.");
        }

        if ($access_level === ROLE_SUPERVISOR) {
            throw new Exception("Não é permitido criar outros supervisores.");
        }

        try {
            // Verificar se usuário já existe
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE user = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                throw new Exception("Este nome de usuário já está em uso.");
            }

            // Inserir novo usuário
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare(
                "INSERT INTO users (user, password, access_level) 
                 VALUES (:username, :password, :access_level)"
            );
            return $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'access_level' => $access_level
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }
}

// supervisor/dashboard.php
<?php
require_once '../config/config.php';
require_once '../config/session_and_template.php';

if ($_SESSION['lvl'] !== ROLE_SUPERVISOR) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

$content = <<<EOT
<div class="dashboard-container">
    <h2>Painel do Supervisor</h2>
    <div class="dashboard-cards">
        <div class="card">
            <h3>Usuários Ativos</h3>
            <!-- Adicionar conteúdo -->
        </div>
        <div class="card">
            <h3>Pedidos do Dia</h3>
            <!-- Adicionar conteúdo -->
        </div>
        <div class="card">
            <h3>Eficiência por Administrador</h3>
            <!-- Adicionar conteúdo -->
        </div>
    </div>
</div>
EOT;

renderPage('Dashboard do Supervisor', $content);

// supervisor/order_history.php
<?php
require_once '../config/config.php';
require_once '../config/session_and_template.php';

if (!hasPermission('view_order_history')) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

// Implementar visualização do histórico de pedidos
// ...

// admin/dashboard.php
<?php
require_once '../config/config.php';
require_once '../config/session_and_template.php';

if ($_SESSION['lvl'] !== ROLE_ADMIN) {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

$content = <<<EOT
<div class="dashboard-container">
    <h2>Painel do Administrador</h2>
    <div class="dashboard-cards">
        <div class="card">
            <h3>Pedidos Pendentes</h3>
            <!-- Adicionar conteúdo -->
        </div>
        <div class="card">
            <h3>Entregas do Dia</h3>
            <!-- Adicionar conteúdo -->
        </div>
        <div class="card">
            <h3>Clientes Ativos</h3>
            <!-- Adicionar conteúdo -->
        </div>
    </div>
</div>
EOT;

renderPage('Dashboard do Administrador', $content);