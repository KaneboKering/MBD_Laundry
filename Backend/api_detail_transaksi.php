<?php
// api_detail_transaksi.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Menangkap parameter 'id' dari URL
    $id_transaksi = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

    if (empty($id_transaksi)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Parameter 'id' transaksi wajib dikirim."]);
        exit();
    }

    // 1. Query utama menggunakan JOIN untuk merangkai semua data yang dibutuhkan struk
    $query_transaksi = "SELECT t.ID_Transaksi, t.Tanggal_Masuk, t.Tanggal_Ambil, t.Berat_Cucian, t.Total_Harga, 
                               t.Status_Pesanan, t.Status_Pembayaran,
                               p.Nama_Pelanggan, p.No_Telepon, p.Alamat,
                               l.Nama_Layanan, l.Harga_Per_Kg,
                               u.Nama_Lengkap AS Nama_Kasir
                        FROM transaksi t
                        JOIN pelanggan p ON t.ID_Pelanggan = p.ID_Pelanggan
                        JOIN layanan l ON t.ID_Layanan = l.ID_Layanan
                        JOIN user u ON t.ID_User = u.ID_User
                        WHERE t.ID_Transaksi = '$id_transaksi'";

    $result_transaksi = mysqli_query($koneksi, $query_transaksi);

    if (mysqli_num_rows($result_transaksi) > 0) {
        $data_transaksi = mysqli_fetch_assoc($result_transaksi);

        // 2. Query tambahan untuk mengambil riwayat pembayaran transaksi ini
        $query_pembayaran = "SELECT Tanggal_Bayar, Jumlah_Bayar, Metode_Pembayaran 
                             FROM pembayaran 
                             WHERE ID_Transaksi = '$id_transaksi' 
                             ORDER BY Tanggal_Bayar ASC";
        
        $result_pembayaran = mysqli_query($koneksi, $query_pembayaran);
        
        $riwayat_bayar = [];
        $total_dibayar = 0;

        while ($row_bayar = mysqli_fetch_assoc($result_pembayaran)) {
            $riwayat_bayar[] = $row_bayar;
            $total_dibayar += $row_bayar['Jumlah_Bayar'];
        }

        // 3. Menggabungkan riwayat pembayaran ke dalam data transaksi
        $data_transaksi['Total_Dibayar'] = $total_dibayar;
        $data_transaksi['Sisa_Tagihan'] = $data_transaksi['Total_Harga'] - $total_dibayar;
        $data_transaksi['Riwayat_Pembayaran'] = $riwayat_bayar;

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $data_transaksi
        ]);

    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Data transaksi dengan ID $id_transaksi tidak ditemukan."]);
    }

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan GET."]);
}
?>