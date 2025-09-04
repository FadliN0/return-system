<?php
// config/Database.php

class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $dbname = "mini_mart_pos";
    private $conn;

    // Metode untuk mendapatkan koneksi
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);
            $this->conn->set_charset("utf8");
        } catch (mysqli_sql_exception $e) {
            echo "Koneksi Gagal: " . $e->getMessage();
        }
        return $this->conn;
    }
}