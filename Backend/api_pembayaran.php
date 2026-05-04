<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id_transaksi) && !empty($data->jumlah_bayar) && !empty($data->metode_pembayaran)) {
            $id_transaksi = mysqli_real_escape_string($koneksi, $data->id_transaksi);
            $jumlah_bayar = mysqli_real_escape_string($koneksi, $data->jumlah_bayar);
            $metode_pembayaran = mysqli_real_escape_string($koneksi, $data->metode_pembayaran);
            $tanggal_bayar = date('Y-m-d H:i:s');

            // 1. Insert ke tabel pembayaran
            $query_bayar = "INSERT INTO pembayaran (ID_Transaksi, Tanggal_Bayar, Jumlah_Bayar, Metode_Pembayaran) 
                            VALUES ('$id_transaksi', '$tanggal_bayar', '$jumlah_bayar', '$metode_pembayaran')";
            
            if (mysqli_query($koneksi, $query_bayar)) {
                // 2. Update status pembayaran di tabel transaksi menjadi Lunas
                $query_update_transaksi = "UPDATE transaksi SET Status_Pembayaran = 'Lunas' WHERE ID_Transaksi = '$id_transaksi'";
                mysqli_query($koneksi, $query_update_transaksi);

                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Pembayaran berhasil dicatat dan transaksi Lunas."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Gagal memproses pembayaran: " . mysqli_error($koneksi)]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap. Butuh id_transaksi, jumlah_bayar, metode_pembayaran."]);
        }
        break;

    // Bisa tambahkan case 'GET' di sini nanti untuk melihat riwayat pembayaran
}
?>