<?php
// api_transaksi.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ==========================================
    // METHOD GET: Mengambil Data Transaksi
    // ==========================================
    case 'GET':
        $query = "SELECT t.ID_Transaksi, t.Tanggal_Masuk, p.Nama_Pelanggan, l.Nama_Layanan, 
                         t.Berat_Cucian, t.Total_Harga, t.Status_Pesanan, t.Status_Pembayaran
                  FROM transaksi t
                  JOIN pelanggan p ON t.ID_Pelanggan = p.ID_Pelanggan
                  JOIN layanan l ON t.ID_Layanan = l.ID_Layanan
                  ORDER BY t.Tanggal_Masuk DESC";
        
        $result = mysqli_query($koneksi, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode(["status" => "success", "data" => $data]);
        } else {
            echo json_encode(["status" => "success", "data" => [], "message" => "Belum ada transaksi."]);
        }
        break;

    // ==========================================
    // METHOD POST: Menambah Transaksi Baru
    // ==========================================
    case 'POST':
        // Menangkap data JSON dari Postman / Frontend
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id_pelanggan) && !empty($data->id_layanan) && !empty($data->berat_cucian) && !empty($data->id_user)) {
            
            $id_pelanggan = mysqli_real_escape_string($koneksi, $data->id_pelanggan);
            $id_layanan   = mysqli_real_escape_string($koneksi, $data->id_layanan);
            $id_user      = mysqli_real_escape_string($koneksi, $data->id_user);
            $berat        = mysqli_real_escape_string($koneksi, $data->berat_cucian);
            $tgl_masuk    = date('Y-m-d H:i:s');

            // 1. Ambil harga layanan dari database
            $query_layanan = mysqli_query($koneksi, "SELECT Harga_Per_Kg FROM layanan WHERE ID_Layanan = '$id_layanan'");
            
            if (mysqli_num_rows($query_layanan) > 0) {
                $row_layanan  = mysqli_fetch_assoc($query_layanan);
                $harga_per_kg = $row_layanan['Harga_Per_Kg'];
                
                // 2. Hitung total harga otomatis
                $total_harga = $berat * $harga_per_kg;

                // 3. Insert ke database
                $query_insert = "INSERT INTO transaksi 
                                 (ID_Pelanggan, ID_Layanan, ID_User, Tanggal_Masuk, Berat_Cucian, Total_Harga, Status_Pesanan, Status_Pembayaran) 
                                 VALUES 
                                 ('$id_pelanggan', '$id_layanan', '$id_user', '$tgl_masuk', '$berat', '$total_harga', 'Diterima', 'Belum Lunas')";
                
                if (mysqli_query($koneksi, $query_insert)) {
                    http_response_code(201); // 201 Created
                    echo json_encode([
                        "status" => "success", 
                        "message" => "Transaksi berhasil dibuat.",
                        "data" => [
                            "id_transaksi" => mysqli_insert_id($koneksi),
                            "total_harga" => $total_harga
                        ]
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(["status" => "error", "message" => "Gagal menyimpan transaksi: " . mysqli_error($koneksi)]);
                }
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Layanan tidak ditemukan."]);
            }
        } else {
            http_response_code(400); // 400 Bad Request
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap. Pastikan mengirim id_pelanggan, id_layanan, id_user, dan berat_cucian."]);
        }
        break;

    default:
        http_response_code(405); // 405 Method Not Allowed
        echo json_encode(["status" => "error", "message" => "Method tidak diizinkan."]);
        break;

    // ==========================================
    // METHOD PUT: Update Status Pesanan
    // ==========================================
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id_transaksi) && !empty($data->status_pesanan)) {
            $id_transaksi = mysqli_real_escape_string($koneksi, $data->id_transaksi);
            $status_pesanan = mysqli_real_escape_string($koneksi, $data->status_pesanan);

            // Jika statusnya "Diambil", kita otomatis isi Tanggal_Ambil
            $set_tanggal_ambil = "";
            if ($status_pesanan === 'Diambil') {
                $tgl_ambil = date('Y-m-d H:i:s');
                $set_tanggal_ambil = ", Tanggal_Ambil = '$tgl_ambil'";
            }

            $query_update = "UPDATE transaksi 
                             SET Status_Pesanan = '$status_pesanan' $set_tanggal_ambil 
                             WHERE ID_Transaksi = '$id_transaksi'";

            if (mysqli_query($koneksi, $query_update)) {
                echo json_encode(["status" => "success", "message" => "Status pesanan berhasil diperbarui menjadi $status_pesanan."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Gagal update status: " . mysqli_error($koneksi)]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Kirimkan id_transaksi dan status_pesanan."]);
        }
        break;
}
?>