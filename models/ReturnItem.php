<?php

class ReturnItem {
    private $conn;
    private $table_name = "return_items";

    public $return_id;
    public $product_id;
    public $product_name;
    public $quantity;
    public $unit_price;
    public $subtotal;
    public $restock;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET return_id=?, product_id=?, product_name=?, quantity=?, unit_price=?, subtotal=?, restock=?";
        $stmt = $this->conn->prepare($query);

        $this->return_id = htmlspecialchars(strip_tags($this->return_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
        $this->subtotal = htmlspecialchars(strip_tags($this->subtotal));
        $this->restock = htmlspecialchars(strip_tags($this->restock));

        $stmt->bind_param("iisidii", $this->return_id, $this->product_id, $this->product_name, $this->quantity, $this->unit_price, $this->subtotal, $this->restock);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}