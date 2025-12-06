<?php
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestao de Entregadores - Agua Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
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
            background-color: #000080;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background-color: #0000b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        .btn-success { background-color: #28a745; }
        .btn-success:hover { background-color: #218838; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .status-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .status-card {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 150px;
        }
        .status-card .number { font-size: 32px; font-weight: bold; }
        .status-card .label { color: #666; font-size: 14px; }
        .status-card.disponivel .number { color: #28a745; }
        .status-card.em_entrega .number { color: #ffc107; }
        .status-card.indisponivel .number { color: #dc3545; }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background-color: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background-color: #f5f9ff; }
        .actions { display: flex; gap: 8px; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        .badge-disponivel { background-color: #d4edda; color: #155724; }
        .badge-em_entrega { background-color: #fff3cd; color: #856404; }
        .badge-indisponivel { background-color: #f8d7da; color: #721c24; }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-title { font-size: 24px; color: #000080; margin: 0; }
        .no-results { text-align: center; padding: 40px 0; color: #666; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .modal-title { font-size: 18px; font-weight: bold; color: #000080; margin: 0; }
        .close { font-size: 28px; font-weight: bold; cursor: pointer; }
        .modal-body { margin-bottom: 20px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
        .status-select {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 13px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Gestao de Entregadores</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/admin" class="btn">Dashboard</a>
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

    <div class="status-cards">
        <div class="status-card disponivel">
            <div class="number"><?php echo $counts['disponivel'] ?? 0; ?></div>
            <div class="label">Disponiveis</div>
        </div>
        <div class="status-card em_entrega">
            <div class="number"><?php echo $counts['em_entrega'] ?? 0; ?></div>
            <div class="label">Em Entrega</div>
        </div>
        <div class="status-card indisponivel">
            <div class="number"><?php echo $counts['indisponivel'] ?? 0; ?></div>
            <div class="label">Indisponiveis</div>
        </div>
    </div>

    <div class="page-header">
        <h2 class="page-title">Lista de Entregadores</h2>
        <a href="/admin/entregadores/create" class="btn btn-success">Novo Entregador</a>
    </div>

    <div class="table-container">
        <?php if (empty($entregadores)): ?>
            <div class="no-results">
                <p>Nenhum entregador cadastrado.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Veiculo</th>
                    <th>Placa</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entregadores as $entregador): ?>
                    <tr>
                        <td><?php echo $entregador['id']; ?></td>
                        <td><?php echo htmlspecialchars($entregador['nome']); ?></td>
                        <td><?php echo htmlspecialchars($entregador['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($entregador['veiculo'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($entregador['placa'] ?? '-'); ?></td>
                        <td>
                            <select class="status-select" onchange="atualizarStatus(<?php echo $entregador['id']; ?>, this.value)" data-original="<?php echo $entregador['status']; ?>">
                                <option value="disponivel" <?php echo $entregador['status'] === 'disponivel' ? 'selected' : ''; ?>>Disponivel</option>
                                <option value="em_entrega" <?php echo $entregador['status'] === 'em_entrega' ? 'selected' : ''; ?>>Em Entrega</option>
                                <option value="indisponivel" <?php echo $entregador['status'] === 'indisponivel' ? 'selected' : ''; ?>>Indisponivel</option>
                            </select>
                        </td>
                        <td class="actions">
                            <a href="/admin/entregadores/edit/<?php echo $entregador['id']; ?>" class="btn btn-sm">Editar</a>
                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?php echo $entregador['id']; ?>, '<?php echo htmlspecialchars($entregador['nome']); ?>')">Excluir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="modal-excluir" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Confirmar Exclusao</h3>
            <span class="close" onclick="fecharModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja excluir o entregador <strong id="nome-entregador"></strong>?</p>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="fecharModal()">Cancelar</button>
            <form id="form-excluir" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <button type="submit" class="btn btn-danger">Confirmar</button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmarExclusao(id, nome) {
        document.getElementById('nome-entregador').textContent = nome;
        document.getElementById('form-excluir').action = '/admin/entregadores/delete/' + id;
        document.getElementById('modal-excluir').style.display = 'block';
    }

    function fecharModal() {
        document.getElementById('modal-excluir').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('modal-excluir');
        if (event.target == modal) {
            fecharModal();
        }
    }

    function atualizarStatus(id, status) {
        fetch('/api/entregadores/' + id + '/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao atualizar status: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao atualizar status');
            console.error(error);
        });
    }

    setTimeout(function() {
        var alerts = document.getElementsByClassName('alert');
        for (var i = 0; i < alerts.length; i++) {
            alerts[i].style.display = 'none';
        }
    }, 5000);
</script>
</body>
</html>
