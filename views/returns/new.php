<?php
// views/returns/new.php

include_once __DIR__ . '/../../controllers/ReturnController.php';

$returnController = new ReturnController();
$transaction = null;
$message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['transaction_number'])) {
    $transaction = $returnController->getSaleByTransactionNumber($_POST['transaction_number']);
    if (!$transaction) {
        $message = "<p class='error'>Nomor transaksi tidak ditemukan.</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_return'])) {
    $saleId = $_POST['sale_id'];
    $items = json_decode($_POST['items'], true);
    $reason = $_POST['reason'];
    $notes = $_POST['notes'];

    $result = $returnController->processReturn($saleId, $items, $reason, $notes);
    
    if ($result['status'] === 'success') {
        $message = "<p class='success'>Retur berhasil! Total Pengembalian: Rp " . number_format($result['total_refund'], 0, ',', '.') . "</p>";
    } else {
        $message = "<p class='error'>Error: " . htmlspecialchars($result['message']) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Retur</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Proses Retur Barang</h1>
        <?= $message ?>
        
        <form action="new.php" method="POST">
            <label for="transaction_number">Nomor Transaksi:</label>
            <input type="text" id="transaction_number" name="transaction_number" required>
            <button type="submit" class="btn-primary">Cari Transaksi</button>
        </form>

        <?php if ($transaction): ?>
            <hr>
            <h2>Detail Transaksi</h2>
            <p><strong>Nomor Transaksi:</strong> <?= htmlspecialchars($transaction['transaction_number']) ?></p>
            <p><strong>Tanggal:</strong> <?= htmlspecialchars($transaction['sale_date']) ?></p>
            <p><strong>Total Penjualan:</strong> Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></p>

            <form action="new.php" method="POST" id="return-form">
                <input type="hidden" name="process_return" value="1">
                <input type="hidden" name="sale_id" value="<?= htmlspecialchars($transaction['id']) ?>">
                <input type="hidden" name="items" id="return-items-input">

                <div id="return-list">
                    <h2>Pilih Barang yang Diretur</h2>
                    <table class="return-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga Satuan</th>
                                <th>Jumlah Beli</th>
                                <th>Jumlah Retur</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaction['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td>Rp <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td><input type="number" name="quantity_<?= $item['product_id'] ?>" data-product-id="<?= htmlspecialchars($item['product_id']) ?>" data-name="<?= htmlspecialchars($item['product_name']) ?>" data-price="<?= htmlspecialchars($item['unit_price']) ?>" max="<?= htmlspecialchars($item['quantity']) ?>" min="0" value="0"></td>
                                <td>
                                    <select name="condition_<?= $item['product_id'] ?>" data-product-id="<?= htmlspecialchars($item['product_id']) ?>">
                                        <option value="baik">Baik (Restock)</option>
                                        <option value="rusak">Rusak (Tidak Restock)</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <hr>
                
                <label for="reason">Alasan Retur:</label>
                <select name="reason" id="reason">
                    <option value="rusak">Rusak</option>
                    <option value="expired">Kadaluarsa</option>
                    <option value="salah_beli">Salah Beli</option>
                    <option value="lainnya">Lainnya</option>
                </select>
                
                <label for="notes">Catatan Tambahan:</label>
                <textarea name="notes" id="notes" rows="3"></textarea>
                
                <button type="button" onclick="prepareReturn()">Proses Retur</button>
            </form>
        <?php endif; ?>

        <br>
        <a href="../../index.php">Kembali ke Dashboard</a>
    </div>

    <script>
        function prepareReturn() {
            const form = document.getElementById('return-form');
            const items = [];
            let totalQuantity = 0;

            document.querySelectorAll('#return-list tbody tr').forEach(row => {
                const quantityInput = row.querySelector('input[type="number"]');
                const conditionSelect = row.querySelector('select');
                const quantity = parseInt(quantityInput.value);

                if (quantity > 0) {
                    const productId = quantityInput.getAttribute('data-product-id');
                    const productName = quantityInput.getAttribute('data-name');
                    const unitPrice = parseFloat(quantityInput.getAttribute('data-price'));
                    const condition = conditionSelect.value;
                    const subtotal = quantity * unitPrice;
                    
                    items.push({
                        product_id: productId,
                        product_name: productName,
                        quantity: quantity,
                        unit_price: unitPrice,
                        subtotal: subtotal,
                        condition: condition
                    });
                    totalQuantity += quantity;
                }
            });

            if (totalQuantity === 0) {
                alert('Pilih setidaknya satu barang untuk diretur.');
                return;
            }

            if (confirm(`Yakin ingin memproses retur untuk ${totalQuantity} barang?`)) {
                document.getElementById('return-items-input').value = JSON.stringify(items);
                form.submit();
            }
        }
    </script>
</body>
</html>