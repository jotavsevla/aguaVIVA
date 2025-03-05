<?php
// Verificação de acesso
if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Recuperar dados do formulário em caso de erro
$formData = $_SESSION['form_data'] ?? $cliente ?? [];
unset($_SESSION['form_data']);

// Recuperar erros
$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - Água Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
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
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0000b3;
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

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .card-title {
            margin: 0;
            color: #000080;
            font-size: 20px;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #000080;
            box-shadow: 0 0 0 2px rgba(0, 0, 128, 0.1);
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
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

        .error-list {
            margin: 0;
            padding-left: 20px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Editar Cliente - Água Mineral VIVA</h1>
    <div class="user-info">
        <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="/admin" class="btn">Dashboard</a>
        <a href="/logout" class="btn btn-danger">Sair</a>
    </div>
</div>

<div class="container">
    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger">
            <strong>Ocorreram erros:</strong>
            <ul class="error-list">
                <?php foreach ($formErrors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Editar Cliente</h2>
        </div>
        <div class="card-body">
            <form method="post" action="/admin/clientes/update/<?php echo $cliente['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($formData['nome'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($formData['cpf'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($formData['endereco'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($formData['telefone'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone2">Telefone 2 (opcional)</label>
                        <input type="text" id="telefone2" name="telefone2" value="<?php echo htmlspecialchars($formData['telefone2'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/clientes" class="btn">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>