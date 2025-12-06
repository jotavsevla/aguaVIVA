<?php
/**
 * Script para criar dados de demonstração
 * Popula o banco com dados básicos para o MVP
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
echo "CRIANDO DADOS DE DEMONSTRAÇÃO\n";
echo "========================================\n\n";

try {
    $conn = \src\Database\Connection::getInstance();

    // Create models
    $clienteModel = new \app\Models\Cliente($conn);
    $entregadorModel = new \app\Models\Entregador($conn);
    $entregaModel = new \app\Models\Entrega($conn);

    // Dados de clientes para teste
    $clientes = [
        [
            'nome' => 'João Silva',
            'telefone' => '(11) 98765-4321',
            'endereco' => 'Rua das Flores, 123 - Centro',
            'latitude' => -23.5505,
            'longitude' => -46.6333
        ],
        [
            'nome' => 'Maria Santos',
            'telefone' => '(11) 97654-3210',
            'endereco' => 'Av. Paulista, 1000 - Bela Vista',
            'latitude' => -23.5615,
            'longitude' => -46.6562
        ],
        [
            'nome' => 'Carlos Oliveira',
            'telefone' => '(11) 96543-2109',
            'endereco' => 'Rua Augusta, 500 - Consolação',
            'latitude' => -23.5550,
            'longitude' => -46.6620
        ]
    ];

    echo "1. Criando clientes...\n";
    $clienteIds = [];
    foreach ($clientes as $cliente) {
        $id = $clienteModel->create($cliente);
        if ($id) {
            $clienteIds[] = $id;
            echo "   ✓ Cliente '{$cliente['nome']}' criado (ID: $id)\n";
        }
    }
    echo "\n";

    // Verificar se já existem entregadores
    $entregadoresExistentes = $entregadorModel->findAll();
    echo "2. Verificando entregadores...\n";
    echo "   - Entregadores existentes: " . count($entregadoresExistentes) . "\n";

    // Se não existirem, criar alguns
    if (count($entregadoresExistentes) == 0) {
        $entregadores = [
            [
                'nome' => 'Pedro Entregador',
                'telefone' => '(11) 95432-1098',
                'veiculo' => 'Moto Honda CG 160',
                'placa' => 'ABC-1234',
                'status' => 'disponivel'
            ],
            [
                'nome' => 'Ana Entregadora',
                'telefone' => '(11) 94321-0987',
                'veiculo' => 'Moto Yamaha Factor',
                'placa' => 'DEF-5678',
                'status' => 'disponivel'
            ]
        ];

        foreach ($entregadores as $entregador) {
            $id = $entregadorModel->create($entregador);
            if ($id) {
                echo "   ✓ Entregador '{$entregador['nome']}' criado (ID: $id)\n";
            }
        }
    } else {
        echo "   ✓ Entregadores já existem, pulando criação\n";
    }
    echo "\n";

    // Criar algumas entregas de exemplo
    echo "3. Criando entregas de exemplo...\n";
    foreach ($clienteIds as $clienteId) {
        $entrega = [
            'cliente_id' => $clienteId,
            'endereco_entrega' => 'Endereço do cliente ' . $clienteId,
            'observacoes' => 'Entrega de 2 garrafões de 20L'
        ];

        $id = $entregaModel->create($entrega);
        if ($id) {
            echo "   ✓ Entrega criada para cliente ID $clienteId (Entrega ID: $id)\n";
        }
    }
    echo "\n";

    echo "========================================\n";
    echo "DADOS CRIADOS COM SUCESSO!\n";
    echo "========================================\n\n";

    // Mostrar resumo
    $totalClientes = count($clienteModel->findAll());
    $entregadoresCounts = $entregadorModel->countByStatus();
    $entregasCounts = $entregaModel->countByStatus();

    echo "RESUMO DO SISTEMA:\n";
    echo "- Total de clientes: $totalClientes\n";
    echo "- Entregadores disponíveis: {$entregadoresCounts['disponivel']}\n";
    echo "- Entregas pendentes: {$entregasCounts['pendente']}\n";
    echo "\n";

} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
