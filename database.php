<?php
// Database configuration
class Database {

    private $host = '127.0.0.1';
    private $db_name = 'erp_system';
    private $username = 'erp_user';
    private $password = 'Himanshu@1310';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        // Try MySQL first (normal production setup)
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8";
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $opts);
            return $this->conn;
        } catch (PDOException $e) {
            // MySQL not available or auth failed — fall back to SQLite so app can run locally
            error_log("MySQL connection failed: " . $e->getMessage());
        }

        // SQLite fallback (local file-based DB) ---------------------------------
        try {
            $dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
            if (!is_dir($dataDir)) {
                @mkdir($dataDir, 0777, true);
            }
            $sqliteFile = $dataDir . DIRECTORY_SEPARATOR . 'erp_system.sqlite';
            $sqliteDsn = 'sqlite:' . $sqliteFile;
            $this->conn = new PDO($sqliteDsn);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create schema if needed
            $this->createSqliteSchemaIfNeeded($this->conn);

            return $this->conn;
        } catch (Exception $ex) {
            // If even SQLite fails, report and return null
            echo "Connection error (sqlite): " . $ex->getMessage();
            return null;
        }
    }

    private function createSqliteSchemaIfNeeded(PDO $pdo) {
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        // Create student_details table
        $pdo->exec("CREATE TABLE IF NOT EXISTS student_details (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            roll_number TEXT UNIQUE NOT NULL,
            email TEXT NOT NULL,
            department TEXT NOT NULL,
            course TEXT NOT NULL,
            phone TEXT,
            address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );");

        // Insert demo users if none exist
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM users");
        $count = (int) ($stmt->fetchColumn() ?: 0);
        if ($count === 0) {
            // use same bcrypt hash used in schema.sql (password: password)
            $hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $ins = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:u,:p,:r)");
            $ins->execute([':u' => 'student1', ':p' => $hash, ':r' => 'student']);
            $ins->execute([':u' => 'teacher1', ':p' => $hash, ':r' => 'teacher']);

            // insert a sample student_details record linked to student1 (id 1)
            $pdo->exec("INSERT INTO student_details (user_id, name, roll_number, email, department, course, phone, address) VALUES (1, 'John Doe', 'CS2021001', 'john.doe@email.com', 'Computer Science', 'B.Tech CSE', '9876543210', '123 Main Street, City');");
        }
    }
}
?>
