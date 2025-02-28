<?php
// app/Models/User.php
namespace App\Models;

use PDO;

class User {
    private $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findByUsername($username) {
        $stmt = $this->conn->prepare("
            SELECT id, user, password, access_level
            FROM users
            WHERE user = ?
        ");

        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin($userId) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }
}