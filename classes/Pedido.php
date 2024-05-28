<?php

namespace classes;

class Pedido {
    public $id;
    public $dataPedido;
    public $quantidadeAgua;
    public $statusPedido;
    public $clienteId; // Chave estrangeira
    public $entregadorId; // Chave estrangeira

    // Construtor
    public function __construct($id = null, $dataPedido = '', $quantidadeAgua = 0, $statusPedido = '', $clienteId = null, $entregadorId = null) {
        $this->id = $id;
        $this->dataPedido = $dataPedido;
        $this->quantidadeAgua = $quantidadeAgua;
        $this->statusPedido = $statusPedido;
        $this->clienteId = $clienteId;
        $this->entregadorId = $entregadorId;
    }
}