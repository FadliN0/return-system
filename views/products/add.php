<?php

include_once '../../controllers/ProductController.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $productController = new ProductController();
    if ($productController->addProduct($name, $price, $stock)) {
        $message = "<p class='success'>Produk berhasil ditambahkan!</p>";
    } else {
        $message = "<p class='error'>Gagal menambahkan produk.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Produk Baru</h1>
        <?= $message ?>
        <form action="add.php" method="POST">
            <label for="name">Nama Produk:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="price">Harga:</label>
            <input type="number" step="0.01" id="price" name="price" required>
            
            <label for="stock">Stok Awal:</label>
            <input type="number" id="stock" name="stock" required>
            
            <button type="submit" class="btn-primary">Simpan</button>
        </form>
        <br>
        <a href="index.php">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>