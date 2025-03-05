<?php
// app/Models/Cliente.php
namespace App\Models;

use PDO;

class Cliente {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Busca todos os clientes
     * @return array
     */
    public function findAll() {
        $stmt = $this->conn->query("
            SELECT id, nome, cpf, endereco, telefone, telefone2
            FROM clientes
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca cliente por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("
            SELECT id, nome, cpf, endereco, telefone, telefone2
            FROM clientes
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo cliente
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO clientes (nome, cpf, endereco, telefone, telefone2)
            VALUES (?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['nome'],
            $data['cpf'],
            $data['endereco'],
            $data['telefone'],
            $data['telefone2'] ?? null
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza um cliente existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE clientes
            SET nome = ?, cpf = ?, endereco = ?, telefone = ?, telefone2 = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['cpf'],
            $data['endereco'],
            $data['telefone'],
            $data['telefone2'] ?? null,
            $id
        ]);
    }

    /**
     * Remove um cliente
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Busca clientes ativos
     * @return array
     */
    public function findActive() {
        $stmt = $this->conn->query("
            SELECT id, nome, email, telefone
            FROM clientes
            WHERE status = 'ativo'
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}