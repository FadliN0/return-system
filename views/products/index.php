<?php
// views/products/index.php - Improved version with integrated delete

include_once '../../controllers/ProductController.php';

$productController = new ProductController();
$message = "";

// Handle DELETE request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    if ($productController->deleteProduct($id)) {
        $message = "<p class='success'>Produk berhasil dihapus!</p>";
    } else {
        $message = "<p class='error'>Gagal menghapus produk. Produk mungkin masih digunakan dalam transaksi.</p>";
    }
}

// Handle messages from redirects
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'added':
            $message = "<p class='success'>Produk berhasil ditambahkan!</p>";
            break;
        case 'updated':
            $message = "<p class='success'>Produk berhasil diperbarui!</p>";
            break;
    }
}

$products = $productController->listProducts();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .search-box {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .stock-low {
            background-color: #fff3cd;
        }
        .stock-empty {
            background-color: #f8d7da;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            animation: fadeOut 5s ease-out forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daftar Produk Mini Mart Berkah</h1>
        
        <?= $message ?>
        
        <div class="search-box">
            <input type="text" id="searchProduct" placeholder="🔍 Cari produk..." onkeyup="searchProducts()">
            <a href="add.php" class="btn-primary" style="margin-left: 10px;">➕ Tambah Produk Baru</a>
        </div>

        <div class="stats" style="margin: 15px 0; padding: 10px; background: #e3f2fd; border-radius: 5px;">
            <?php 
            $totalProducts = $products ? $products->num_rows : 0;
            // Reset pointer untuk menghitung statistik
            if ($products) {
                $products->data_seek(0);
                $totalStock = 0;
                $lowStock = 0;
                while($row = $products->fetch_assoc()) {
                    $totalStock += $row['stock'];
                    if ($row['stock'] < 10) $lowStock++;
                }
                $products->data_seek(0); // Reset pointer lagi
            }
            ?>
            📊 <strong>Total Produk:</strong> <?= $totalProducts ?> | 
            📦 <strong>Total Stok:</strong> <?= $totalStock ?? 0 ?> unit |
            ⚠️ <strong>Stok Rendah:</strong> <?= $lowStock ?? 0 ?> produk
        </div>
        
        <table id="productTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while($row = $products->fetch_assoc()): ?>
                        <?php 
                        $stockClass = '';
                        $stockStatus = '✅ Normal';
                        if ($row['stock'] == 0) {
                            $stockClass = 'stock-empty';
                            $stockStatus = '❌ Habis';
                        } elseif ($row['stock'] < 10) {
                            $stockClass = 'stock-low'; 
                            $stockStatus = '⚠️ Rendah';
                        }
                        ?>
                        <tr class="<?= $stockClass ?>">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['stock']) ?></td>
                            <td><?= $stockStatus ?></td>
                            <td>
                                <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-action">✏️ Edit</a>
                                <button onclick="confirmDelete(<?= htmlspecialchars($row['id']) ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>')" class="btn-delete">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 30px;">
                        📦 Belum ada produk. <a href="add.php">Tambah produk pertama</a>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <br>
        <a href="../../index.php">🏠 Kembali ke Dashboard</a>
        
        <!-- Hidden form for delete -->
        <form id="deleteForm" method="POST" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
        </form>
    </div>

    <script>
        // Search functionality
        function searchProducts() {
            const input = document.getElementById('searchProduct');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('productTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[1]; // Nama produk column
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        // Delete confirmation
        function confirmDelete(id, name) {
            if (confirm(`⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus produk:\n"${name}"\n\nTindakan ini tidak dapat dibatalkan!`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.message, .success, .error');
            messages.forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => {
                    if (msg.parentNode) msg.parentNode.removeChild(msg);
                }, 500);
            });
        }, 5000);

        // Add keyboard shortcut
        document.addEventListener('keydown', function(e) {
            // Ctrl + F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchProduct').focus();
            }
        });
    </script>
</body>
</html>