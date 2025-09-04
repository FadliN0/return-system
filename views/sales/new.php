<?php
// views/sales/new.php

include_once __DIR__ . '/../../controllers/ProductController.php';
include_once __DIR__ . '/../../controllers/SaleController.php';

$productController = new ProductController();
$saleController = new SaleController();

$products = $productController->listProducts();
$transaction_result = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $items = json_decode($_POST['items'], true);
    if (!empty($items)) {
        $transaction_result = $saleController->processSale($items);

        if ($transaction_result['status'] === 'success') {
            // Jika berhasil, lakukan redirect ke halaman yang sama
            header("Location: new.php?status=success&trans_no=" . $transaction_result['transaction_number']);
            exit(); // Hentikan eksekusi script setelah redirect
        }
    }
}

if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['trans_no'])) {
    $transaction_result = [
        'status' => 'success',
        'message' => 'Transaksi berhasil!',
        'transaction_number' => $_GET['trans_no']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Transaksi Penjualan Baru</h1>
        <?php if ($transaction_result): ?>
            <div class="<?= $transaction_result['status'] === 'success' ? 'success' : 'error' ?>">
                <p><?= htmlspecialchars($transaction_result['message']) ?></p>
                <?php if ($transaction_result['status'] === 'success'): ?>
                    <p>Nomor Transaksi: <strong><?= htmlspecialchars($transaction_result['transaction_number']) ?></strong></p>
                    <a href="new.php" class="btn-primary">Buat Transaksi Baru</a>
                <?php endif; ?>
            </div>
            <br>
        <?php endif; ?>

        <form action="new.php" method="POST" id="sales-form">
            <div id="product-list">
                <label for="product_select">Pilih Produk:</label>
                <select id="product_select">
                    <option value="">-- Pilih Produk --</option>
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while($row = $products->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>" data-name="<?= htmlspecialchars($row['name']) ?>" data-price="<?= htmlspecialchars($row['price']) ?>">
                                <?= htmlspecialchars($row['name']) ?> - Rp <?= number_format($row['price'], 0, ',', '.') ?> (Stok: <?= htmlspecialchars($row['stock']) ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
                <label for="quantity">Jumlah:</label>
                <input type="number" id="quantity" value="1" min="1" required>
                <button type="button" onclick="addItem()">Tambah</button>
            </div>
            
            <hr>
            
            <h2>Keranjang Belanja</h2>
            <table id="cart-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="cart-items">
                    </tbody>
            </table>

            <h3>Total: <span id="total-amount">Rp 0</span></h3>
            
            <input type="hidden" name="items" id="items-input">
            <button type="submit" class="btn-primary" id="checkout-btn">Bayar</button>
        </form>
        <br>
        <a href="../../index.php">Kembali ke Dashboard</a>
    </div>

    <script>
        let cartItems = {};
        const productSelect = document.getElementById('product_select');
        const quantityInput = document.getElementById('quantity');
        const cartTableBody = document.getElementById('cart-items');
        const totalAmountSpan = document.getElementById('total-amount');
        const itemsInput = document.getElementById('items-input');

        function updateCart() {
            cartTableBody.innerHTML = '';
            let total = 0;
            for (const productId in cartItems) {
                const item = cartItems[productId];
                const subtotal = item.price * item.quantity;
                total += subtotal;
                
                const row = `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>Rp ${item.price.toLocaleString('id-ID')}</td>
                        <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td><button type="button" onclick="removeItem(${productId})">Hapus</button></td>
                    </tr>
                `;
                cartTableBody.innerHTML += row;
            }
            totalAmountSpan.textContent = `Rp ${total.toLocaleString('id-ID')}`;
            itemsInput.value = JSON.stringify(Object.values(cartItems));
        }

        function addItem() {
            const productId = productSelect.value;
            const quantity = parseInt(quantityInput.value);

            if (!productId || quantity <= 0) {
                alert('Pilih produk dan masukkan jumlah yang valid.');
                return;
            }
            
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const productName = selectedOption.getAttribute('data-name');
            const productPrice = parseFloat(selectedOption.getAttribute('data-price'));
            
            if (cartItems[productId]) {
                cartItems[productId].quantity += quantity;
            } else {
                cartItems[productId] = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: quantity
                };
            }
            
            updateCart();
            quantityInput.value = 1;
        }

        function removeItem(productId) {
            delete cartItems[productId];
            updateCart();
        }
    </script>
</body>
</html>