<?php
// controllers/ProductController.php

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Product.php';

class ProductController {
    private $db;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->product = new Product($this->db);
    }

    public function listProducts() {
        $result = $this->product->readAll();
        return $result;
    }

    public function addProduct($name, $price, $stock) {
        $this->product->name = $name;
        $this->product->price = $price;
        $this->product->stock = $stock;
        return $this->product->create();
    }

    public function getProduct($id) {
        $this->product->id = $id;
        if ($this->product->readOne()) {
            return $this->product;
        }
        return null;
    }

    public function updateProduct($id, $name, $price, $stock) {
        $this->product->id = $id;
        $this->product->name = $name;
        $this->product->price = $price;
        $this->product->stock = $stock;
        return $this->product->update();
    }
    
    public function deleteProduct($id) {
        $this->product->id = $id;
        return $this->product->delete();
    }
}