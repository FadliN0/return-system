<?php

class SaleItem {
    private $conn;
    private $table_name = "sale_items";

    public $sale_id;
    public $product_id;
    public $product_name;
    public $quantity;
    public $unit_price;
    public $subtotal;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET sale_id=?, product_id=?, product_name=?, quantity=?, unit_price=?, subtotal=?";
        $stmt = $this->conn->prepare($query);

        $this->sale_id = htmlspecialchars(strip_tags($this->sale_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
        $this->subtotal = htmlspecialchars(strip_tags($this->subtotal));

        $stmt->bind_param("iisidd", $this->sale_id, $this->product_id, $this->product_name, $this->quantity, $this->unit_price, $this->subtotal);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}