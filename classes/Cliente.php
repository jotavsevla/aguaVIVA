<?php
class Cliente {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function criar($dados) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO clientes (nome, telefone, endereco, email)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $dados['nome'],
                $dados['telefone'],
                $dados['endereco'],
                $dados['email'] ?? null
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar cliente: " . $e->getMessage());
        }
    }

    public function buscar($id) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}