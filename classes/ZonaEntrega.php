<?php

namespace classes;

class ZonaEntrega {
    public $id;
    public $descricao;

    // Construtor
    public function __construct($id = null, $descricao = '') {
        $this->id = $id;
        $this->descricao = $descricao;
    }
}
