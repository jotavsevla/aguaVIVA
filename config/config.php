<?php
// config/config.php

// Valores padrão para configuração do banco de dados
const DB_HOST_DEFAULT = '127.0.0.1';
const DB_NAME_DEFAULT = 'aguaVIVA';
const DB_USER_DEFAULT = 'usuario_padrao';
const DB_PASS_DEFAULT = 'senha_padrao';
const APP_NAME_DEFAULT = 'Água Mineral VIVA';

// Variáveis que serão utilizadas na aplicação
$DB_HOST = DB_HOST_DEFAULT;
$DB_NAME = DB_NAME_DEFAULT;
$DB_USER = DB_USER_DEFAULT;
$DB_PASS = DB_PASS_DEFAULT;
$APP_NAME = APP_NAME_DEFAULT;

// Carrega variáveis de ambiente se disponíveis
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Pula comentários
        if (strpos($line, '=') === false) continue; // Pula linhas sem sinal de igual

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Atualiza as variáveis com base nos valores do .env
        switch ($name) {
            case 'DB_HOST':
                $DB_HOST = $value;
                break;
            case 'DB_NAME':
                $DB_NAME = $value;
                break;
            case 'DB_USER':
                $DB_USER = $value;
                break;
            case 'DB_PASS':
                $DB_PASS = $value;
                break;
            case 'APP_NAME':
                $APP_NAME = $value;
                break;
        }
    }
}

// Define constantes acessíveis globalmente, se necessário
define('DB_HOST', $DB_HOST);
define('DB_NAME', $DB_NAME);
define('DB_USER', $DB_USER);
define('DB_PASS', $DB_PASS);
define('APP_NAME', $APP_NAME);

// Função de conexão com DB (mantida igual para compatibilidade)
function conectarDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $conn;
    } catch(PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}