<?php

include_once '../../controllers/ProductController.php';

$message = "";
$product = null;

// Langkah 1: Mendapatkan ID produk dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Inisiasi controller
    $productController = new ProductController();

    // Langkah 2: Membaca data produk jika request GET
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $product = $productController->getProduct($id);
        if (!$product) {
            // Redirect jika produk tidak ditemukan
            header("Location: index.php?message=product_not_found");
            exit();
        }
    }
    
    // Langkah 4: Memproses POST Request (saat formulir dikirim)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        
        // Langkah 5: Panggil metode updateProduct dari controller
        if ($productController->updateProduct($id, $name, $price, $stock)) {
            $message = "<p class='success'>Produk berhasil diperbarui!</p>";
            // Update objek product untuk menampilkan data terbaru di form
            $product = $productController->getProduct($id);
        } else {
            $message = "<p class='error'>Gagal memperbarui produk.</p>";
        }
    }
} else {
    // Redirect jika ID tidak ada di URL
    header("Location: index.php?message=id_not_found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Produk: <?= htmlspecialchars($product->name ?? '') ?></h1>
        <?= $message ?>
        
        <?php if ($product): ?>
        <form action="edit.php?id=<?= htmlspecialchars($product->id) ?>" method="POST">
            <label for="name">Nama Produk:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product->name) ?>" required>
            
            <label for="price">Harga:</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($product->price) ?>" required>
            
            <label for="stock">Stok:</label>
            <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product->stock) ?>" required>
            
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
        <?php else: ?>
            <p>Produk tidak ditemukan.</p>
        <?php endif; ?>

        <br>
        <a href="index.php">Kembali ke Daftar Produk</a>
    </div>
</body>
</html>