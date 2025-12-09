<?php
require_once "../config/config.php"; // koneksi database

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_usaha    = trim($_POST['nama_usaha']);
    $nama_pemilik  = trim($_POST['nama_pemilik']);
    $bidang_usaha  = trim($_POST['bidang_usaha']);
    $alamat_usaha  = trim($_POST['alamat_usaha']);
    $no_telepon    = trim($_POST['no_telepon']);

    // VALIDASI KOSONG
    if ($nama_usaha == "" || $nama_pemilik == "" || $bidang_usaha == "" || 
        $alamat_usaha == "" || $no_telepon == "") {
        
        header("Location: daftar_umkm.php?error=Semua field harus diisi");
        exit;
    }

    // VALIDASI ANGKA TELEPON
    if (!ctype_digit($no_telepon)) {
        header("Location: daftar_umkm.php?error=Nomor telepon harus angka");
        exit;
    }

    if (strlen($no_telepon) < 10) {
        header("Location: daftar_umkm.php?error=Nomor telepon minimal 10 digit");
        exit;
    }

    // Placeholder user_id (nantinya dari session login)
    $user_id = 1;

    // QUERY INSERT
    $query = "INSERT INTO umkm (user_id, nama_usaha, bidang_usaha, alamat_usaha, nama_pemilik, no_telepon)
              VALUES ('$user_id', '$nama_usaha', '$bidang_usaha', '$alamat_usaha', '$nama_pemilik', '$no_telepon')";

    if (mysqli_query($conn, $query)) {
        header("Location: daftar_umkm.php?success=Pendaftaran UMKM berhasil!");
        exit;
    } else {
        header("Location: daftar_umkm.php?error=Gagal menyimpan data: " . mysqli_error($conn));
        exit;
    }
}
?>
