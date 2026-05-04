<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "laundry";
$port = 3308;
$koneksi = new mysqli($host, $user, $pass, $db, $port);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

echo "Koneksi berhasil ke database: " . $db . PHP_EOL;

?>
