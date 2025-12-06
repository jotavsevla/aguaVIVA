<?php
// app/Models/Entregador.php
namespace app\Models;

use PDO;

class Entregador {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Busca todos os entregadores
     * @return array
     */
    public function findAll() {
        $stmt = $this->conn->query("
            SELECT id, nome, telefone, veiculo, placa, status,
                   latitude_atual, longitude_atual, updated_at
            FROM entregadores
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca entregador por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("
            SELECT id, nome, telefone, veiculo, placa, status,
                   latitude_atual, longitude_atual
            FROM entregadores
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca entregadores disponiveis
     * @return array
     */
    public function findDisponiveis() {
        $stmt = $this->conn->query("
            SELECT id, nome, telefone, veiculo, placa,
                   latitude_atual, longitude_atual
            FROM entregadores
            WHERE status = 'disponivel'
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta entregadores por status
     * @return array
     */
    public function countByStatus() {
        $stmt = $this->conn->query("
            SELECT status, COUNT(*) as total
            FROM entregadores
            GROUP BY status
        ");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'disponivel' => 0,
            'em_entrega' => 0,
            'indisponivel' => 0
        ];

        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Insere um novo entregador
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO entregadores (nome, telefone, veiculo, placa, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['nome'],
            $data['telefone'],
            $data['veiculo'] ?? null,
            $data['placa'] ?? null,
            $data['status'] ?? 'indisponivel'
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza um entregador existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE entregadores
            SET nome = ?, telefone = ?, veiculo = ?, placa = ?, status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nome'],
            $data['telefone'],
            $data['veiculo'] ?? null,
            $data['placa'] ?? null,
            $data['status'],
            $id
        ]);
    }

    /**
     * Atualiza apenas o status do entregador
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("
            UPDATE entregadores
            SET status = ?
            WHERE id = ?
        ");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Atualiza localizacao do entregador
     * @param int $id
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    public function updateLocation($id, $lat, $lng) {
        $stmt = $this->conn->prepare("
            UPDATE entregadores
            SET latitude_atual = ?, longitude_atual = ?
            WHERE id = ?
        ");
        return $stmt->execute([$lat, $lng, $id]);
    }

    /**
     * Remove um entregador
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM entregadores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
