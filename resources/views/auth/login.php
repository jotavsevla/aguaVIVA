<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Água Mineral VIVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f7ff;
            background-image: url('/public/assets/images/planodefundoaguaVIVA.jpg');
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            max-width: 1200px;
        }

        .login-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 102, 0.15);
            width: 100%;
            max-width: 420px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .login-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 102, 0.2);
        }

        .logo {
            width: 220px;
            margin-bottom: 0.5rem;
        }

        h1 {
            text-align: center;
            color: #1a56db;
            margin: 0.5rem 0 1.5rem;
            font-size: 2.2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(45deg, #1a56db, #0066cc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0px 2px 2px rgba(0, 0, 102, 0.1);
        }

        .form-group {
            margin-bottom: 1.2rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 0.85rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border 0.3s ease;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.85rem;
            background-color: #000080;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn:hover {
            background-color: #000080;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .error {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Efeito de água na parte inferior */
        .water-effect {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(0deg, rgba(0, 102, 204, 0.1), transparent);
            z-index: -1;
        }
        h1 {
            text-align: center;
            color: #000080;
            margin: 0.5rem 0 1.5rem;
            font-size: 2.2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #8ac0f6, #0101a4);
            transform: translateX(-50%);
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div class="water-effect"></div>
<div class="container">
    <div class="login-form">
        <img src="/public/assets/images/logo_viva.png" alt="Logo Água Mineral VIVA" class="logo" id="login-form">
        <div id="error-message" class="error" style="display: none;"></div>
        <h1>Login</h1>

        <?php if (isset($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/login">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            fetch('/login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta recebida:', data);
                    if (!data.success) {
                        const errorDiv = document.querySelector('.error') ||
                            document.createElement('div');
                        errorDiv.className = 'error';
                        errorDiv.textContent = data.message;
                        errorDiv.style.display = 'block';
                        if (!document.querySelector('.error')) {
                            document.querySelector('h1').after(errorDiv);
                        }
                    } else {
                        window.location.href = data.redirect;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        });
    });
</script>
</body>
</html>