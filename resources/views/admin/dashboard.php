<?php
// Dashboard Administrativo
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Carregar dados do banco
use app\Models\Cliente;
use app\Models\Entregador;
use app\Models\Entrega;
use src\Database\Connection;

$conn = Connection::getInstance();
$clienteModel = new Cliente($conn);
$entregadorModel = new Entregador($conn);
$entregaModel = new Entrega($conn);

$clientes = $clienteModel->findAll();
$totalClientes = count($clientes);

$entregadoresCounts = $entregadorModel->countByStatus();
$entregadoresDisponiveis = $entregadoresCounts['disponivel'];

$entregasCounts = $entregaModel->countByStatus();
$entregasPendentes = $entregasCounts['pendente'];
$entregasEmAndamento = $entregasCounts['em_andamento'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Agua Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #000080;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { margin: 0; font-size: 24px; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-info span { margin-right: 15px; }
        .btn {
            display: inline-block;
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background-color: rgba(255,255,255,0.1); }
        .btn-danger { border-color: #ff6b6b; color: #ff6b6b; }
        .btn-danger:hover { background-color: rgba(255,107,107,0.1); }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }
        .card h2 {
            color: #000080;
            margin-top: 0;
            border-bottom: 2px solid #e6f0ff;
            padding-bottom: 10px;
        }
        .card-content { margin-top: 15px; }
        .stat {
            font-size: 36px;
            font-weight: bold;
            color: #000080;
            margin: 5px 0;
        }
        .stat.success { color: #28a745; }
        .stat.warning { color: #ffc107; }
        .stat.danger { color: #dc3545; }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .card-link {
            display: inline-block;
            margin-top: 15px;
            color: #000080;
            text-decoration: none;
            font-weight: 600;
        }
        .card-link:hover { text-decoration: underline; }
        .quick-actions {
            margin-top: 30px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .quick-actions h3 {
            color: #000080;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .action-btn {
            display: inline-block;
            background-color: #000080;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .action-btn:hover { background-color: #0000b3; }
        .action-btn.success { background-color: #28a745; }
        .action-btn.success:hover { background-color: #218838; }
        .status-summary {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .status-item {
            font-size: 13px;
            color: #666;
        }
        .status-item span {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Dashboard - Agua Mineral VIVA</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/logout" class="btn btn-danger">Sair</a>
    </div>
</div>

<div class="dashboard-container">
    <div class="dashboard-cards">
        <div class="card">
            <h2>Clientes</h2>
            <div class="card-content">
                <div class="stat"><?php echo $totalClientes; ?></div>
                <div class="stat-label">Total cadastrados</div>
                <a href="/admin/clientes" class="card-link">Gerenciar clientes &rarr;</a>
            </div>
        </div>

        <div class="card">
            <h2>Entregadores</h2>
            <div class="card-content">
                <div class="stat <?php echo $entregadoresDisponiveis > 0 ? 'success' : 'danger'; ?>">
                    <?php echo $entregadoresDisponiveis; ?>
                </div>
                <div class="stat-label">Disponiveis agora</div>
                <div class="status-summary">
                    <span class="status-item">Em entrega: <span><?php echo $entregadoresCounts['em_entrega']; ?></span></span>
                    <span class="status-item">Indisponiveis: <span><?php echo $entregadoresCounts['indisponivel']; ?></span></span>
                </div>
                <a href="/admin/entregadores" class="card-link">Gerenciar entregadores &rarr;</a>
            </div>
        </div>

        <div class="card">
            <h2>Entregas Pendentes</h2>
            <div class="card-content">
                <div class="stat <?php echo $entregasPendentes > 0 ? 'warning' : 'success'; ?>">
                    <?php echo $entregasPendentes; ?>
                </div>
                <div class="stat-label">Aguardando atribuicao</div>
                <div class="status-summary">
                    <span class="status-item">Em andamento: <span><?php echo $entregasEmAndamento; ?></span></span>
                    <span class="status-item">Concluidas: <span><?php echo $entregasCounts['concluida']; ?></span></span>
                </div>
                <a href="/admin/entregas" class="card-link">Gerenciar entregas &rarr;</a>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Acoes Rapidas</h3>
        <div class="action-buttons">
            <a href="/admin/entregas/create" class="action-btn success">+ Nova Entrega</a>
            <a href="/admin/entregadores/create" class="action-btn">+ Novo Entregador</a>
            <a href="/admin/clientes/create" class="action-btn">+ Novo Cliente</a>
            <a href="/admin/entregas?status=pendente" class="action-btn">Ver Pendentes</a>
        </div>
    </div>
</div>
</body>
</html>
