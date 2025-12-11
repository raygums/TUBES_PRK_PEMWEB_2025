<?php
session_start();

if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_usaha    = trim($_POST['nama_usaha']);
    $nama_pemilik  = trim($_POST['nama_pemilik']);
    $bidang_usaha  = trim($_POST['bidang_usaha']);
    $alamat_usaha  = trim($_POST['alamat_usaha']);
    $no_telepon    = trim($_POST['no_telepon']);

    if ($nama_usaha == "" || $nama_pemilik == "" || $bidang_usaha == "" || 
        $alamat_usaha == "" || $no_telepon == "") {
        
        header("Location: daftar_umkm.php?error=Semua field harus diisi");
        exit;
    }

    if (!ctype_digit($no_telepon)) {
        header("Location: daftar_umkm.php?error=Nomor telepon harus angka");
        exit;
    }

    if (strlen($no_telepon) < 10) {
        header("Location: daftar_umkm.php?error=Nomor telepon minimal 10 digit");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO umkm (user_id, nama_usaha, bidang_usaha, alamat_usaha, nama_pemilik, no_telepon) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $nama_usaha, $bidang_usaha, $alamat_usaha, $nama_pemilik, $no_telepon);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: daftar_umkm.php?success=Pendaftaran UMKM berhasil!");
        exit;
    } else {
        $error_msg = $stmt->error;
        $stmt->close();
        header("Location: daftar_umkm.php?error=Gagal menyimpan data: " . urlencode($error_msg));
        exit;
    }
}
?>
