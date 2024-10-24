<?php
class ZonaEntrega {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function listar() {
        $stmt = $this->conn->query("
            SELECT *
            FROM zonas_entrega
            WHERE ativo = 1
            ORDER BY nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}