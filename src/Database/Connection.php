<?php

namespace src\Database;

use PDO;
use PDOException;

class Connection {
    private static $instance = null;

    private function __construct() {
        // Construtor privado para impedir instanciação direta
    }

    public static function getInstance() {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

                self::$instance = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                error_log("Conexão com banco de dados estabelecida com sucesso via singleton");
            } catch (PDOException $e) {
                error_log("Erro de conexão com o banco: " . $e->getMessage());
                die("Erro de conexão: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}