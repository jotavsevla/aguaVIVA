<?php
    include_once '.\config\config.php';
    if (!(isset($_SESSION['userlogged'])))
        header('Location: login.php');
?>
<!DOCTYPE html>
<html lang="br">
<head>
    <meta charset="UTF-8">
    <title>Controle de Fidelidade Agua Viva</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #ADD8E6;
            margin: 0;
        }
        form {
            border: 1px solid lightblue;
            padding: 40px;
            background-color: #fff;
        }
        form div {
            margin-bottom: 10px;
        }
        form input[type="submit"] {
            display: block;
            margin: auto;
        }
    </style>
</head>
<body>
<form action="login.php" method="post" style="width: 220px;">
    <h1>Login VIVA</h1>
    <div>
        <label for="username">Usuário:</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="password">Senha:</label>
        <input
                type="password" name="password" id="password" required>
    </div>
    <div>
        <input type="submit" value="entrar">
    </div>
</form>
</body>
</html>
