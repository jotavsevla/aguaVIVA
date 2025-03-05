<?php
// Conteúdo do arquivo resources/views/admin/dashboard.php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Verifica se o usuário está logado e tem nível de acesso de admin
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrativo - Água Mineral VIVA</title>
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

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info span {
            margin-right: 15px;
        }

        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            color: #000080;
            margin-top: 0;
            border-bottom: 2px solid #e6f0ff;
            padding-bottom: 10px;
        }

        .card-content {
            margin-top: 15px;
        }

        .stat {
            font-size: 32px;
            font-weight: bold;
            color: #000080;
            margin: 5px 0;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Dashboard Administrativo - Água Mineral VIVA</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/logout" class="logout-btn">Sair</a>
    </div>
</div>

<div class="dashboard-container">
    <div class="dashboard-cards">
        <div class="card">
            <h2>Clientes</h2>
            <div class="card-content">
                <div class="stat">56</div>
                <div class="stat-label">Total de clientes</div>
                <p>Gerencie os clientes cadastrados no sistema.</p>
                <a href="/admin/clientes">Ver todos os clientes</a>
            </div>
        </div>

        <div class="card">
            <h2>Pedidos</h2>
            <div class="card-content">
                <div class="stat">23</div>
                <div class="stat-label">Pedidos pendentes</div>
                <p>Gerencie os pedidos de água mineral.</p>
                <a href="/admin/pedidos">Ver todos os pedidos</a>
            </div>
        </div>

        <div class="card">
            <h2>Entregas</h2>
            <div class="card-content">
                <div class="stat">12</div>
                <div class="stat-label">Entregas para hoje</div>
                <p>Acompanhe as entregas programadas.</p>
                <a href="/admin/entregas">Ver cronograma de entregas</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>