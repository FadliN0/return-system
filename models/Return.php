<?php

class ReturnModel {
    private $conn;
    private $table_name = "returns";

    public $id;
    public $sale_id;
    public $return_date;
    public $total_refund;
    public $reason;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET sale_id=?, total_refund=?, reason=?, notes=?";
        $stmt = $this->conn->prepare($query);

        $this->sale_id = htmlspecialchars(strip_tags($this->sale_id));
        $this->total_refund = htmlspecialchars(strip_tags($this->total_refund));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $stmt->bind_param("idss", $this->sale_id, $this->total_refund, $this->reason, $this->notes);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
}