<?php
$pageTitle = 'Página Inicial';
require_once 'partials/header.php';

// Verifica o nível de acesso
$userRole = $_SESSION['lvl'] ?? '';
$userName = $_SESSION['user_name'] ?? 'Usuário';
?>

    <div class="dashboard">
        <div class="welcome-section">
            <h2>Bem-vindo ao Sistema de Gestão Água Mineral VIVA</h2>
            <p>Olá, <?php echo htmlspecialchars($userName); ?>!
                Você está logado como <span class="user-type"><?php echo htmlspecialchars($userRole); ?></span>.</p>
        </div>

        <div class="quick-access-section">
            <h3>Acesso Rápido</h3>
            <div class="dashboard-grid">
                <?php if ($userRole === 'supervisor'): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">👥</div>
                        <h4>Gestão de Usuários</h4>
                        <ul>
                            <li><a href="pages/administrar_usuarios.php">Gerenciar Administradores</a></li>
                            <li><a href="pages/users_list.php">Lista de Usuários</a></li>
                        </ul>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">📊</div>
                        <h4>Relatórios</h4>
                        <p>Acesse relatórios e análises do sistema</p>
                        <a href="pages/relatorios.php" class="card-button">Ver Relatórios</a>
                    </div>
                <?php endif; ?>

                <?php if ($userRole === 'admin'): ?>
                    <div class="dashboard-card">
                        <div class="card-icon">📝</div>
                        <h4>Pedidos</h4>
                        <ul>
                            <li><a href="pages/criar_pedido.php">Criar Novo Pedido</a></li>
                            <li><a href="pages/buscar_pedido.php">Buscar Pedidos</a></li>
                        </ul>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">👤</div>
                        <h4>Clientes</h4>
                        <ul>
                            <li><a href="pages/buscar_cliente.php">Buscar Cliente</a></li>
                            <li><a href="pages/criar_cliente.php">Cadastrar Novo Cliente</a></li>
                        </ul>
                    </div>
                    <div class="dashboard-card">
                        <div class="card-icon">🚚</div>
                        <h4>Entregas</h4>
                        <ul>
                            <li><a href="pages/zona_entrega.php">Zonas de Entrega</a></li>
                            <li><a href="pages/pedidos_entrega.php">Pedidos para Entrega</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once 'partials/footer.php'; ?>