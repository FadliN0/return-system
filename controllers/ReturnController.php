<?php

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Return.php';
include_once __DIR__ . '/../models/ReturnItem.php';
include_once __DIR__ . '/../models/Sale.php';
include_once __DIR__ . '/../models/SaleItem.php';
include_once __DIR__ . '/../models/Product.php';

class ReturnController {
    private $db;
    private $returnModel;
    private $returnItem;
    private $sale;
    private $saleItem;
    private $product;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->returnModel = new ReturnModel($this->db);
        $this->returnItem = new ReturnItem($this->db);
        $this->sale = new Sale($this->db);
        $this->saleItem = new SaleItem($this->db);
        $this->product = new Product($this->db);
    }

    public function getSaleByTransactionNumber($transactionNumber) {
        $query = "SELECT * FROM sales WHERE transaction_number = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $transactionNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $saleData = $result->fetch_assoc();
            // Ambil item-item penjualan
            $itemsQuery = "SELECT * FROM sale_items WHERE sale_id = ?";
            $itemsStmt = $this->db->prepare($itemsQuery);
            $itemsStmt->bind_param("i", $saleData['id']);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            
            $saleData['items'] = [];
            while ($itemRow = $itemsResult->fetch_assoc()) {
                $saleData['items'][] = $itemRow;
            }
            return $saleData;
        }
        return null;
    }

    public function processReturn($saleId, $items, $reason, $notes) {
        $this->db->begin_transaction();
        
        try {
            $totalRefund = 0;
            
            // Hitung total refund dan proses item
            foreach ($items as $item) {
                $itemSubtotal = $item['unit_price'] * $item['quantity'];
                $totalRefund += $itemSubtotal;
            }

            // Buat entri retur
            $this->returnModel->sale_id = $saleId;
            $this->returnModel->total_refund = $totalRefund;
            $this->returnModel->reason = $reason;
            $this->returnModel->notes = $notes;
            $returnId = $this->returnModel->create();

            if (!$returnId) {
                throw new Exception("Gagal membuat entri retur.");
            }

            // Simpan setiap item retur dan update stok
            foreach ($items as $item) {
                // Simpan ke return_items
                $this->returnItem->return_id = $returnId;
                $this->returnItem->product_id = $item['product_id'];
                $this->returnItem->product_name = $item['product_name'];
                $this->returnItem->quantity = $item['quantity'];
                $this->returnItem->unit_price = $item['unit_price'];
                $this->returnItem->subtotal = $item['subtotal'];
                $this->returnItem->restock = ($item['condition'] === 'baik'); // Set boolean berdasarkan kondisi

                if (!$this->returnItem->create()) {
                    throw new Exception("Gagal menyimpan item retur.");
                }

                // Jika kondisi baik, tambahkan stok
                if ($item['condition'] === 'baik') {
                    $this->product->id = $item['product_id'];
                    if ($this->product->readOne()) {
                        $newStock = $this->product->stock + $item['quantity'];
                        $this->product->stock = $newStock;
                        if (!$this->product->update()) {
                            throw new Exception("Gagal mengupdate stok.");
                        }
                    } else {
                        throw new Exception("Produk tidak ditemukan.");
                    }
                }
            }

            $this->db->commit();
            return ['status' => 'success', 'message' => 'Retur berhasil diproses!', 'total_refund' => $totalRefund];

        } catch (Exception $e) {
            $this->db->rollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getReturnsReport() {
    $query = "
        SELECT 
            r.return_date, 
            r.total_refund, 
            r.reason,
            s.transaction_number,
            ri.product_name,
            ri.quantity,
            ri.unit_price,
            ri.restock
        FROM returns r
        JOIN return_items ri ON r.id = ri.return_id
        JOIN sales s ON r.sale_id = s.id
        ORDER BY r.return_date DESC
    ";
    $result = $this->db->query($query);
    return $result;
}
}