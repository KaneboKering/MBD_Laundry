<?php
// api_layanan.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM layanan ORDER BY ID_Layanan ASC";
    $result = mysqli_query($koneksi, $query);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan."]);
}
?>