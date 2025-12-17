<?php
/**
 * Database Configuration
 * Enterprise DLP System
 */

class Database {
    private $host = "localhost";
    private $db_name = "dlps_enterprise";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            // Rethrow exception to be handled by caller
            throw new Exception("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
