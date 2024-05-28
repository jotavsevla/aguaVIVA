<?php

namespace classes;

date_default_timezone_set('America/Sao Paulo');

class Cliente {

    public $id;
    public $nome;
    public $endereco;
    public $telefone;
    public $telefone2;
    public $numeroFidelidade;
    public static $novoNumeroFidelidade = 0;

    // Método para inicializar o número de fidelidade
    public static function inicializarNumeroFidelidade() {
        $anoAtual = date('Y');
        self::$novoNumeroFidelidade = $anoAtual * 100000 + 1;
    }

    // Construtor
    public function __construct($id = null, $nome = '', $endereco = '', $telefone = '', $telefone2 = '') {
        $this->id = $id;
        $this->nome = $nome;
        $this->endereco = $endereco;
        $this->telefone = $telefone;
        $this->telefone2 = $telefone2;
        $this->numeroFidelidade = self::$novoNumeroFidelidade++;
    }

    public function getNumeroTelefone() {
        return $this->telefone;
    }
    public function getNumeroTelefone2() {
        return $this->telefone2;
    }
    public function setNumeroTelefone($telefone2) {
        $this->telefone2 = $telefone2;
    }

    public function setNumeroTelefone2($telefone2) {
        $this->telefone2 = $telefone2;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function getNumeroFidelidade() {
        return $this->numeroFidelidade;
    }

}

// Chamada para inicializar o número de fidelidade
Cliente::inicializarNumeroFidelidade();

// Exemplo de criação de um novo cliente
$cliente = new Cliente(null, 'João Silva', 'Rua das Flores, 123', '11987654321', '21987654321');
echo "ID: {$cliente->id}\n";
echo "Nome: {$cliente->nome}\n";
echo "Endereço: {$cliente->endereco}\n";
echo "Telefone: {$cliente->telefone}\n";
echo "Telefone 2: {$cliente->telefone2}\n";
echo "Número de Fidelidade: {$cliente->numeroFidelidade}\n";
