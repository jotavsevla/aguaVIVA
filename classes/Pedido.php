<?php
class Pedido {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function criar($clienteId, $adminId, $itens) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO pedidos (cliente_id, admin_id, status, data_criacao)
                VALUES (?, ?, 'pendente', NOW())
            ");
            $stmt->execute([$clienteId, $adminId]);

            $pedidoId = $this->conn->lastInsertId();

            foreach ($itens as $item) {
                $stmtItem = $this->conn->prepare("
                    INSERT INTO pedido_itens (pedido_id, produto_id, quantidade)
                    VALUES (?, ?, ?)
                ");
                $stmtItem->execute([$pedidoId, $item['produto_id'], $item['quantidade']]);
            }

            $this->conn->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao criar pedido: " . $e->getMessage());
        }
    }

    public function buscar($id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.nome as cliente_nome, u.user as admin_nome
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN users u ON p.admin_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}