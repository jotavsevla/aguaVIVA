<?php
// app/Models/UserAdmin.php
namespace app\Models;

use PDO;

class UserAdmin {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Busca todos os usuários
     * @return array
     */
    public function findAll() {
        $stmt = $this->conn->query("
            SELECT id, user as username, access_level, last_login
            FROM users
            ORDER BY user ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuário por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("
            SELECT id, user as username, access_level, last_login
            FROM users
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se um nome de usuário já existe
     * @param string $username
     * @param int|null $excludeId ID do usuário a ser excluído da verificação (para edição)
     * @return bool
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE user = ?";
        $params = [$username];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Cria um novo usuário
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        // Verificar se username já existe
        if ($this->usernameExists($data['username'])) {
            return false;
        }

        // Hash da senha
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO users (user, password, access_level)
            VALUES (?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['access_level']
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza um usuário existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Verificar se username já existe (excluindo o usuário atual)
        if (isset($data['username']) && $this->usernameExists($data['username'], $id)) {
            return false;
        }

        // Base da query de atualização
        $sql = "UPDATE users SET ";
        $params = [];
        $fields = [];

        // Campos que podem ser atualizados
        if (isset($data['username'])) {
            $fields[] = "user = ?";
            $params[] = $data['username'];
        }

        if (isset($data['access_level'])) {
            $fields[] = "access_level = ?";
            $params[] = $data['access_level'];
        }

        // Se uma senha foi fornecida, fazer hash e atualizar
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Se não há campos para atualizar, retorna falso
        if (empty($fields)) {
            return false;
        }

        // Completar a query
        $sql .= implode(', ', $fields) . " WHERE id = ?";
        $params[] = $id;

        // Executar a atualização
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Altera a senha de um usuário
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
        ");

        return $stmt->execute([$hashedPassword, $id]);
    }

    /**
     * Remove um usuário
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}