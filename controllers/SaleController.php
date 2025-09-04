<?php

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Sale.php';
include_once __DIR__ . '/../models/SaleItem.php';
include_once __DIR__ . '/../models/Product.php';

class SaleController {
    private $db;
    private $sale;
    private $sale_item;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->sale = new Sale($this->db);
        $this->sale_item = new SaleItem($this->db);
        $this->product = new Product($this->db);
    }

    public function processSale($items) {
        // Mulai transaksi database
        $this->db->begin_transaction();
        
        try {
            // 1. Generate nomor transaksi
            $transactionNumber = 'TRX' . date('YmdHis') . rand(1000, 9999);
            
            // 2. Hitung total jumlah penjualan
            $totalAmount = 0;
            $itemsData = [];
            foreach ($items as $item) {
                // Pastikan stok tersedia
                $this->product->id = $item['id'];
                $this->product->readOne();
                if ($this->product->stock < $item['quantity']) {
                    throw new Exception("Stok untuk " . $this->product->name . " tidak mencukupi.");
                }
                
                $subtotal = $this->product->price * $item['quantity'];
                $totalAmount += $subtotal;
                $itemsData[] = [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $this->product->price,
                    'subtotal' => $subtotal
                ];
            }
            
            // 3. Buat entri di tabel sales
            $this->sale->transaction_number = $transactionNumber;
            $this->sale->total_amount = $totalAmount;
            $saleId = $this->sale->create();
            
            if (!$saleId) {
                throw new Exception("Gagal membuat transaksi.");
            }
            
            // 4. Tambahkan setiap item ke sale_items dan kurangi stok
            foreach ($itemsData as $itemData) {
                // Tambah ke sale_items
                $this->sale_item->sale_id = $saleId;
                $this->sale_item->product_id = $itemData['product_id'];
                $this->sale_item->product_name = $itemData['product_name'];
                $this->sale_item->quantity = $itemData['quantity'];
                $this->sale_item->unit_price = $itemData['unit_price'];
                $this->sale_item->subtotal = $itemData['subtotal'];
                
                if (!$this->sale_item->create()) {
                    throw new Exception("Gagal menyimpan item penjualan.");
                }

                // Update stok di tabel products
                $this->product->id = $itemData['product_id'];
                $this->product->readOne();
                $newStock = $this->product->stock - $itemData['quantity'];
                $this->product->stock = $newStock;
                
                if (!$this->product->update()) {
                    throw new Exception("Gagal mengupdate stok.");
                }
            }

            // Commit transaksi jika semua berhasil
            $this->db->commit();
            return ['status' => 'success', 'message' => 'Transaksi berhasil!', 'transaction_number' => $transactionNumber];

        } catch (Exception $e) {
            // Rollback jika ada kesalahan
            $this->db->rollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getDailySales() {
    $query = "
        SELECT 
            DATE(sale_date) AS sale_day,
            COUNT(id) AS total_transactions,
            SUM(total_amount) AS total_sales
        FROM sales
        GROUP BY DATE(sale_date)
        ORDER BY sale_day DESC
    ";
    $result = $this->db->query($query);
    return $result;
}
}