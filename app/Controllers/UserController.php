<?php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Models\UserAdmin;
use Src\Database\Connection;

class UserController {
    private $userModel;
    private $basePath;

    public function __construct() {
        $conn = Connection::getInstance();
        $this->userModel = new UserAdmin($conn);

        // Detecta caminho base
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
     * Lista todos os usuários
     */
    public function index() {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        $users = $this->userModel->findAll();

        // Inclui a view de listagem
        include BASE_PATH . '/resources/views/supervisor/users/index.php';
    }

    /**
     * Exibe formulário para criar usuário
     */
    public function create() {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        // Inclui a view do formulário
        include BASE_PATH . '/resources/views/supervisor/users/create.php';
    }

    /**
     * Processa a criação de um usuário
     */
    public function store() {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/supervisor/users/create');
            exit;
        }

        // Validação básica
        $required = ['username', 'password', 'confirm_password', 'access_level'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst(str_replace('_', ' ', $field)) . " é obrigatório.";
            }
        }

        // Validação de senha
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "As senhas não coincidem.";
        }

        // Validação de nível de acesso
        $allowedAccessLevels = ['admin', 'supervisor'];
        if (!in_array($_POST['access_level'], $allowedAccessLevels)) {
            $errors[] = "Nível de acesso inválido.";
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/supervisor/users/create');
            exit;
        }

        // Criar o usuário
        $result = $this->userModel->create([
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'access_level' => $_POST['access_level']
        ]);

        if ($result) {
            $_SESSION['flash']['success'] = 'Usuário cadastrado com sucesso!';
            $this->redirect('/supervisor/users');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao cadastrar usuário. Verifique se o nome de usuário já existe.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/supervisor/users/create');
        }
    }

    /**
     * Exibe formulário para editar usuário
     */
    public function edit($id) {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        $user = $this->userModel->findById($id);

        if (!$user) {
            $_SESSION['flash']['error'] = 'Usuário não encontrado.';
            $this->redirect('/supervisor/users');
            exit;
        }

        // Inclui a view do formulário de edição
        include BASE_PATH . '/resources/views/supervisor/users/edit.php';
    }

    /**
     * Processa a atualização de um usuário
     */
    public function update($id) {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/supervisor/users/edit/' . $id);
            exit;
        }

        // Validação básica
        $required = ['username', 'access_level'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst(str_replace('_', ' ', $field)) . " é obrigatório.";
            }
        }

        // Validação de senha (opcional na edição)
        if (!empty($_POST['password']) && $_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "As senhas não coincidem.";
        }

        // Validação de nível de acesso
        $allowedAccessLevels = ['admin', 'supervisor'];
        if (!in_array($_POST['access_level'], $allowedAccessLevels)) {
            $errors[] = "Nível de acesso inválido.";
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/supervisor/users/edit/' . $id);
            exit;
        }

        // Preparar dados para atualização
        $userData = [
            'username' => $_POST['username'],
            'access_level' => $_POST['access_level']
        ];

        // Adicionar senha apenas se fornecida
        if (!empty($_POST['password'])) {
            $userData['password'] = $_POST['password'];
        }

        // Atualizar o usuário
        $result = $this->userModel->update($id, $userData);

        if ($result) {
            $_SESSION['flash']['success'] = 'Usuário atualizado com sucesso!';
            $this->redirect('/supervisor/users');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao atualizar usuário. Verifique se o nome de usuário já existe.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/supervisor/users/edit/' . $id);
        }
    }

    /**
     * Exclui um usuário
     */
    public function delete($id) {
        // Verifica se usuário está logado como supervisor
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'supervisor') {
            $this->redirect('/login');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validação. Tente novamente.';
            $this->redirect('/supervisor/users');
            exit;
        }

        $user = $this->userModel->findById($id);

        if (!$user) {
            $_SESSION['flash']['error'] = 'Usuário não encontrado.';
            $this->redirect('/supervisor/users');
            exit;
        }

        // Executar exclusão
        $result = $this->userModel->delete($id);

        if ($result) {
            $_SESSION['flash']['success'] = 'Usuário excluído com sucesso!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao excluir usuário.';
        }

        $this->redirect('/supervisor/users');
    }
}