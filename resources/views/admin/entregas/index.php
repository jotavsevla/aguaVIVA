<?php
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}
$statusFilter = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestao de Entregas - Agua Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
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
        .btn-warning { background-color: #ffc107; color: #000; }
        .btn-warning:hover { background-color: #e0a800; }
        .btn-info { background-color: #17a2b8; }
        .btn-info:hover { background-color: #138496; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .status-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .status-card {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 130px;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .status-card:hover { transform: translateY(-3px); }
        .status-card.active { border: 2px solid #000080; }
        .status-card .number { font-size: 28px; font-weight: bold; }
        .status-card .label { color: #666; font-size: 13px; }
        .status-card.pendente .number { color: #dc3545; }
        .status-card.atribuida .number { color: #ffc107; }
        .status-card.em_andamento .number { color: #17a2b8; }
        .status-card.concluida .number { color: #28a745; }
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
        .actions { display: flex; gap: 5px; flex-wrap: wrap; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-pendente { background-color: #f8d7da; color: #721c24; }
        .badge-atribuida { background-color: #fff3cd; color: #856404; }
        .badge-em_andamento { background-color: #d1ecf1; color: #0c5460; }
        .badge-concluida { background-color: #d4edda; color: #155724; }
        .badge-cancelada { background-color: #e2e3e5; color: #383d41; }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
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
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .modal-title { font-size: 18px; font-weight: bold; color: #000080; margin: 0; }
        .close { font-size: 28px; cursor: pointer; }
        .modal-body { margin-bottom: 20px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
        .entregador-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #000080;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-box h4 { margin: 0 0 10px; color: #000080; }
        .entregadores-disponiveis {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .entregador-chip {
            background: #d4edda;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Gestao de Entregas</h1>
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

    <?php if (!empty($entregadoresDisponiveis)): ?>
    <div class="info-box">
        <h4>Entregadores Disponiveis Agora</h4>
        <div class="entregadores-disponiveis">
            <?php foreach ($entregadoresDisponiveis as $ent): ?>
                <span class="entregador-chip"><?php echo htmlspecialchars($ent['nome']); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="info-box" style="background: #fff3cd; border-color: #ffc107;">
        <h4 style="color: #856404;">Nenhum entregador disponivel</h4>
        <p style="margin: 0; color: #856404;">Marque entregadores como disponiveis em <a href="/admin/entregadores">Gestao de Entregadores</a></p>
    </div>
    <?php endif; ?>

    <div class="status-cards">
        <a href="/admin/entregas" class="status-card <?php echo !$statusFilter ? 'active' : ''; ?>">
            <div class="number"><?php echo array_sum($counts); ?></div>
            <div class="label">Total</div>
        </a>
        <a href="/admin/entregas?status=pendente" class="status-card pendente <?php echo $statusFilter === 'pendente' ? 'active' : ''; ?>">
            <div class="number"><?php echo $counts['pendente'] ?? 0; ?></div>
            <div class="label">Pendentes</div>
        </a>
        <a href="/admin/entregas?status=atribuida" class="status-card atribuida <?php echo $statusFilter === 'atribuida' ? 'active' : ''; ?>">
            <div class="number"><?php echo $counts['atribuida'] ?? 0; ?></div>
            <div class="label">Atribuidas</div>
        </a>
        <a href="/admin/entregas?status=em_andamento" class="status-card em_andamento <?php echo $statusFilter === 'em_andamento' ? 'active' : ''; ?>">
            <div class="number"><?php echo $counts['em_andamento'] ?? 0; ?></div>
            <div class="label">Em Andamento</div>
        </a>
        <a href="/admin/entregas?status=concluida" class="status-card concluida <?php echo $statusFilter === 'concluida' ? 'active' : ''; ?>">
            <div class="number"><?php echo $counts['concluida'] ?? 0; ?></div>
            <div class="label">Concluidas</div>
        </a>
    </div>

    <div class="page-header">
        <h2 class="page-title">Lista de Entregas</h2>
        <a href="/admin/entregas/create" class="btn btn-success">Nova Entrega</a>
    </div>

    <div class="table-container">
        <?php if (empty($entregas)): ?>
            <div class="no-results">
                <p>Nenhuma entrega encontrada.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Endereco</th>
                    <th>Entregador</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Acoes</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($entregas as $entrega): ?>
                    <tr>
                        <td><?php echo $entrega['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($entrega['cliente_nome'] ?? 'N/A'); ?></strong>
                            <?php if (!empty($entrega['cliente_telefone'])): ?>
                                <br><small><?php echo htmlspecialchars($entrega['cliente_telefone']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($entrega['endereco_entrega']); ?></td>
                        <td>
                            <?php if ($entrega['entregador_nome']): ?>
                                <?php echo htmlspecialchars($entrega['entregador_nome']); ?>
                            <?php else: ?>
                                <em style="color: #999;">Nao atribuido</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusLabels = [
                                'pendente' => 'Pendente',
                                'atribuida' => 'Atribuida',
                                'em_andamento' => 'Em Andamento',
                                'concluida' => 'Concluida',
                                'cancelada' => 'Cancelada'
                            ];
                            ?>
                            <span class="badge badge-<?php echo $entrega['status']; ?>">
                                <?php echo $statusLabels[$entrega['status']] ?? $entrega['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($entrega['data_solicitacao'])); ?>
                        </td>
                        <td class="actions">
                            <?php if ($entrega['status'] === 'pendente'): ?>
                                <button class="btn btn-success btn-sm" onclick="abrirModalAtribuir(<?php echo $entrega['id']; ?>, '<?php echo htmlspecialchars($entrega['cliente_nome']); ?>')">Atribuir</button>
                                <form method="post" action="/admin/entregas/<?php echo $entrega['id']; ?>/atribuir-auto" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <button type="submit" class="btn btn-info btn-sm" <?php echo empty($entregadoresDisponiveis) ? 'disabled' : ''; ?>>Auto</button>
                                </form>
                            <?php elseif ($entrega['status'] === 'atribuida'): ?>
                                <form method="post" action="/admin/entregas/<?php echo $entrega['id']; ?>/iniciar" style="display:inline;">
                                    <button type="submit" class="btn btn-warning btn-sm">Iniciar</button>
                                </form>
                            <?php elseif ($entrega['status'] === 'em_andamento'): ?>
                                <form method="post" action="/admin/entregas/<?php echo $entrega['id']; ?>/concluir" style="display:inline;">
                                    <button type="submit" class="btn btn-success btn-sm">Concluir</button>
                                </form>
                            <?php endif; ?>

                            <?php if (in_array($entrega['status'], ['pendente', 'atribuida'])): ?>
                                <form method="post" action="/admin/entregas/<?php echo $entrega['id']; ?>/cancelar" style="display:inline;">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancelar esta entrega?')">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div id="modal-atribuir" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Atribuir Entrega</h3>
            <span class="close" onclick="fecharModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Atribuir entrega para: <strong id="cliente-nome"></strong></p>
            <form id="form-atribuir" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <label for="entregador_id">Selecione o Entregador:</label>
                <select name="entregador_id" id="entregador_id" class="entregador-select" required>
                    <option value="">-- Selecione --</option>
                    <?php foreach ($entregadoresDisponiveis as $ent): ?>
                        <option value="<?php echo $ent['id']; ?>">
                            <?php echo htmlspecialchars($ent['nome']); ?>
                            <?php if ($ent['veiculo']): ?> - <?php echo htmlspecialchars($ent['veiculo']); ?><?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="fecharModal()">Cancelar</button>
            <button type="submit" class="btn btn-success">Atribuir</button>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalAtribuir(id, clienteNome) {
        document.getElementById('cliente-nome').textContent = clienteNome;
        document.getElementById('form-atribuir').action = '/admin/entregas/' + id + '/atribuir';
        document.getElementById('modal-atribuir').style.display = 'block';
    }

    function fecharModal() {
        document.getElementById('modal-atribuir').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('modal-atribuir');
        if (event.target == modal) {
            fecharModal();
        }
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
