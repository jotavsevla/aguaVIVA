<?php
namespace App\Services;

use App\Models\User;
use Src\Database\Connection;

class AuthService {
    private $maxLoginAttempts = 5;
    private $loginLockoutTime = 15; // minutos

    public function authenticate($username, $password) {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Usuário e senha são obrigatórios'
            ];
        }

        // Implementar verificação de tentativas de login
        if ($this->isIpBlocked()) {
            return [
                'success' => false,
                'message' => 'Muitas tentativas de login. Tente novamente mais tarde.'
            ];
        }

        try {
            $conn = Connection::getInstance();
            $userModel = new User($conn);
            $user = $userModel->findByUsername($username);

            if (!$user) {
                $this->recordFailedAttempt();
                return [
                    'success' => false,
                    'message' => 'Usuário ou senha inválidos'
                ];
            }

            // Usar APENAS password_verify para verificação
            if (!password_verify($password, $user['password'])) {
                $this->recordFailedAttempt();
                return [
                    'success' => false,
                    'message' => 'Usuário ou senha inválidos'
                ];
            }

            // Limpa tentativas em caso de sucesso
            $this->clearLoginAttempts();
            $userModel->updateLastLogin($user['id']);

            return [
                'success' => true,
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Erro de autenticação: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ocorreu um erro durante a autenticação. Tente novamente mais tarde.'
            ];
        }
    }

    private function recordFailedAttempt() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $_SESSION['login_attempts'][$ip] = ($_SESSION['login_attempts'][$ip] ?? 0) + 1;
        $_SESSION['login_time'][$ip] = time();
    }

    private function isIpBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!isset($_SESSION['login_attempts'][$ip])) {
            return false;
        }

        if ($_SESSION['login_attempts'][$ip] >= $this->maxLoginAttempts) {
            $blockTime = $_SESSION['login_time'][$ip] + ($this->loginLockoutTime * 60);
            if (time() < $blockTime) {
                return true;
            }
            // Reset após período de bloqueio
            $this->clearLoginAttempts();
        }
        return false;
    }

    private function clearLoginAttempts() {
        $ip = $_SERVER['REMOTE_ADDR'];
        unset($_SESSION['login_attempts'][$ip]);
        unset($_SESSION['login_time'][$ip]);
    }
}