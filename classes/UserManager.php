<?php
/**
 * Classe responsável pelo gerenciamento de usuários
 */
class UserManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Busca todos os usuários
     * @return array
     */
    public function getUsers() {
        try {
            $stmt = $this->conn->query("
                SELECT id, user, access_level, created_at, last_login 
                FROM users 
                ORDER BY user
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuários: " . $e->getMessage());
        }
    }

    /**
     * Cria um novo usuário
     * @param string $username
     * @param string $password
     * @param string $access_level
     * @return bool
     */
    public function createUser($username, $password, $access_level = 'admin') {
        try {
            // Verificar se o usuário atual é supervisor
            if (!hasRole(ROLE_SUPERVISOR)) {
                throw new Exception("Apenas supervisores podem criar usuários.");
            }

            // Não permitir criar supervisores
            if ($access_level === ROLE_SUPERVISOR) {
                throw new Exception("Não é permitido criar outros supervisores.");
            }

            // Verificar se usuário já existe
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE user = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception("Este nome de usuário já está em uso.");
            }

            // Inserir novo usuário
            $stmt = $this->conn->prepare("
                INSERT INTO users (user, password, access_level, created_at) 
                VALUES (?, ?, ?, NOW())
            ");

            return $stmt->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $access_level
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    /**
     * Atualiza um usuário existente
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUser($userId, $data) {
        try {
            $allowedFields = ['user', 'access_level', 'active'];
            $updates = [];
            $values = [];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($updates)) {
                throw new Exception("Nenhum campo válido para atualização.");
            }

            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";

            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar usuário: " . $e->getMessage());
        }
    }

    /**
     * Remove um usuário
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId) {
        try {
            // Não permitir deletar o próprio usuário
            if ($userId == $_SESSION['userlogged']) {
                throw new Exception("Você não pode deletar seu próprio usuário.");
            }

            // Verificar se é supervisor
            $stmt = $this->conn->prepare("SELECT access_level FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['access_level'] === ROLE_SUPERVISOR) {
                throw new Exception("Não é permitido deletar supervisores.");
            }

            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar usuário: " . $e->getMessage());
        }
    }

    /**
     * Busca um usuário específico
     * @param int $userId
     * @return array|false
     */
    public function getUser($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id, user, access_level, created_at, last_login 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        }
    }
}
