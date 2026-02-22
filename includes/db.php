<?php
/**
 * Filename: includes/db.php
 * Deskripsi: Database Helper Class untuk koneksi dan operasi database
 * Usage: Require di setiap halaman yang perlu akses database
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'ralira_db';
    private $db_user = 'root';
    private $db_pass = '';
    private $conn;

    // Koneksi ke database
    public function connect() {
        $this->conn = new mysqli($this->host, $this->db_user, $this->db_pass, $this->db_name);

        // Cek koneksi
        if ($this->conn->connect_error) {
            die('Connection Failed: ' . $this->conn->connect_error);
        }

        // Set charset ke utf8mb4
        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }

    // Get koneksi
    public function getConnection() {
        return $this->conn;
    }

    // Query SELECT - Return array hasil
    public function query($sql) {
        $result = $this->conn->query($sql);

        if (!$result) {
            die('Error executing query: ' . $this->conn->error . ' | Query: ' . $sql);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    // Query SELECT - Return 1 baris
    public function get($sql) {
        $result = $this->conn->query($sql);

        if (!$result) {
            return false;
        }

        return $result->fetch_assoc();
    }

    // Execute INSERT, UPDATE, DELETE
    public function execute($sql) {
        if ($this->conn->query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    // Prepared statement untuk keamanan - SELECT
    public function queryPrepare($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die('Error preparing statement: ' . $this->conn->error . ' | Query: ' . $sql);
        }

        if ($params) {
            // Build type string
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            // Bind parameters
            if (!$stmt->bind_param($types, ...$params)) {
                die('Error binding parameters: ' . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            die('Error executing statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        if (!$result) {
            die('Error getting result: ' . $stmt->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    // Prepared statement - GET 1 baris
    public function getPrepare($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);

        if ($params) {
            // Build type string
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            // Bind parameters
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data;
    }

    // Prepared statement - EXECUTE (INSERT, UPDATE, DELETE)
    public function executePrepare($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        if ($params) {
            // Build type string
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            // Bind parameters
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    // Get last inserted ID
    public function lastId() {
        return $this->conn->insert_id;
    }

    // Escape string
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    // Hash password dengan bcrypt
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // Verify password
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Close connection
    public function close() {
        $this->conn->close();
    }
}

// Create instance
$db = new Database();
$db->connect();
?>
