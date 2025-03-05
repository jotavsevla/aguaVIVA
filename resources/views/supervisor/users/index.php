<!-- Modal de confirmação -->
<div id="modal-excluir" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Exclusão</h3>
            <span class="close" onclick="fecharModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja excluir o usuário <strong id="nome-usuario"></strong>?</p>
            <p>Esta ação não poderá ser desfeita.</p>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="fecharModal()">Cancelar</button>
            <form id="form-excluir" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Funções para o modal
    function confirmarExclusao(id, nome) {
        document.getElementById('nome-usuario').textContent = nome;
        <script>
            // Funções para o modal
            function confirmarExclusao(id, nome) {
            document.getElementById('nome-usuario').textContent = nome;
            document.getElementById('form-excluir').action = '/supervisor/users/delete/' + id;
            document.getElementById('modal-excluir').style.display = 'block';
        }

            function fecharModal() {
            document.getElementById('modal-excluir').style.display = 'none';
        }

            // Fechar modal ao clicar fora
            window.onclick = function(event) {
            var modal = document.getElementById('modal-excluir');
            if (event.target == modal) {
            fecharModal();
        }
        }

            // Auto-fechar alertas após 5 segundos
            setTimeout(function() {
            var alerts = document.getElementsByClassName('alert');
            for (var i = 0; i < alerts.length; i++) {
            alerts[i].style.display = 'none';
        }
        }, 5000);
</script><?php
// Verificação de acesso
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Usuários - Água Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }

        .container {
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

        .btn {
            display: inline-block;
            background-color: #004d99;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #003366;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background-color: #f5f9ff;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            color: #004d99;
            margin: 0;
        }

        .no-results {
            text-align: center;
            padding: 40px 0;
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: #004d99;
            margin: 0;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Gestão de Usuários - Água Mineral VIVA</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/supervisor" class="btn">Dashboard</a>
        <a href="/logout" class="btn btn-danger">Sair</a>
    </div>
</div>

<div class="container">
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h2 class="page-title">Lista de Usuários</h2>
        <a href="/supervisor/users/create" class="btn btn-success">Novo Usuário</a>
    </div>

    <div class="table-container">
        <?php if (empty($users)): ?>
            <div class="no-results">
                <p>Nenhum usuário cadastrado.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Nível de Acesso</th>
                    <th>Último Login</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($user['access_level'] === 'admin'): ?>
                                <span class="badge badge-info">Administrador</span>
                            <?php elseif ($user['access_level'] === 'supervisor'): ?>
                                <span class="badge badge-warning">Supervisor</span>
                            <?php else: ?>
                                <span class="badge"><?php echo htmlspecialchars($user['access_level']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?></td>
                        <td class="actions">
                            <a href="/supervisor/users/edit/<?php echo $user['id']; ?>" class="btn">Editar</a>
                            <button class="btn btn-danger" onclick="confirmarExclusao(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Excluir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmação para desativar -->
<div id="modal-desativar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Desativação</h3>
            <span class="close" onclick="fecharModal('modal-desativar')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja desativar o usuário <strong id="nome-usuario-desativar"></strong>?</p>
            <p>Este usuário não poderá mais acessar o sistema até ser reativado.</p>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="fecharModal('modal-desativar')">Cancelar</button>
            <form id="form-desativar" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <button type="submit" class="btn btn-danger">Confirmar Desativação</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmação para ativar -->
<div id="modal-ativar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Ativação</h3>
            <span class="close" onclick="fecharModal('modal-ativar')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja ativar o usuário <strong id="nome-usuario-ativar"></strong>?</p>
            <p>Este usuário poderá acessar o sistema novamente.</p>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="fecharModal('modal-ativar')">Cancelar</button>
            <form id="form-ativar" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <button type="submit" class="btn btn-success">Confirmar Ativação</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Funções para o modal
    function confirmarDesativacao(id, nome) {
        document.getElementById('nome-usuario-desativar').textContent = nome;
        document.getElementById('form-desativar').action = '/supervisor/users/toggle-status/' + id;
        document.getElementById('modal-desativar').style.display = 'block';
    }

    function confirmarAtivacao(id, nome) {
        document.getElementById('nome-usuario-ativar').textContent = nome;
        document.getElementById('form-ativar').action = '/supervisor/users/toggle-status/' + id;
        document.getElementById('modal-ativar').style.display = 'block';
    }

    function fecharModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        var modals = document.getElementsByClassName('modal');
        for (var i = 0; i < modals.length; i++) {
            if (event.target == modals[i]) {
                modals[i].style.display = 'none';
            }
        }
    }

    // Auto-fechar alertas após 5 segundos
    setTimeout(function() {
        var alerts = document.getElementsByClassName('alert');
        for (var i = 0; i < alerts.length; i++) {
            alerts[i].style.display = 'none';
        }
    }, 5000);
</script>
</body>
</html>