<?php
// views/reports/returns.php

include_once __DIR__ . '/../../controllers/ReturnController.php';

$returnController = new ReturnController();
$returnsData = $returnController->getReturnsReport();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Retur</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Laporan Retur Barang</h1>
        <table>
            <thead>
                <tr>
                    <th>Tanggal Retur</th>
                    <th>Nomor Transaksi Asal</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total Refund</th>
                    <th>Kondisi</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($returnsData && $returnsData->num_rows > 0): ?>
                    <?php while($row = $returnsData->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['return_date']))) ?></td>
                            <td><?= htmlspecialchars($row['transaction_number']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td>Rp <?= number_format($row['unit_price'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['total_refund'], 0, ',', '.') ?></td>
                            <td><?= $row['restock'] ? 'Baik (Restock)' : 'Rusak (Tidak Restock)' ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Tidak ada data retur.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="../../index.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>