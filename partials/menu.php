<?php
$isSubDirectory = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$basePathPrefix = $isSubDirectory ? '../' : '';

function isCurrentPage($pageName) {
    return strpos($_SERVER['PHP_SELF'], $pageName) !== false;
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo $basePathPrefix; ?>images/logo_viva.png" alt="Logo Água Mineral VIVA" class="logo">
    </div>

    <div class="user-greeting">
        <p>Olá, <?php echo $_SESSION['user_name'] ?? 'Usuário'; ?>!</p>
        <small class="user-role"><?php echo ucfirst($_SESSION['lvl'] ?? ''); ?></small>
    </div>

    <nav class="sidebar-menu">
        <ul>
            <li>
                <a href="<?php echo $basePathPrefix; ?>index.php"
                   class="<?php echo isCurrentPage('index.php') ? 'active' : ''; ?>">
                    Página Inicial
                </a>
            </li>

            <?php if ($_SESSION['lvl'] === 'supervisor'): ?>
                <!-- Links do Supervisor -->
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/administrar_usuarios.php"
                       class="<?php echo isCurrentPage('administrar_usuarios.php') ? 'active' : ''; ?>">
                        Gerenciar Administradores
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/users_list.php"
                       class="<?php echo isCurrentPage('users_list.php') ? 'active' : ''; ?>">
                        Lista de Usuários
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['lvl'] === 'admin'): ?>
                <!-- Links do Admin -->
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/buscar_cliente.php"
                       class="<?php echo isCurrentPage('buscar_cliente.php') ? 'active' : ''; ?>">
                        Buscar Cliente
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/criar_pedido.php"
                       class="<?php echo isCurrentPage('criar_pedido.php') ? 'active' : ''; ?>">
                        Criar Pedido
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/buscar_pedido.php"
                       class="<?php echo isCurrentPage('buscar_pedido.php') ? 'active' : ''; ?>">
                        Buscar Pedido
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePathPrefix; ?>pages/zona_entrega.php"
                       class="<?php echo isCurrentPage('zona_entrega.php') ? 'active' : ''; ?>">
                        Zona de Entrega
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="logout-container">
        <a href="<?php echo $basePathPrefix; ?>logout.php" class="logout-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Sair
        </a>
    </div>
</div>
