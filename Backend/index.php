<?php
// index.php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Transaksi Laundry</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 12px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

    <h2>Daftar Transaksi Laundry</h2>
    <a href="tambah.php" class="btn">+ Tambah Transaksi Baru</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Masuk</th>
                <th>Nama Pelanggan</th>
                <th>Layanan</th>
                <th>Berat (Kg)</th>
                <th>Total Harga</th>
                <th>Status Pesanan</th>
                <th>Status Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query JOIN untuk mengambil data lengkap
            $query = "SELECT t.ID_Transaksi, t.Tanggal_Masuk, p.Nama_Pelanggan, l.Nama_Layanan, 
                             t.Berat_Cucian, t.Total_Harga, t.Status_Pesanan, t.Status_Pembayaran
                      FROM transaksi t
                      JOIN pelanggan p ON t.ID_Pelanggan = p.ID_Pelanggan
                      JOIN layanan l ON t.ID_Layanan = l.ID_Layanan
                      ORDER BY t.Tanggal_Masuk DESC";
            
            $result = mysqli_query($conn, $query);
            $no = 1;

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $row['Tanggal_Masuk'] . "</td>";
                echo "<td>" . $row['Nama_Pelanggan'] . "</td>";
                echo "<td>" . $row['Nama_Layanan'] . "</td>";
                echo "<td>" . $row['Berat_Cucian'] . "</td>";
                echo "<td>Rp " . number_format($row['Total_Harga'], 0, ',', '.') . "</td>";
                echo "<td>" . $row['Status_Pesanan'] . "</td>";
                echo "<td>" . $row['Status_Pembayaran'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>