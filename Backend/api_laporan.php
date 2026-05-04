<?php
// api_laporan.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Menangkap parameter dari URL (GET Request)
    $id_user    = isset($_GET['id_user']) ? mysqli_real_escape_string($koneksi, $_GET['id_user']) : '';
    $start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($koneksi, $_GET['start_date']) : '';
    $end_date   = isset($_GET['end_date']) ? mysqli_real_escape_string($koneksi, $_GET['end_date']) : '';

    // 1. Validasi input
    if (empty($id_user) || empty($start_date) || empty($end_date)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Parameter id_user, start_date, dan end_date wajib diisi."]);
        exit();
    }

    // 2. Otorisasi: Cek apakah user adalah Owner
    $query_cek_user = "SELECT Role FROM user WHERE ID_User = '$id_user'";
    $result_user    = mysqli_query($koneksi, $query_cek_user);

    if (mysqli_num_rows($result_user) > 0) {
        $row_user = mysqli_fetch_assoc($result_user);
        
        // Jika bukan Owner, tolak akses
        if ($row_user['Role'] !== 'Owner') {
            http_response_code(403); // 403 Forbidden
            echo json_encode(["status" => "error", "message" => "Akses Ditolak! Hanya Owner yang dapat melihat laporan."]);
            exit();
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User tidak ditemukan."]);
        exit();
    }

    // 3. Proses Laporan (Hanya berjalan jika lolos pengecekan Owner)
    // Kita ambil transaksi yang sudah Lunas sebagai pendapatan riil
    $query_laporan = "SELECT t.ID_Transaksi, t.Tanggal_Masuk, p.Nama_Pelanggan, l.Nama_Layanan, 
                             t.Berat_Cucian, t.Total_Harga, t.Tanggal_Ambil
                      FROM transaksi t
                      JOIN pelanggan p ON t.ID_Pelanggan = p.ID_Pelanggan
                      JOIN layanan l ON t.ID_Layanan = l.ID_Layanan
                      WHERE DATE(t.Tanggal_Masuk) BETWEEN '$start_date' AND '$end_date'
                      AND t.Status_Pembayaran = 'Lunas'
                      ORDER BY t.Tanggal_Masuk ASC";

    $result_laporan = mysqli_query($koneksi, $query_laporan);
    
    $data_laporan = [];
    $total_pendapatan = 0;

    while ($row = mysqli_fetch_assoc($result_laporan)) {
        $data_laporan[] = $row;
        $total_pendapatan += $row['Total_Harga']; // Kalkulasi total uang masuk
    }

    // 4. Kirim Respons
    echo json_encode([
        "status" => "success",
        "periode" => "$start_date sampai $end_date",
        "total_transaksi" => count($data_laporan),
        "total_pendapatan" => $total_pendapatan,
        "data" => $data_laporan
    ]);

} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan GET."]);
}
?>