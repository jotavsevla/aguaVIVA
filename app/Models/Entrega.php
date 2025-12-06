<?php
// app/Models/Entrega.php
namespace app\Models;

use PDO;

class Entrega {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Busca todas as entregas com dados relacionados
     * @param string|null $status Filtrar por status
     * @return array
     */
    public function findAll($status = null) {
        $sql = "
            SELECT e.*,
                   c.nome as cliente_nome, c.telefone as cliente_telefone,
                   ent.nome as entregador_nome
            FROM entregas e
            LEFT JOIN clientes c ON e.cliente_id = c.id
            LEFT JOIN entregadores ent ON e.entregador_id = ent.id
        ";

        if ($status) {
            $sql .= " WHERE e.status = ?";
            $sql .= " ORDER BY e.data_solicitacao DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$status]);
        } else {
            $sql .= " ORDER BY e.data_solicitacao DESC";
            $stmt = $this->conn->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca entregas pendentes (sem entregador atribuido)
     * @return array
     */
    public function findPendentes() {
        $stmt = $this->conn->query("
            SELECT e.*,
                   c.nome as cliente_nome, c.telefone as cliente_telefone, c.endereco as cliente_endereco
            FROM entregas e
            LEFT JOIN clientes c ON e.cliente_id = c.id
            WHERE e.status = 'pendente'
            ORDER BY e.data_solicitacao ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca entrega por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("
            SELECT e.*,
                   c.nome as cliente_nome, c.telefone as cliente_telefone, c.endereco as cliente_endereco,
                   ent.nome as entregador_nome, ent.telefone as entregador_telefone
            FROM entregas e
            LEFT JOIN clientes c ON e.cliente_id = c.id
            LEFT JOIN entregadores ent ON e.entregador_id = ent.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Conta entregas por status
     * @return array
     */
    public function countByStatus() {
        $stmt = $this->conn->query("
            SELECT status, COUNT(*) as total
            FROM entregas
            GROUP BY status
        ");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'pendente' => 0,
            'atribuida' => 0,
            'em_andamento' => 0,
            'concluida' => 0,
            'cancelada' => 0
        ];

        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Cria nova entrega
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO entregas (
                cliente_id, endereco_entrega, latitude_destino, longitude_destino,
                observacoes, status
            ) VALUES (?, ?, ?, ?, ?, 'pendente')
        ");

        $result = $stmt->execute([
            $data['cliente_id'],
            $data['endereco_entrega'],
            $data['latitude_destino'] ?? null,
            $data['longitude_destino'] ?? null,
            $data['observacoes'] ?? null
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Atribui entrega a um entregador
     * @param int $entregaId
     * @param int $entregadorId
     * @param float|null $distancia
     * @param int|null $tempoEstimado
     * @return bool
     */
    public function atribuir($entregaId, $entregadorId, $distancia = null, $tempoEstimado = null) {
        $stmt = $this->conn->prepare("
            UPDATE entregas
            SET entregador_id = ?,
                status = 'atribuida',
                distancia_km = ?,
                tempo_estimado_min = ?,
                data_atribuicao = NOW()
            WHERE id = ? AND status = 'pendente'
        ");

        return $stmt->execute([$entregadorId, $distancia, $tempoEstimado, $entregaId]);
    }

    /**
     * Inicia uma entrega
     * @param int $id
     * @return bool
     */
    public function iniciar($id) {
        $stmt = $this->conn->prepare("
            UPDATE entregas
            SET status = 'em_andamento', data_inicio = NOW()
            WHERE id = ? AND status = 'atribuida'
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Conclui uma entrega
     * @param int $id
     * @return bool
     */
    public function concluir($id) {
        $stmt = $this->conn->prepare("
            UPDATE entregas
            SET status = 'concluida', data_conclusao = NOW()
            WHERE id = ? AND status = 'em_andamento'
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Cancela uma entrega
     * @param int $id
     * @return bool
     */
    public function cancelar($id) {
        $stmt = $this->conn->prepare("
            UPDATE entregas
            SET status = 'cancelada', entregador_id = NULL
            WHERE id = ? AND status IN ('pendente', 'atribuida')
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Atualiza dados de uma entrega
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->conn->prepare("
            UPDATE entregas
            SET cliente_id = ?,
                endereco_entrega = ?,
                observacoes = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['cliente_id'],
            $data['endereco_entrega'],
            $data['observacoes'] ?? null,
            $id
        ]);
    }

    /**
     * Remove uma entrega
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM entregas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Busca entregas ativas de um entregador
     * @param int $entregadorId
     * @return array
     */
    public function findByEntregador($entregadorId) {
        $stmt = $this->conn->prepare("
            SELECT e.*, c.nome as cliente_nome, c.endereco as cliente_endereco
            FROM entregas e
            LEFT JOIN clientes c ON e.cliente_id = c.id
            WHERE e.entregador_id = ? AND e.status IN ('atribuida', 'em_andamento')
            ORDER BY e.data_atribuicao ASC
        ");
        $stmt->execute([$entregadorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
