<?php
// app/Services/AuthService.php
namespace App\Services;

use App\Models\User;
use Src\Database\Connection;

class AuthService {
    public function authenticate($username, $password) {
        error_log("AuthService::authenticate chamado para usuário: $username");

        if (empty($username) || empty($password)) {
            error_log("Login falhou: Usuário ou senha vazios");
            return [
                'success' => false,
                'message' => 'Username and password are required'
            ];
        }

        try {
            // Get database connection usando o Singleton
            $conn = Connection::getInstance();
            error_log("Conexão com banco obtida com sucesso");

            // Create user model
            $userModel = new User($conn);
            error_log("Modelo User instanciado");

            // Find user by username
            $user = $userModel->findByUsername($username);

            if (!$user) {
                error_log("Login falhou: Usuário não encontrado: $username");
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }

            error_log("Usuário encontrado no banco, verificando senha");

            // Para depuração - NÃO use em produção
            error_log("Senha armazenada (hash): " . $user['password']);

            // Verify password
            if (!password_verify($password, $user['password'])) {
                error_log("Login falhou: Senha incorreta para usuário: $username");

                // Para situações de emergência, adicione verificação de senha em texto claro
                // REMOVA EM PRODUÇÃO, isto é apenas para depuração
                if ($password === $user['password']) {
                    error_log("ATENÇÃO: Senha em texto plano corresponde, mas hash não");
                    // Usuário encontrado e senha corresponde em texto plano
                    $userModel->updateLastLogin($user['id']);
                    error_log("Login com senha em texto plano bem-sucedido: $username (ID: {$user['id']})");
                    return [
                        'success' => true,
                        'user' => $user
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }

            // Update last login timestamp
            $userModel->updateLastLogin($user['id']);

            error_log("Login bem-sucedido: $username (ID: {$user['id']})");

            return [
                'success' => true,
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Erro de autenticação: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'An error occurred during authentication. Please try again later.'
            ];
        }
    }
}