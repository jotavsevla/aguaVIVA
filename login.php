<?php
session_start();
require_once 'config/config.php';

$error = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $conn = conectarDB();
        $stmt = $conn->prepare("SELECT id, user, password, access_level FROM users WHERE user = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['userlogged'] = $user['id'];
                $_SESSION['user_name'] = $user['user'];
                $_SESSION['last_activity'] = time();
                $_SESSION['lvl'] = $user['acess_level'];

                // Atualizar último login
                $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                header('Location: index.php');
                exit;
            } else {
                $debug .= "Senha incorreta. ";
            }
        } else {
            $debug .= "Usuário não encontrado. ";
        }

        $error = "Usuário ou senha inválidos";
    } catch(PDOException $e) {
        $error = "Erro de conexão: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo TITLE; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        body {
            background-image: url('images/producaoAguaViva.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }
        .logo {
            display: block;
            margin: 0 auto 1rem;
            max-width: 150px;
        }
        h1 {
            text-align: center;
            color: #0066cc;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0052a3;
        }
        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <img src="images/logo_viva.png" alt="Logo Água Mineral VIVA" class="logo">
    <h1>Login VIVA</h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <div class="form-group">
            <label for="username">Usuário:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
