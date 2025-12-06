<?php
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}
$formData = $_SESSION['form_data'] ?? [];
$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Entrega - Agua Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header {
            background-color: #000080;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { margin: 0; font-size: 24px; }
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
        .btn-success { background-color: #28a745; }
        .btn-success:hover { background-color: #218838; }
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .form-title {
            color: #000080;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e6f0ff;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #000080;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert ul { margin: 5px 0 0; padding-left: 20px; }
        .required { color: #dc3545; }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Nova Entrega</h1>
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/admin/entregas" class="btn">Voltar</a>
    </div>
</div>

<div class="container">
    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <strong>Corrija os erros abaixo:</strong>
            <ul>
                <?php foreach ($formErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h2 class="form-title">Cadastrar Nova Entrega</h2>
        <form method="post" action="/admin/entregas/store">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="cliente_id">Cliente <span class="required">*</span></label>
                <select id="cliente_id" name="cliente_id" required>
                    <option value="">-- Selecione o Cliente --</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>"
                                data-endereco="<?php echo htmlspecialchars($cliente['endereco']); ?>"
                            <?php echo ($formData['cliente_id'] ?? '') == $cliente['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nome']); ?>
                            - <?php echo htmlspecialchars($cliente['telefone']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="endereco_entrega">Endereco de Entrega <span class="required">*</span></label>
                <input type="text" id="endereco_entrega" name="endereco_entrega"
                       value="<?php echo htmlspecialchars($formData['endereco_entrega'] ?? ''); ?>"
                       required placeholder="Endereco completo para entrega">
                <p class="help-text">Selecione um cliente para preencher automaticamente ou digite um endereco diferente.</p>
            </div>

            <div class="form-group">
                <label for="observacoes">Observacoes</label>
                <textarea id="observacoes" name="observacoes" rows="3"
                          placeholder="Informacoes adicionais sobre a entrega..."><?php echo htmlspecialchars($formData['observacoes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="atribuir_automatico" name="atribuir_automatico" value="1"
                        <?php echo !empty($formData['atribuir_automatico']) ? 'checked' : ''; ?>>
                    <label for="atribuir_automatico" style="display: inline; font-weight: normal;">
                        Atribuir automaticamente a um entregador disponivel
                    </label>
                </div>
                <p class="help-text">Se marcado, o sistema ira atribuir a entrega ao primeiro entregador disponivel.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Cadastrar Entrega</button>
                <a href="/admin/entregas" class="btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('cliente_id').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        var endereco = selected.getAttribute('data-endereco');
        if (endereco) {
            document.getElementById('endereco_entrega').value = endereco;
        }
    });
</script>
</body>
</html>
