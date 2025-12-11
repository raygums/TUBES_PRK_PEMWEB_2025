<?php
/**
 * LampungSmart - Process Contact Form
 * Backend untuk form Hubungi Kami dengan validasi dan PHPMailer email notification
 */

session_start();
require '../config/config.php';
require '../../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil dan sanitasi input
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telepon = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
$subjek = isset($_POST['subjek']) ? trim($_POST['subjek']) : '';
$pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';

// Validasi input
$errors = [];

if (empty($nama)) {
    $errors[] = 'Nama wajib diisi';
} elseif (strlen($nama) < 3) {
    $errors[] = 'Nama minimal 3 karakter';
}

if (empty($email)) {
    $errors[] = 'Email wajib diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

if (!empty($telepon) && !preg_match('/^[0-9\-\+\(\)\s]{8,20}$/', $telepon)) {
    $errors[] = 'Format nomor telepon tidak valid';
}

if (empty($subjek)) {
    $errors[] = 'Subjek wajib diisi';
} elseif (strlen($subjek) < 5) {
    $errors[] = 'Subjek minimal 5 karakter';
}

if (empty($pesan)) {
    $errors[] = 'Pesan wajib diisi';
} elseif (strlen($pesan) < 10) {
    $errors[] = 'Pesan minimal 10 karakter';
}

// Jika ada error, return
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitasi untuk database (prepared statement lebih baik, tapi untuk kompatibilitas)
$nama_clean = mysqli_real_escape_string($conn, $nama);
$email_clean = mysqli_real_escape_string($conn, $email);
$telepon_clean = mysqli_real_escape_string($conn, $telepon);
$subjek_clean = mysqli_real_escape_string($conn, $subjek);
$pesan_clean = mysqli_real_escape_string($conn, $pesan);

// Insert ke database
$query = "INSERT INTO kontak (nama, email, telepon, subjek, pesan, status, created_at) 
          VALUES ('$nama_clean', '$email_clean', '$telepon_clean', '$subjek_clean', '$pesan_clean', 'baru', NOW())";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesan. Silakan coba lagi.']);
    exit;
}

// Kirim email notifikasi ke admin menggunakan PHPMailer
$emailConfig = require '../config/email_config.php';

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $emailConfig['smtp_host'];
    $mail->SMTPAuth   = $emailConfig['smtp_auth'];
    $mail->Username   = $emailConfig['smtp_username'];
    $mail->Password   = $emailConfig['smtp_password'];
    $mail->SMTPSecure = $emailConfig['smtp_secure'];
    $mail->Port       = $emailConfig['smtp_port'];
    $mail->SMTPDebug  = $emailConfig['smtp_debug'];
    
    // Recipients
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress($emailConfig['admin_email'], $emailConfig['admin_name']);
    $mail->addReplyTo($email, $nama);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Pesan Kontak Baru - ' . $subjek;
    
    // Template email HTML
    $email_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #00308F, #001A4D); color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; border-left: 4px solid #FFD700; }
            .info-row { margin: 10px 0; }
            .label { font-weight: bold; color: #00308F; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔔 Pesan Kontak Baru</h2>
                <p>LampungSmart Platform</p>
            </div>
            <div class='content'>
                <div class='info-row'><span class='label'>Dari:</span> " . htmlspecialchars($nama) . "</div>
                <div class='info-row'><span class='label'>Email:</span> " . htmlspecialchars($email) . "</div>
                <div class='info-row'><span class='label'>Telepon:</span> " . htmlspecialchars($telepon ?: '-') . "</div>
                <div class='info-row'><span class='label'>Subjek:</span> " . htmlspecialchars($subjek) . "</div>
                <hr>
                <div class='info-row'>
                    <span class='label'>Pesan:</span><br>
                    <p>" . nl2br(htmlspecialchars($pesan)) . "</p>
                </div>
            </div>
            <div class='footer'>
                <p>Pesan ini dikirim otomatis oleh sistem LampungSmart</p>
                <p>Waktu: " . date('d-m-Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mail->Body = $email_body;
    $mail->AltBody = strip_tags($email_body); // Plain text version
    
    $mail->send();
    $email_sent = true;
} catch (Exception $e) {
    $email_sent = false;
    error_log("PHPMailer Error: {$mail->ErrorInfo}");
}

// Response sukses (meskipun email gagal, data tetap tersimpan)
echo json_encode([
    'success' => true, 
    'message' => 'Pesan Anda telah terkirim! Kami akan menghubungi Anda segera.',
    'email_sent' => $email_sent
]);
?>
