<?php


class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $price;
    public $stock;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read Products
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $result = $this->conn->query($query);
        return $result;
    }

    // Create Product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=?, price=?, stock=?";
        $stmt = $this->conn->prepare($query);

        // Membersihkan data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));

        $stmt->bind_param("sdi", $this->name, $this->price, $this->stock);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read One Product
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->name = $row['name'];
            $this->price = $row['price'];
            $this->stock = $row['stock'];
            return true;
        }
        return false;
    }

    // Update Product
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = ?, price = ?, stock = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bind_param("sidi", $this->name, $this->price, $this->stock, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete Product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bind_param("i", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}