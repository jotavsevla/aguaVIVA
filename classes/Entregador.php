<?php

namespace classes;

class Entregador {
    public $id;
    public $nome;
    public $zonaEntregaId; // Chave estrangeira

    // Construtor
    public function __construct($id = null, $nome = '', $zonaEntregaId = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->zonaEntregaId = $zonaEntregaId;
    }
}
