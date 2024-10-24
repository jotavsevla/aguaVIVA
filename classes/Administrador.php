<?php
class Administrador {
    private $conn;
    private $id;
    private $user;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function criarPedido($clienteId, $itens) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("
                INSERT INTO pedidos (cliente_id, admin_id, status, data_criacao)
                VALUES (?, ?, 'pendente', NOW())
            ");
            $stmt->execute([$clienteId, $_SESSION['userlogged']]);

            $pedidoId = $this->conn->lastInsertId();

            // Inserir itens do pedido
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

    public function buscarCliente($termo) {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM clientes
            WHERE nome LIKE ? OR telefone LIKE ?
        ");
        $termo = "%$termo%";
        $stmt->execute([$termo, $termo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verZonasEntrega() {
        $stmt = $this->conn->query("
            SELECT *
            FROM zonas_entrega
            WHERE ativo = 1
            ORDER BY nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}