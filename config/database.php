<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "db_gudang";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>