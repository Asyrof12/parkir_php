<?php
/**
 * Database Connection Class
 * Menggunakan PDO untuk koneksi database
 */

class Database {
    private $host = "localhost";
    private $db_name = "parkir_wisnu_db";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Membuat koneksi database
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
