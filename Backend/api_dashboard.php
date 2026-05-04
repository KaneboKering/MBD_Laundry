<?php
// api_dashboard.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // 1. Menghitung jumlah transaksi hari ini (berdasarkan tanggal server/sistem)
    $query_hari_ini = "SELECT COUNT(ID_Transaksi) as total FROM transaksi WHERE DATE(Tanggal_Masuk) = CURDATE()";
    $result_hari_ini = mysqli_query($koneksi, $query_hari_ini);
    $hari_ini = mysqli_fetch_assoc($result_hari_ini)['total'];

    // 2. Menghitung cucian yang masih antre / sedang dikerjakan
    $query_proses = "SELECT COUNT(ID_Transaksi) as total FROM transaksi WHERE Status_Pesanan IN ('Diterima', 'Proses')";
    $result_proses = mysqli_query($koneksi, $query_proses);
    $proses = mysqli_fetch_assoc($result_proses)['total'];

    // 3. Menghitung cucian yang sudah selesai tapi belum diambil
    $query_selesai = "SELECT COUNT(ID_Transaksi) as total FROM transaksi WHERE Status_Pesanan = 'Selesai'";
    $result_selesai = mysqli_query($koneksi, $query_selesai);
    $selesai = mysqli_fetch_assoc($result_selesai)['total'];

    // 4. Menghitung total pendapatan khusus di bulan dan tahun ini (Hanya yang Lunas)
    $query_pendapatan = "SELECT SUM(Total_Harga) as total_pendapatan 
                         FROM transaksi 
                         WHERE MONTH(Tanggal_Masuk) = MONTH(CURDATE()) 
                         AND YEAR(Tanggal_Masuk) = YEAR(CURDATE()) 
                         AND Status_Pembayaran = 'Lunas'";
    $result_pendapatan = mysqli_query($koneksi, $query_pendapatan);
    $row_pendapatan = mysqli_fetch_assoc($result_pendapatan);
    
    // Jika belum ada pendapatan bulan ini, set jadi 0 agar tidak null
    $pendapatan = $row_pendapatan['total_pendapatan'] ? $row_pendapatan['total_pendapatan'] : 0;

    // Mengirimkan respons JSON
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "transaksi_hari_ini" => (int)$hari_ini,
            "cucian_diproses" => (int)$proses,
            "cucian_siap_diambil" => (int)$selesai,
            "pendapatan_bulan_ini" => (float)$pendapatan
        ]
    ]);

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan GET."]);
}
?>