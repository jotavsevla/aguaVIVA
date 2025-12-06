<?php
// app/Controllers/EntregadorController.php
namespace app\Controllers;

use app\Models\Entregador;
use src\Database\Connection;

class EntregadorController {
    private $entregadorModel;
    private $basePath;

    public function __construct() {
        $conn = Connection::getInstance();
        $this->entregadorModel = new Entregador($conn);

        $this->basePath = '';
        if (php_sapi_name() != 'cli-server') {
            $this->basePath = '/aguaVIVA';
        }
    }

    private function redirect($path) {
        $fullPath = $this->basePath . $path;
        header("Location: $fullPath");
        exit;
    }

    /**
     * Lista todos os entregadores
     */
    public function index() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $entregadores = $this->entregadorModel->findAll();
        $counts = $this->entregadorModel->countByStatus();

        include BASE_PATH . '/resources/views/admin/entregadores/index.php';
    }

    /**
     * Exibe formulario para criar entregador
     */
    public function create() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        include BASE_PATH . '/resources/views/admin/entregadores/create.php';
    }

    /**
     * Processa a criacao de um entregador
     */
    public function store() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao. Tente novamente.';
            $this->redirect('/admin/entregadores/create');
            exit;
        }

        $required = ['nome', 'telefone'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst($field) . " e obrigatorio.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregadores/create');
            exit;
        }

        $result = $this->entregadorModel->create([
            'nome' => $_POST['nome'],
            'telefone' => $_POST['telefone'],
            'veiculo' => $_POST['veiculo'] ?? null,
            'placa' => $_POST['placa'] ?? null,
            'status' => $_POST['status'] ?? 'indisponivel'
        ]);

        if ($result) {
            $_SESSION['flash']['success'] = 'Entregador cadastrado com sucesso!';
            $this->redirect('/admin/entregadores');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao cadastrar entregador.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregadores/create');
        }
    }

    /**
     * Exibe formulario para editar entregador
     */
    public function edit($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $entregador = $this->entregadorModel->findById($id);

        if (!$entregador) {
            $_SESSION['flash']['error'] = 'Entregador nao encontrado.';
            $this->redirect('/admin/entregadores');
            exit;
        }

        include BASE_PATH . '/resources/views/admin/entregadores/edit.php';
    }

    /**
     * Processa a atualizacao de um entregador
     */
    public function update($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao. Tente novamente.';
            $this->redirect('/admin/entregadores/edit/' . $id);
            exit;
        }

        $required = ['nome', 'telefone', 'status'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst($field) . " e obrigatorio.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregadores/edit/' . $id);
            exit;
        }

        $result = $this->entregadorModel->update($id, [
            'nome' => $_POST['nome'],
            'telefone' => $_POST['telefone'],
            'veiculo' => $_POST['veiculo'] ?? null,
            'placa' => $_POST['placa'] ?? null,
            'status' => $_POST['status']
        ]);

        if ($result) {
            $_SESSION['flash']['success'] = 'Entregador atualizado com sucesso!';
            $this->redirect('/admin/entregadores');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao atualizar entregador.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregadores/edit/' . $id);
        }
    }

    /**
     * Atualiza apenas o status (via AJAX)
     */
    public function updateStatus($id) {
        header('Content-Type: application/json');

        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nao autorizado']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? null;

        if (!in_array($status, ['disponivel', 'em_entrega', 'indisponivel'])) {
            echo json_encode(['success' => false, 'message' => 'Status invalido']);
            exit;
        }

        $result = $this->entregadorModel->updateStatus($id, $status);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
        exit;
    }

    /**
     * Remove um entregador
     */
    public function delete($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao. Tente novamente.';
            $this->redirect('/admin/entregadores');
            exit;
        }

        $result = $this->entregadorModel->delete($id);

        if ($result) {
            $_SESSION['flash']['success'] = 'Entregador removido com sucesso!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao remover entregador.';
        }

        $this->redirect('/admin/entregadores');
    }

    /**
     * Retorna entregadores disponiveis (API)
     */
    public function apiDisponiveis() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['userlogged'])) {
            echo json_encode(['success' => false, 'message' => 'Nao autorizado']);
            exit;
        }

        $entregadores = $this->entregadorModel->findDisponiveis();
        echo json_encode(['success' => true, 'data' => $entregadores]);
        exit;
    }
}
