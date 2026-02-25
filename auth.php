<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password, $role) {
        $query = "SELECT id, username, password, role FROM users WHERE username = :username AND role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        // Use fetch() directly because rowCount() is unreliable for SELECT on some PDO drivers
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                return true;
            }
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getRole() {
        return isset($_SESSION['role']) ? $_SESSION['role'] : null;
    }
    
    public function requireRole($role) {
        if (!$this->isLoggedIn() || $this->getRole() !== $role) {
            header("Location: ../index.php");
            exit();
        }
    }
}
?>
