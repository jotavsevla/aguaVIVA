<?php
require_once '../config/config.php';
require_once '../config/session_and_template.php';

// Verificar se é administrador
if (!isset($_SESSION['lvl']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: ' . getBaseUrl() . 'login.php');
    exit;
}

// Classe para gerenciar informações do Dashboard
class DashboardManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getTotalUsers() {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM users");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalClients() {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM clientes");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getRecentPedidos($limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT p.id, p.data_pedido, p.quantidade_agua, p.status_pedido, 
                   c.nome as cliente_nome
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            ORDER BY p.data_pedido DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidosPorStatus() {
        $stmt = $this->conn->query("
            SELECT status_pedido, COUNT(*) as total
            FROM pedidos
            GROUP BY status_pedido
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntregadoresAtivos() {
        $stmt = $this->conn->query("
            SELECT id, nome, telefone
            FROM entregadores
            WHERE status_ativo = 1
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Conexão com o banco de dados
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dashboardManager = new DashboardManager($conn);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Obter dados para o dashboard
try {
    $totalUsers = $dashboardManager->getTotalUsers();
    $totalClients = $dashboardManager->getTotalClients();
    $recentPedidos = $dashboardManager->getRecentPedidos();
    $pedidosPorStatus = $dashboardManager->getPedidosPorStatus();
    $entregadoresAtivos = $dashboardManager->getEntregadoresAtivos();
} catch(Exception $e) {
    $error = $e->getMessage();
}

// Construir o conteúdo da página
$content = <<<EOT
<div class="dashboard-container">
    <div class="welcome-section">
        <h2>Bem-vindo, {$_SESSION['user_name']}!</h2>
        <p>Painel Administrativo - Água Mineral VIVA</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Usuários do Sistema</h3>
            <div class="stat-number">{$totalUsers}</div>
            <a href="dashboard.php" class="stat-link">Gerenciar Usuários</a>
        </div>

        <div class="stat-card">
            <h3>Total de Clientes</h3>
            <div class="stat-number">{$totalClients}</div>
            <a href="buscar_cliente.php" class="stat-link">Gerenciar Clientes</a>
        </div>

        <div class="stat-card">
            <h3>Status dos Pedidos</h3>
            <div class="stat-list">
EOT;

foreach ($pedidosPorStatus as $status) {
    $content .= "<div class='status-item'>
                    <span>{$status['status_pedido']}</span>
                    <span class='status-count'>{$status['total']}</span>
                 </div>";
}

$content .= <<<EOT
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Pedidos Recentes</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
EOT;

foreach ($recentPedidos as $pedido) {
    $data = date('d/m/Y H:i', strtotime($pedido['data_pedido']));
    $content .= "<tr>
                    <td>#{$pedido['id']}</td>
                    <td>{$pedido['cliente_nome']}</td>
                    <td>{$data}</td>
                    <td><span class='status-badge status-{$pedido['status_pedido']}'>{$pedido['status_pedido']}</span></td>
                 </tr>";
}

$content .= <<<EOT
                </tbody>
            </table>
            <a href="buscar_pedido.php" class="view-all">Ver Todos os Pedidos</a>
        </div>

        <div class="dashboard-card">
            <h3>Entregadores Ativos</h3>
            <div class="entregadores-list">
EOT;

foreach ($entregadoresAtivos as $entregador) {
    $content .= "<div class='entregador-item'>
                    <span class='entregador-nome'>{$entregador['nome']}</span>
                    <span class='entregador-telefone'>{$entregador['telefone']}</span>
                 </div>";
}

$content .= <<<EOT
            </div>
            <a href="zona_entrega.php" class="view-all">Gerenciar Zonas de Entrega</a>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Ações Rápidas</h3>
        <div class="action-buttons">
            <a href="criar_pedido.php" class="btn btn-primary">Novo Pedido</a>
            <a href="dashboard.php" class="btn btn-secondary">Gerenciar Usuários</a>
            <a href="buscar_cliente.php" class="btn btn-secondary">Buscar Cliente</a>
            <a href="zona_entrega.php" class="btn btn-secondary">Zonas de Entrega</a>
        </div>
    </div>
</div>
EOT;

// Adicionar CSS específico para o dashboard
$additionalCss = <<<EOT
<style>
    .dashboard-container {
        padding: 20px;
    }
    .welcome-section {
        margin-bottom: 30px;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2em;
        font-weight: bold;
        color: #0066cc;
    }
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .dashboard-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
    }
    .status-pendente { background: #fff3cd; color: #856404; }
    .status-confirmado { background: #d4edda; color: #155724; }
    .status-em_entrega { background: #cce5ff; color: #004085; }
    .status-entregue { background: #d1e7dd; color: #0f5132; }
    .status-cancelado { background: #f8d7da; color: #721c24; }
    .quick-actions {
        margin-top: 30px;
    }
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .view-all {
        display: block;
        text-align: right;
        margin-top: 10px;
        color: #0066cc;
        text-decoration: none;
    }
    .entregadores-list {
        margin-top: 10px;
    }
    .entregador-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
    }
</style>
EOT;

$content = $additionalCss . $content;

// Renderizar a página
renderPage('Dashboard Administrativo', $content);