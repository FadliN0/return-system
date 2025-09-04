<?php
// views/products/index.php

include_once '../../controllers/ProductController.php';

$productController = new ProductController();
$products = $productController->listProducts();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Daftar Produk</h1>
        <a href="add.php" class="btn-primary">Tambah Produk Baru</a>
        <br><br>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['stock']) ?></td>
                            <td>
                                <a href="edit.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-action">Edit</a>
                                <a href="delete.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn-action" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Tidak ada produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="../../index.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>