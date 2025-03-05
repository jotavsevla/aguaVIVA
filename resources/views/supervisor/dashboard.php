<?php

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Verifica se o usuário está logado e tem nível de acesso de supervisor
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard do Supervisor - Água Mineral VIVA</title>
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
            background-color: #004d99;
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
            color: #004d99;
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
            color: #004d99;
            margin: 5px 0;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .chart {
            height: 200px;
            background-color: #f0f7ff;
            border-radius: 4px;
            margin-top: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Dashboard do Supervisor - Água Mineral VIVA</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/logout" class="logout-btn">Sair</a>
    </div>
</div>

<div class="dashboard-container">
    <div class="dashboard-cards">
        <div class="card">
            <h2>Usuários</h2>
            <div class="card-content">
                <div class="stat">12</div>
                <div class="stat-label">Usuários ativos</div>
                <p>Gerencie os usuários do sistema.</p>
                <a href="/supervisor/users">Gerenciar usuários</a>
            </div>
        </div>

        <div class="card">
            <h2>Relatórios</h2>
            <div class="card-content">
                <div class="stat">5</div>
                <div class="stat-label">Relatórios disponíveis</div>
                <p>Acesse os relatórios gerenciais.</p>
                <a href="/supervisor/reports">Ver relatórios</a>
            </div>
        </div>

        <div class="card">
            <h2>Vendas (Mensal)</h2>
            <div class="card-content">
                <div class="stat">R$ 56.780</div>
                <div class="stat-label">Total de vendas</div>
                <div class="chart">Gráfico de vendas será exibido aqui</div>
            </div>
        </div>

        <div class="card">
            <h2>Performance</h2>
            <div class="card-content">
                <div class="stat">98%</div>
                <div class="stat-label">Taxa de entregas no prazo</div>
                <p>Análise de performance da operação.</p>
                <a href="/supervisor/performance">Ver detalhes</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>