<?php

class Sale {
    private $conn;
    private $table_name = "sales";

    public $id;
    public $transaction_number;
    public $sale_date;
    public $total_amount;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET transaction_number=?, total_amount=?";
        $stmt = $this->conn->prepare($query);

        $this->transaction_number = htmlspecialchars(strip_tags($this->transaction_number));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));

        $stmt->bind_param("sd", $this->transaction_number, $this->total_amount);

        if ($stmt->execute()) {
            return $this->conn->insert_id; // Mengembalikan ID transaksi yang baru dibuat
        }
        return false;
    }

    // Metode lain (read, readOne, dll) bisa ditambahkan nanti
}