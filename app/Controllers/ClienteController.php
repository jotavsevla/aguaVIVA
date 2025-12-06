<?php
// app/Controllers/ClienteController.php
namespace app\Controllers;

use app\Models\Cliente;
use src\Database\Connection;

class ClienteController {
    private $clienteModel;
    private $basePath;

    public function __construct() {
        $conn = Connection::getInstance();
        $this->clienteModel = new Cliente($conn);

        $this->basePath = '';
        if (php_sapi_name() != 'cli-server') {
            $this->basePath = '/aguaVIVA';
        }
    }

    // Função auxiliar para redirecionamento
    private function redirect($path) {
        $fullPath = $this->basePath . $path;
        header("Location: $fullPath");
        exit;
    }

    /**
     * Lista todos os clientes
     */
    public function index() {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $clientes = $this->clienteModel->findAll();

        // Inclui a view de listagem
        include BASE_PATH . '/resources/views/admin/clientes/index.php';
    }

    /**
     * Exibe formulário para criar cliente
     */
    public function create() {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        // Inclui a view do formulário
        include BASE_PATH . '/resources/views/admin/clientes/create.php';
    }

    /**
     * Processa a criação de um cliente
     */
    public function store() {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/admin/clientes/create');
            exit;
        }

        // Validação básica
        $required = ['nome', 'cpf', 'telefone', 'endereco'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst($field) . " é obrigatório.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/clientes/create');
            exit;
        }

        // Criar o cliente
        $result = $this->clienteModel->create([
            'nome' => $_POST['nome'],
            'cpf' => $_POST['cpf'],
            'telefone' => $_POST['telefone'],
            'telefone2' => $_POST['telefone2'] ?? null,
            'endereco' => $_POST['endereco']
        ]);

        if ($result) {
            $_SESSION['flash']['success'] = 'Cliente cadastrado com sucesso!';
            $this->redirect('/admin/clientes');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao cadastrar cliente.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/clientes/create');
        }
    }

    /**
     * Exibe formulário para editar cliente
     */
    public function edit($id) {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            $_SESSION['flash']['error'] = 'Cliente não encontrado.';
            $this->redirect('/admin/clientes');
            exit;
        }

        // Inclui a view do formulário de edição
        include BASE_PATH . '/resources/views/admin/clientes/edit.php';
    }

    /**
     * Processa a atualização de um cliente
     */
    public function update($id) {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/admin/clientes/edit/' . $id);
            exit;
        }

        // Validação básica
        $required = ['nome', 'cpf', 'telefone', 'endereco'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst($field) . " é obrigatório.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/clientes/edit/' . $id);
            exit;
        }

        // Atualizar o cliente
        $result = $this->clienteModel->update($id, [
            'nome' => $_POST['nome'],
            'cpf' => $_POST['cpf'],
            'telefone' => $_POST['telefone'],
            'telefone2' => $_POST['telefone2'] ?? null,
            'endereco' => $_POST['endereco']
        ]);

        if ($result) {
            $_SESSION['flash']['success'] = 'Cliente atualizado com sucesso!';
            $this->redirect('/admin/clientes');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao atualizar cliente.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/clientes/edit/' . $id);
        }
    }

    /**
     * Remove um cliente
     */
    public function delete($id) {
        // Verifica se usuário está logado como admin
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/admin/clientes');
            exit;
        }

        $result = $this->clienteModel->delete($id);

        if ($result) {
            $_SESSION['flash']['success'] = 'Cliente removido com sucesso!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao remover cliente.';
        }

        $this->redirect('/admin/clientes');
    }
}