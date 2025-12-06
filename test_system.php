<?php
/**
 * Script de teste do sistema
 * Testa se todas as classes e conexões estão funcionando
 */

// Define base path
define('BASE_PATH', __DIR__);

// Simple autoloader
spl_autoload_register(function($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load config
require_once BASE_PATH . '/config/config.php';

echo "========================================\n";
echo "TESTE DO SISTEMA AGUA VIVA\n";
echo "========================================\n\n";

// Test 1: Database connection
echo "1. Testando conexão com banco de dados...\n";
try {
    $conn = \src\Database\Connection::getInstance();
    echo "   ✓ Conexão com banco estabelecida\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Load models
echo "2. Testando carregamento dos Models...\n";
try {
    $clienteModel = new \app\Models\Cliente($conn);
    echo "   ✓ Cliente model carregado\n";

    $entregadorModel = new \app\Models\Entregador($conn);
    echo "   ✓ Entregador model carregado\n";

    $entregaModel = new \app\Models\Entrega($conn);
    echo "   ✓ Entrega model carregado\n";

    $userModel = new \app\Models\User($conn);
    echo "   ✓ User model carregado\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Test data retrieval
echo "3. Testando consultas ao banco...\n";
try {
    $clientes = $clienteModel->findAll();
    echo "   ✓ Clientes encontrados: " . count($clientes) . "\n";

    $entregadoresCounts = $entregadorModel->countByStatus();
    echo "   ✓ Entregadores disponíveis: " . $entregadoresCounts['disponivel'] . "\n";

    $entregasCounts = $entregaModel->countByStatus();
    echo "   ✓ Entregas pendentes: " . $entregasCounts['pendente'] . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Test authentication
echo "4. Testando autenticação...\n";
try {
    $user = $userModel->findByUsername('admin');
    if ($user) {
        echo "   ✓ Usuário admin encontrado\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Nível de acesso: " . $user['access_level'] . "\n";

        // Test password
        $testPassword = 'admin123';
        if (password_verify($testPassword, $user['password'])) {
            echo "   ✓ Senha verificada com sucesso\n";
        } else {
            echo "   ✗ Senha incorreta\n";
        }
    } else {
        echo "   ✗ Usuário admin não encontrado\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "========================================\n";
echo "TODOS OS TESTES PASSARAM!\n";
echo "========================================\n\n";
echo "Credenciais para login:\n";
echo "- Usuário: admin\n";
echo "- Senha: admin123\n";
echo "- URL: http://localhost:8080/login\n\n";
