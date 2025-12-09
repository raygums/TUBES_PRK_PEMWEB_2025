<?php 
$host = "localhost";
$user = "root";
$password = "";
$db = "lampungsmart";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    echo("Koneksi gagal" . mysqli_connect_error());
}

date_default_timezone_set('Asia/Jakarta');
?>