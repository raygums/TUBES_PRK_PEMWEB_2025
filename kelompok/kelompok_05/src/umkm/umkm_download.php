<?php
session_start();

// Cek login
if (!isset($_SESSION['login'])) {
    die("Unauthorized access");
}

require "../config/config.php";

// Validasi ID
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = intval($_GET['id']);

// Ambil data UMKM
$sql = "SELECT * FROM umkm WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("Data UMKM tidak ditemukan.");
}

$data = mysqli_fetch_assoc($result);

// Hanya boleh download jika sudah approved
if ($data['status'] !== "approved") {
    die("UMKM belum disetujui. Tidak dapat mengunduh.");
}

// ---------------------------------------------
// GENERATE FILE PDF SEDERHANA (tanpa library)
// ---------------------------------------------

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=UMKM-{$data['nama_usaha']}.txt");

// Isi dokumen
echo "====== SERTIFIKAT UMKM LAMPUNGSMART ======\n";
echo "Nama Usaha      : " . $data['nama_usaha'] . "\n";
echo "Pemilik         : " . $data['nama_pemilik'] . "\n";
echo "Bidang Usaha    : " . $data['bidang_usaha'] . "\n";
echo "Alamat Usaha    : " . $data['alamat_usaha'] . "\n";
echo "Status          : APPROVED\n";
echo "Tanggal Setuju  : " . $data['created_at'] . "\n";
echo "==========================================\n";
echo "Dokumen ini dihasilkan otomatis oleh sistem.\n";

exit;
?>
