<?php
// app/Controllers/EntregaController.php
namespace app\Controllers;

use app\Models\Entrega;
use app\Models\Entregador;
use app\Models\Cliente;
use app\Services\AtribuicaoService;
use src\Database\Connection;

class EntregaController {
    private $entregaModel;
    private $entregadorModel;
    private $clienteModel;
    private $basePath;

    public function __construct() {
        $conn = Connection::getInstance();
        $this->entregaModel = new Entrega($conn);
        $this->entregadorModel = new Entregador($conn);
        $this->clienteModel = new Cliente($conn);

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
     * Lista todas as entregas
     */
    public function index() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $status = $_GET['status'] ?? null;
        $entregas = $this->entregaModel->findAll($status);
        $counts = $this->entregaModel->countByStatus();
        $entregadoresDisponiveis = $this->entregadorModel->findDisponiveis();

        include BASE_PATH . '/resources/views/admin/entregas/index.php';
    }

    /**
     * Exibe formulario para nova entrega
     */
    public function create() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $clientes = $this->clienteModel->findAll();

        include BASE_PATH . '/resources/views/admin/entregas/create.php';
    }

    /**
     * Processa criacao de entrega
     */
    public function store() {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao. Tente novamente.';
            $this->redirect('/admin/entregas/create');
            exit;
        }

        $required = ['cliente_id', 'endereco_entrega'];
        $errors = [];

        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "O campo " . ucfirst(str_replace('_', ' ', $field)) . " e obrigatorio.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregas/create');
            exit;
        }

        $result = $this->entregaModel->create([
            'cliente_id' => $_POST['cliente_id'],
            'endereco_entrega' => $_POST['endereco_entrega'],
            'observacoes' => $_POST['observacoes'] ?? null
        ]);

        if ($result) {
            // Tenta atribuir automaticamente se solicitado
            if (!empty($_POST['atribuir_automatico'])) {
                $this->tentarAtribuicaoAutomatica($result);
            }

            $_SESSION['flash']['success'] = 'Entrega cadastrada com sucesso!';
            $this->redirect('/admin/entregas');
        } else {
            $_SESSION['flash']['error'] = 'Erro ao cadastrar entrega.';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/entregas/create');
        }
    }

    /**
     * Atribui entrega manualmente
     */
    public function atribuir($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao.';
            $this->redirect('/admin/entregas');
            exit;
        }

        $entregadorId = $_POST['entregador_id'] ?? null;

        if (!$entregadorId) {
            $_SESSION['flash']['error'] = 'Selecione um entregador.';
            $this->redirect('/admin/entregas');
            exit;
        }

        // Atribui a entrega
        $result = $this->entregaModel->atribuir($id, $entregadorId);

        if ($result) {
            // Atualiza status do entregador para "em_entrega"
            $this->entregadorModel->updateStatus($entregadorId, 'em_entrega');

            $_SESSION['flash']['success'] = 'Entrega atribuida com sucesso!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao atribuir entrega.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * Atribuicao automatica - seleciona primeiro entregador disponivel
     */
    public function atribuirAutomatico($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao.';
            $this->redirect('/admin/entregas');
            exit;
        }

        $sucesso = $this->tentarAtribuicaoAutomatica($id);

        if ($sucesso) {
            $_SESSION['flash']['success'] = 'Entrega atribuida automaticamente!';
        } else {
            $_SESSION['flash']['error'] = 'Nenhum entregador disponivel no momento.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * Tenta atribuir uma entrega automaticamente
     * @param int $entregaId
     * @return bool
     */
    private function tentarAtribuicaoAutomatica($entregaId) {
        $disponiveis = $this->entregadorModel->findDisponiveis();

        if (empty($disponiveis)) {
            return false;
        }

        // Pega o primeiro disponivel (poderia ser mais inteligente no futuro)
        $entregador = $disponiveis[0];

        $result = $this->entregaModel->atribuir($entregaId, $entregador['id']);

        if ($result) {
            $this->entregadorModel->updateStatus($entregador['id'], 'em_entrega');
            return true;
        }

        return false;
    }

    /**
     * Inicia uma entrega
     */
    public function iniciar($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $result = $this->entregaModel->iniciar($id);

        if ($result) {
            $_SESSION['flash']['success'] = 'Entrega iniciada!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao iniciar entrega.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * Conclui uma entrega
     */
    public function concluir($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $entrega = $this->entregaModel->findById($id);

        if (!$entrega) {
            $_SESSION['flash']['error'] = 'Entrega nao encontrada.';
            $this->redirect('/admin/entregas');
            exit;
        }

        $result = $this->entregaModel->concluir($id);

        if ($result) {
            // Verifica se o entregador tem outras entregas pendentes
            if ($entrega['entregador_id']) {
                $entregasAtivas = $this->entregaModel->findByEntregador($entrega['entregador_id']);
                if (empty($entregasAtivas)) {
                    // Libera o entregador
                    $this->entregadorModel->updateStatus($entrega['entregador_id'], 'disponivel');
                }
            }

            $_SESSION['flash']['success'] = 'Entrega concluida com sucesso!';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao concluir entrega.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * Cancela uma entrega
     */
    public function cancelar($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        $entrega = $this->entregaModel->findById($id);

        if (!$entrega) {
            $_SESSION['flash']['error'] = 'Entrega nao encontrada.';
            $this->redirect('/admin/entregas');
            exit;
        }

        $result = $this->entregaModel->cancelar($id);

        if ($result) {
            // Libera o entregador se tinha um atribuido
            if ($entrega['entregador_id']) {
                $entregasAtivas = $this->entregaModel->findByEntregador($entrega['entregador_id']);
                if (empty($entregasAtivas)) {
                    $this->entregadorModel->updateStatus($entrega['entregador_id'], 'disponivel');
                }
            }

            $_SESSION['flash']['success'] = 'Entrega cancelada.';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao cancelar entrega.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * Remove uma entrega
     */
    public function delete($id) {
        if (!isset($_SESSION['userlogged']) || $_SESSION['lvl'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['flash']['error'] = 'Erro de validacao.';
            $this->redirect('/admin/entregas');
            exit;
        }

        $result = $this->entregaModel->delete($id);

        if ($result) {
            $_SESSION['flash']['success'] = 'Entrega removida.';
        } else {
            $_SESSION['flash']['error'] = 'Erro ao remover entrega.';
        }

        $this->redirect('/admin/entregas');
    }

    /**
     * API: Lista entregas pendentes
     */
    public function apiPendentes() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['userlogged'])) {
            echo json_encode(['success' => false, 'message' => 'Nao autorizado']);
            exit;
        }

        $entregas = $this->entregaModel->findPendentes();
        $entregadores = $this->entregadorModel->findDisponiveis();

        echo json_encode([
            'success' => true,
            'entregas' => $entregas,
            'entregadores_disponiveis' => $entregadores
        ]);
        exit;
    }
}
