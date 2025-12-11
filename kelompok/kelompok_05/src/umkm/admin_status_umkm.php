<?php
session_start();
require '../config/config.php';

// OPTIONAL: hidupkan jika login aktif
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     die("Akses ditolak!");
// }

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Parameter tidak lengkap!");
}

$id = $_GET['id'];
$action = $_GET['action'];

if ($action === 'approve') {
    $status = 'approved';
} elseif ($action === 'reject') {
    $status = 'rejected';
} else {
    die("Aksi tidak valid!");
}

$query = "UPDATE umkm SET status = '$status' WHERE id = $id";
mysqli_query($conn, $query);

header("Location: admin_umkm.php?filter=$status");
exit;
