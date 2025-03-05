<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Services\AuthService;

class AuthController {
    private $authService;
    private $basePath;

    public function __construct() {
        $this->authService = new AuthService();

        // Detecta caminho base
        $this->basePath = '';
        if (php_sapi_name() != 'cli-server') {
            $this->basePath = '/aguaVIVA';
        }

        error_log("AuthController construído com caminho base: {$this->basePath}");
    }

    // Função auxiliar para redirecionamento
    private function redirect($path) {
        $fullPath = $this->basePath . $path;
        error_log("Redirecionando para: $fullPath");
        header("Location: $fullPath");
        exit;
    }

    public function showLoginForm() {
        error_log("Método showLoginForm chamado");

        // Check if already logged in - verificação corrigida
        if (isset($_SESSION['userlogged']) && $_SESSION['userlogged'] === true) {
            error_log("Usuário já está logado, redirecionando para home");
            $this->redirect('/');
            exit;
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        error_log("Renderizando formulário de login" . ($error ? " com erro: $error" : ""));

        // Include login view
        include BASE_PATH . '/resources/views/auth/login.php';
    }

    public function processLogin() {
        // Verifica o token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro de validação. Tente novamente.'
                ]);
                exit;
            } else {
                $_SESSION['login_error'] = 'Erro de validação. Tente novamente.';
                $this->redirect('/login');
                exit;
            }
        }
        error_log("Método processLogin chamado");

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        error_log("Tentativa de login para usuário: $username");

        $result = $this->authService->authenticate($username, $password);

        // Detecta se é uma requisição AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($result['success']) {
            error_log("Autenticação bem-sucedida para: $username");

            // Set session variables
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_name'] = $result['user']['user'];
            $_SESSION['user_role'] = $result['user']['access_level'];
            $_SESSION['userlogged'] = true; // Para compatibilidade com função isLoggedIn()
            $_SESSION['lvl'] = $result['user']['access_level']; // Para compatibilidade com função hasRole()

            // Determina destino com base no papel
            $redirectPath = '/';
            switch ($result['user']['access_level']) {
                case 'admin':
                    $redirectPath = '/admin';
                    break;
                case 'supervisor':
                    $redirectPath = '/supervisor';
                    break;
            }

            if ($isAjax) {
                // Resposta JSON para AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'redirect' => $this->basePath . $redirectPath
                ]);
                exit;
            } else {
                // Redirecionamento tradicional
                $this->redirect($redirectPath);
            }
        } else {
            error_log("Falha na autenticação: " . $result['message']);

            if ($isAjax) {
                // Erro em formato JSON para AJAX
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $result['message']
                ]);
                exit;
            } else {
                // Store error and redirect back to login
                $_SESSION['login_error'] = $result['message'];
                $this->redirect('/login');
            }
        }
    }

    public function logout() {
        error_log("Método logout chamado");

        // Clear session data
        session_unset();
        session_destroy();

        // Redirect to login page
        $this->redirect('/login');
        exit;
    }
}