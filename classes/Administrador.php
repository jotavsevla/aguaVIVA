<?php

namespace classes;

class Administrador {
    public $id;
    public $usuario;
    private $senha; // Alterado para privado para restringir o acesso direto
    public $nivelAcesso;

    // Construtor
    public function __construct($id = null, $usuario = '', $senha = '', $nivelAcesso = '') {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->setSenha($senha); // Utiliza um método setter para definir a senha já hashida
        $this->nivelAcesso = $nivelAcesso;
    }

    // Método para definir a senha
    public function setSenha($senha) {
        $this->senha = password_hash($senha, PASSWORD_DEFAULT);
    }

    // Método para verificar a senha
    public function verificaSenha($senha) {
        return password_verify($senha, $this->senha);
    }
}
