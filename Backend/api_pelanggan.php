<?php
// api_pelanggan.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ==========================================
    // METHOD GET: Mengambil Daftar Pelanggan
    // ==========================================
    case 'GET':
        $query = "SELECT * FROM pelanggan ORDER BY Tanggal_Daftar DESC";
        $result = mysqli_query($koneksi, $query);
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    // ==========================================
    // METHOD POST: Menambah Pelanggan Baru
    // ==========================================
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nama_pelanggan) && !empty($data->no_telepon)) {
            $nama = mysqli_real_escape_string($koneksi, $data->nama_pelanggan);
            $alamat = isset($data->alamat) ? mysqli_real_escape_string($koneksi, $data->alamat) : '';
            $no_telp = mysqli_real_escape_string($koneksi, $data->no_telepon);
            $tgl_daftar = date('Y-m-d');

            $query = "INSERT INTO pelanggan (Nama_Pelanggan, Alamat, No_Telepon, Tanggal_Daftar) 
                      VALUES ('$nama', '$alamat', '$no_telp', '$tgl_daftar')";

            if (mysqli_query($koneksi, $query)) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Pelanggan baru berhasil didaftarkan."]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Gagal: " . mysqli_error($koneksi)]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Nama pelanggan dan No Telepon wajib diisi."]);
        }
        break;
}
?>