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
    <title>Novo Entregador - Agua Mineral VIVA</title>
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #000080;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            border: 1px solid #f5c6cb;
        }
        .alert ul { margin: 5px 0 0; padding-left: 20px; }
        .required { color: #dc3545; }
    </style>
</head>
<body>
<div class="header">
    <h1>Novo Entregador</h1>
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/admin/entregadores" class="btn">Voltar</a>
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
        <h2 class="form-title">Cadastrar Entregador</h2>
        <form method="post" action="/admin/entregadores/store">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="nome">Nome <span class="required">*</span></label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($formData['nome'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone <span class="required">*</span></label>
                <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($formData['telefone'] ?? ''); ?>" required placeholder="(00) 00000-0000">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="veiculo">Veiculo</label>
                    <input type="text" id="veiculo" name="veiculo" value="<?php echo htmlspecialchars($formData['veiculo'] ?? ''); ?>" placeholder="Ex: Moto Honda CG 160">
                </div>

                <div class="form-group">
                    <label for="placa">Placa</label>
                    <input type="text" id="placa" name="placa" value="<?php echo htmlspecialchars($formData['placa'] ?? ''); ?>" placeholder="ABC-1234">
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status Inicial</label>
                <select id="status" name="status">
                    <option value="indisponivel" <?php echo ($formData['status'] ?? '') === 'indisponivel' ? 'selected' : ''; ?>>Indisponivel</option>
                    <option value="disponivel" <?php echo ($formData['status'] ?? '') === 'disponivel' ? 'selected' : ''; ?>>Disponivel</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Cadastrar</button>
                <a href="/admin/entregadores" class="btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
