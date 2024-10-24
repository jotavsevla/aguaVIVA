<?php
/**
 * Configurações globais do sistema
 */
const DB_HOST = 'localhost';
const DB_NAME = 'aguaVIVA';
const DB_USER = 'jotavsevla';
const DB_PASS = 'sapinho12';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: charset=utf-8');

define("TITLE", "Água Mineral VIVA");

function conectarDB() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

function verificarLogin() {
    if (!isset($_SESSION['userlogged'])) {
        header('Location: login.php');
        exit();
    }
}