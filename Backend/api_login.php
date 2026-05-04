<?php
// api_login.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Menangkap data JSON dari Postman / Frontend
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->username) && !empty($data->password)) {
        $username = mysqli_real_escape_string($koneksi, $data->username);
        $password = mysqli_real_escape_string($koneksi, $data->password);

        // Cari user berdasarkan username
        $query = "SELECT * FROM user WHERE Username = '$username'";
        $result = mysqli_query($koneksi, $query);

        // Jika username ditemukan
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Cocokkan password 
            // (Karena menggunakan data dummy dari SQL, kita pakai pengecekan teks biasa)
            if ($password === $user['Password']) {
                http_response_code(200); // 200 OK
                echo json_encode([
                    "status" => "success",
                    "message" => "Login berhasil.",
                    "data" => [
                        "id_user" => $user['ID_User'],
                        "nama_lengkap" => $user['Nama_Lengkap'],
                        "role" => $user['Role']
                    ]
                ]);
            } else {
                http_response_code(401); // 401 Unauthorized
                echo json_encode(["status" => "error", "message" => "Password salah."]);
            }
        } else {
            http_response_code(404); // 404 Not Found
            echo json_encode(["status" => "error", "message" => "Username tidak ditemukan."]);
        }
    } else {
        http_response_code(400); // 400 Bad Request
        echo json_encode(["status" => "error", "message" => "Username dan password wajib diisi."]);
    }
} else {
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan. Gunakan POST."]);
}
?>