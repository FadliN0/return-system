<?php
// views/reports/sales.php

include_once __DIR__ . '/../../controllers/SaleController.php';

$saleController = new SaleController();
$dailySales = $saleController->getDailySales();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Harian</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Laporan Penjualan Harian</h1>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($dailySales && $dailySales->num_rows > 0): ?>
                    <?php while($row = $dailySales->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['sale_day']) ?></td>
                            <td><?= htmlspecialchars($row['total_transactions']) ?></td>
                            <td>Rp <?= number_format($row['total_sales'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Tidak ada data penjualan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="../../index.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>