<?php
/**
 * Script para limpar todas as sessões
 * Útil quando há problemas de redirecionamento
 */

$sessionPath = '/var/www/html/tmp/sessions';

if (!is_dir($sessionPath)) {
    $sessionPath = session_save_path();
    if (empty($sessionPath)) {
        $sessionPath = sys_get_temp_dir();
    }
}

echo "Limpando sessões em: $sessionPath\n";

$files = glob($sessionPath . '/sess_*');
if ($files) {
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Removido: " . basename($file) . "\n";
        }
    }
    echo "\n✓ Total de " . count($files) . " sessões removidas\n";
} else {
    echo "✓ Nenhuma sessão encontrada\n";
}
