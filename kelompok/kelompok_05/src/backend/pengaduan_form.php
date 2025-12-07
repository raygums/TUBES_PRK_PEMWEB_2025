<?php

// Mulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../backend/auth/login.php');
    exit;
}

// Cek role user (hanya warga yang bisa submit pengaduan)
if ($_SESSION['role'] !== 'warga') {
    header('Location: ../frontend/index.php');
    exit;
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Koneksi database
require_once '../backend/config.php';

// Variable untuk menyimpan pesan error/success
$success_message = '';
$error_message = '';

// Rate limiting: cek pengaduan terakhir (max 1 pengaduan per 5 menit per user)
$rate_limit_key = 'pengaduan_last_submit_' . $_SESSION['user_id'];
$last_submit = isset($_SESSION[$rate_limit_key]) ? $_SESSION[$rate_limit_key] : 0;
$current_time = time();
$rate_limit_seconds = 300; // Atur ke 5 menit

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF TOKEN VALIDATION 
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('⚠️ CSRF Token validation failed. Request rejected for security reasons.');
    }
    
    // RATE LIMITING
    if ($current_time - $last_submit < $rate_limit_seconds) {
        $error_message = "Mohon tunggu " . ($rate_limit_seconds - ($current_time - $last_submit)) . " detik sebelum mengajukan pengaduan berikutnya.";
    } else {
        // Sanitasi input
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        
        // Array untuk menyimpan error validasi
        $errors = [];
        
        // VALIDATION: JUDUL
        if (empty($judul)) {
            $errors[] = "Judul pengaduan tidak boleh kosong";
        } elseif (strlen($judul) < 5) {
            $errors[] = "Judul minimal 5 karakter";
        } elseif (strlen($judul) > 100) {
            $errors[] = "Judul maksimal 100 karakter";
        }
        // Cek karakter berbahaya dalam judul
        if (preg_match('/[<>\"\'%;()&+]/i', $judul)) {
            $errors[] = "Judul mengandung karakter yang tidak diizinkan";
        }
        
        //  VALIDATION: DESKRIPSI 
        if (empty($deskripsi)) {
            $errors[] = "Deskripsi tidak boleh kosong";
        } elseif (strlen($deskripsi) < 10) {
            $errors[] = "Deskripsi minimal 10 karakter";
        } elseif (strlen($deskripsi) > 5000) {
            $errors[] = "Deskripsi maksimal 5000 karakter";
        }
        // Cek karakter berbahaya dalam deskripsi (tapi allow newline)
        if (preg_match('/<script|<iframe|<object|onclick|onerror|onload/i', $deskripsi)) {
            $errors[] = "Deskripsi mengandung kode berbahaya";
        }
        
        //  VALIDATION: LOKASI 
        if (empty($lokasi)) {
            $errors[] = "Lokasi tidak boleh kosong";
        } elseif (strlen($lokasi) < 5) {
            $errors[] = "Lokasi minimal 5 karakter";
        } elseif (strlen($lokasi) > 255) {
            $errors[] = "Lokasi maksimal 255 karakter";
        }
        
        //  SECURITY: FILE UPLOAD VALIDATION 
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
            $foto = $_FILES['foto'];
            $file_size = $foto['size'];
            $file_tmp = $foto['tmp_name'];
            $file_name = $foto['name'];
            $file_error = $foto['error'];
            
            // Cek upload error
            if ($file_error !== UPLOAD_ERR_OK) {
                switch ($file_error) {
                    case UPLOAD_ERR_INI_SIZE:
                        $errors[] = "File terlalu besar (melebihi upload_max_filesize)";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = "File terlalu besar (melebihi MAX_FILE_SIZE form)";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors[] = "File hanya terupload sebagian";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errors[] = "Tidak ada file yang dipilih";
                        break;
                    default:
                        $errors[] = "Upload error tidak diketahui";
                }
            }
            
            // Validasi ukuran file (max 5MB)
            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = "Ukuran file maksimal 5MB";
            }
            
            // STRICT FILE TYPE VALIDATION 
            // Whitelist tipe file yang diizinkan
            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif'
            ];
            
            // Gunakan finfo_file (lebih secure)
            if (function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
            } else {
                // Fallback: cek magic bytes (file signature)
                $file_type = $this->getMimeTypeByMagicBytes($file_tmp);
            }
            
            if (!in_array($file_type, array_keys($allowed_mimes))) {
                $errors[] = "Tipe file harus JPG, PNG, atau GIF (MIME: " . htmlspecialchars($file_type) . ")";
            }
            
            // CEK MAGIC BYTES / FILE SIGNATURE
            // Cek file header untuk pastikan benar-benar image
            $file_header = fread(fopen($file_tmp, 'rb'), 12);
            
            // JPEG signature
            $is_jpeg = (bin2hex(substr($file_header, 0, 3)) === 'ffd8ff');
            // PNG signature
            $is_png = (bin2hex(substr($file_header, 0, 8)) === '89504e470d0a1a0a');
            // GIF signature
            $is_gif = (substr($file_header, 0, 3) === 'GIF');
            
            if (!($is_jpeg || $is_png || $is_gif)) {
                $errors[] = "File signature tidak valid. Pastikan file benar-benar gambar.";
            }
            
            // PREVENT DOUBLE EXTENSION ATTACK
            // Hanya ambil extension terakhir (paling strict)
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Whitelist extension
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "Extension file tidak diizinkan. Hanya JPG, PNG, GIF.";
            }
            
            //  PREVENT RESERVED NAMES 
            // Jangan allow filename yang berbahaya
            $dangerous_names = ['php', 'phtml', 'php3', 'php4', 'php5', 'sh', 'exe', 'bat', 'cmd'];
            if (in_array($file_ext, $dangerous_names)) {
                $errors[] = "Extension file tidak diizinkan untuk keamanan";
            }
            
            // Jika semua validasi lolos, upload file
            if (empty($errors)) {
                $upload_dir = '../../uploads/pengaduan/';
                
                // Buat folder jika belum ada
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0755, true);
                }
                
                // Generate nama file yang benar aman
                // Format: pengaduan_[user_id]_[random_hash].[ext]
                $random_hash = bin2hex(random_bytes(8)); // 16 character random
                $new_file_name = 'pengaduan_' . (int)$_SESSION['user_id'] . '_' . $random_hash . '.' . $file_ext;
                $foto_path = $new_file_name;
                
                $upload_path = $upload_dir . $new_file_name;
                
                // Gunakan move_uploaded_file (secure function)
                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $errors[] = "Gagal menyimpan file. Mohon coba lagi.";
                } else {
                    // Set file permissions (no execute)
                    @chmod($upload_path, 0644);
                }
            }
        }
        
        // INSERT TO DATABASE WITH PREPARED STATEMENT 
        if (empty($errors)) {
            try {
                $query = "INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, foto, status) 
                          VALUES (?, ?, ?, ?, ?, 'pending')";
                
                $stmt = $conn->prepare($query);
                
                // Ensure type binding
                $user_id = (int)$_SESSION['user_id'];
                $stmt->bind_param('issss', $user_id, $judul, $deskripsi, $lokasi, $foto_path);
                
                if ($stmt->execute()) {
                    $pengaduan_id = $stmt->insert_id;
                    $success_message = "Pengaduan berhasil diajukan! Nomor ID: " . $pengaduan_id;
                    
                    // Update rate limit
                    $_SESSION[$rate_limit_key] = $current_time;
                    
                    // Reset form
                    $judul = $deskripsi = $lokasi = '';
                    $_FILES = [];
                    
                    // Generate new CSRF token untuk next form
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    // Don't reveal database error to user!
                    $errors[] = "Gagal menyimpan pengaduan. Mohon hubungi support.";
                    error_log("Database Error: " . $stmt->error, 3, "../../logs/pengaduan_errors.log");
                }
                
                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Terjadi kesalahan sistem. Mohon coba lagi nanti.";
                error_log("Exception: " . $e->getMessage(), 3, "../../logs/pengaduan_errors.log");
            }
        } else {
            // Jika ada error, tampilkan semua error
            $error_message = implode('<br>', $errors);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan - LampungSmart</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome untuk icon tambahan -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- LampungSmart Theme -->
    <link href="../../assets/css/lampung-theme.css" rel="stylesheet">
    <style>
        :root {
            --lampung-green: #009639;
            --lampung-red: #D60000;
            --lampung-blue: #00308F;
            --lampung-gold: #FFD700;
            --lampung-charcoal: #212121;
            --lampung-green-light: #E8F5E9;
            --lampung-blue-light: #E3F2FD;
            --lampung-red-light: #FFEBEE;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }
        
        /* ============ HERO SECTION ============ */
        .hero-pengaduan {
            background: linear-gradient(135deg, var(--lampung-blue) 0%, var(--lampung-green) 100%);
            color: white;
            padding: 50px 20px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-pengaduan::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(100px, -50px);
        }
        
        .hero-pengaduan::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            transform: translate(-80px, 80px);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .hero-content p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: 0;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-icon {
            display: inline-block;
            margin-right: 15px;
            font-size: 3rem;
        }
        
        /* ============ MAIN CONTAINER ============ */
        .container-pengaduan {
            max-width: 750px;
            margin: 0 auto;
        }
        
        /* ============ FORM CARD ============ */
        .card-pengaduan {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .card-pengaduan:hover {
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
        }
        
        /* ============ ALERT MESSAGES ============ */
        .alert-success-custom {
            background: linear-gradient(135deg, var(--lampung-green-light) 0%, #C8E6C9 100%);
            border: none;
            border-left: 4px solid var(--lampung-green);
            color: #1b5e20;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            animation: slideDown 0.3s ease;
        }
        
        .alert-success-custom i {
            margin-right: 12px;
            margin-top: 2px;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .alert-danger-custom {
            background: linear-gradient(135deg, var(--lampung-red-light) 0%, #FFCDD2 100%);
            border: none;
            border-left: 4px solid var(--lampung-red);
            color: #b71c1c;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 25px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-danger-custom i {
            margin-right: 12px;
            margin-top: 2px;
            font-size: 1.2rem;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ============ FORM GROUP ============ */
        .form-group-custom {
            margin-bottom: 28px;
        }
        
        .form-group-custom label {
            display: block;
            color: var(--lampung-charcoal);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .form-group-custom .required-star {
            color: var(--lampung-red);
            margin-left: 2px;
        }
        
        /* ============ INPUT & TEXTAREA ============ */
        .form-control-custom {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
            background-color: #fafbfc;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: var(--lampung-green);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(0, 150, 57, 0.1);
        }
        
        .form-control-custom:invalid {
            border-color: var(--lampung-red);
        }
        
        .form-control-custom:invalid:focus {
            box-shadow: 0 0 0 4px rgba(214, 0, 0, 0.1);
        }
        
        textarea.form-control-custom {
            resize: vertical;
            min-height: 130px;
            font-family: 'Segoe UI', sans-serif;
        }
        
        /* ============ HELPER TEXT ============ */
        .form-text-custom {
            display: block;
            font-size: 0.85rem;
            color: #666;
            margin-top: 6px;
            opacity: 0.9;
        }
        
        .form-text-custom i {
            margin-right: 4px;
            color: var(--lampung-blue);
        }
        
        /* ============ CHARACTER COUNTER ============ */
        .char-count-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f0f0f0;
        }
        
        .char-count {
            font-size: 0.85rem;
            color: #999;
            font-weight: 500;
        }
        
        .char-count-bar {
            height: 4px;
            background-color: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            flex: 1;
            margin: 0 10px;
        }
        
        .char-count-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--lampung-green), var(--lampung-blue));
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .char-count.warning {
            color: var(--lampung-red);
            font-weight: 600;
        }
        
        .char-count.warning ~ .char-count-bar .char-count-bar-fill {
            background: linear-gradient(90deg, #FF9800, var(--lampung-red));
        }
        
        /* ============ FILE UPLOAD ============ */
        .file-input-wrapper {
            position: relative;
            display: block;
        }
        
        #foto {
            display: none;
        }
        
        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 150px;
            border: 2px dashed #d0d0d0;
            border-radius: 12px;
            padding: 30px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f9fafb 0%, #f0f1f3 100%);
            position: relative;
            overflow: hidden;
        }
        
        .file-input-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(0, 150, 57, 0.02) 100%);
            pointer-events: none;
        }
        
        .file-input-label:hover {
            border-color: var(--lampung-green);
            background: linear-gradient(135deg, #f0f7f4 0%, #e8f5e9 100%);
        }
        
        .file-input-label.dragover {
            border-color: var(--lampung-green);
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            box-shadow: 0 8px 24px rgba(0, 150, 57, 0.15);
        }
        
        .file-input-icon {
            font-size: 2.5rem;
            color: var(--lampung-green);
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover .file-input-icon {
            transform: scale(1.1);
        }
        
        .file-input-label span {
            text-align: center;
            color: #666;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .file-input-label small {
            color: #999;
            font-size: 0.85rem;
        }
        
        #fotoInfo {
            margin-top: 12px;
            padding: 10px 12px;
            background-color: var(--lampung-green-light);
            border-left: 3px solid var(--lampung-green);
            border-radius: 6px;
            display: none;
        }
        
        #fotoInfo.show {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        #fotoInfo i {
            color: var(--lampung-green);
            margin-right: 6px;
        }
        
        .file-selected {
            color: var(--lampung-green);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* ============ BUTTON SECTION ============ */
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 35px;
            margin-bottom: 30px;
        }
        
        .btn-submit, .btn-reset {
            flex: 1;
            padding: 13px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--lampung-green) 0%, #007a2f 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 150, 57, 0.25);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 150, 57, 0.35);
        }
        
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 150, 57, 0.25);
        }
        
        .btn-reset {
            background-color: #f0f0f0;
            color: var(--lampung-charcoal);
            border: 2px solid #e0e0e0;
        }
        
        .btn-reset:hover {
            background-color: #e8e8e8;
            border-color: var(--lampung-blue);
            color: var(--lampung-blue);
        }
        
        /* ============ INFO BOX ============ */
        .info-box {
            background: linear-gradient(135deg, var(--lampung-blue-light) 0%, #B3E5FC 100%);
            border-left: 4px solid var(--lampung-blue);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .info-box h5 {
            color: var(--lampung-blue);
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box h5 i {
            font-size: 1.2rem;
        }
        
        .info-box ul {
            list-style: none;
            margin-bottom: 0;
        }
        
        .info-box li {
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
            line-height: 1.5;
        }
        
        .info-box li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--lampung-green);
            font-weight: bold;
        }
        
        .info-box li:last-child {
            margin-bottom: 0;
        }
        
        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 1.8rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn-submit, .btn-reset {
                flex: auto;
                width: 100%;
            }
        }
        
        /* ============ ANIMATIONS ============ */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group-custom {
            animation: fadeIn 0.4s ease forwards;
        }
        
        .form-group-custom:nth-child(1) { animation-delay: 0.1s; }
        .form-group-custom:nth-child(2) { animation-delay: 0.2s; }
        .form-group-custom:nth-child(3) { animation-delay: 0.3s; }
        .form-group-custom:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
        <?php include '../frontend/layout/header.html'; ?>
    
    <!-- HERO SECTION -->
    <div class="hero-pengaduan">
        <div class="hero-content">
            <i class="fas fa-megaphone hero-icon"></i>
            <h1>Ajukan Pengaduan Anda</h1>
            <p>Sampaikan masalah atau saran kepada pemerintah Provinsi Lampung. Suara Anda penting untuk kami!</p>
        </div>
    </div>
    
    <!-- MAIN CONTAINER -->
    <div class="container-pengaduan">
        
        <!-- Alert Success -->
        <?php if (!empty($success_message)): ?>
            <div class="alert-success-custom">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Pengaduan Berhasil Diajukan!</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Alert Error -->
        <?php if (!empty($error_message)): ?>
            <div class="alert-danger-custom">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Terjadi Kesalahan!</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo $error_message; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- FORM CARD -->
        <div class="card-pengaduan">
            <form method="POST" enctype="multipart/form-data" novalidate id="formPengaduan">
                <div style="padding: 35px;">
                    
                    <!-- Input Judul -->
                    <div class="form-group-custom">
                        <label for="judul">
                            Judul Pengaduan
                            <span class="required-star">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control-custom" 
                            id="judul" 
                            name="judul" 
                            placeholder="Contoh: Jalan Rusak di Jl. Imam Bonjol"
                            value="<?php echo htmlspecialchars($judul ?? ''); ?>"
                            minlength="5"
                            maxlength="100"
                            required>
                        <div class="char-count-container">
                            <div class="char-count">
                                <span id="judulCount">0</span>/100
                            </div>
                            <div class="char-count-bar">
                                <div class="char-count-bar-fill" id="judulBar"></div>
                            </div>
                        </div>
                        <small class="form-text-custom">
                            <i class="fas fa-lightbulb"></i> Judul yang ringkas dan jelas membantu admin memahami masalah Anda
                        </small>
                    </div>
                    
                    <!-- Input Deskripsi -->
                    <div class="form-group-custom">
                        <label for="deskripsi">
                            Deskripsi Lengkap
                            <span class="required-star">*</span>
                        </label>
                        <textarea 
                            class="form-control-custom" 
                            id="deskripsi" 
                            name="deskripsi" 
                            placeholder="Jelaskan detail masalah Anda secara lengkap... Apa yang terjadi? Sejak kapan? Siapa yang terlibat?"
                            minlength="10"
                            maxlength="5000"
                            required><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
                        <div class="char-count-container">
                            <div class="char-count" id="deskripsiCountLabel">
                                <span id="deskripsiCount">0</span>/5000
                            </div>
                            <div class="char-count-bar">
                                <div class="char-count-bar-fill" id="deskripsiBar"></div>
                            </div>
                        </div>
                        <small class="form-text-custom">
                            <i class="fas fa-info-circle"></i> Semakin detail, semakin cepat kami memproses laporan Anda
                        </small>
                    </div>
                    
                    <!-- Input Lokasi -->
                    <div class="form-group-custom">
                        <label for="lokasi">
                            Lokasi Kejadian
                            <span class="required-star">*</span>
                        </label>
                        <input 
                            type="text" 
                            class="form-control-custom" 
                            id="lokasi" 
                            name="lokasi" 
                            placeholder="Contoh: Jl. Imam Bonjol, Kelurahan Penengahan, Bandar Lampung"
                            value="<?php echo htmlspecialchars($lokasi ?? ''); ?>"
                            minlength="5"
                            maxlength="255"
                            required>
                        <small class="form-text-custom">
                            <i class="fas fa-map-marker-alt"></i> Lokasi spesifik membantu kami merespons lebih cepat
                        </small>
                    </div>
                    
                    <!-- Input Foto -->
                    <div class="form-group-custom">
                        <label for="foto">
                            Foto Pendukung
                            <span style="color: #999; font-weight: 400;">(Opsional)</span>
                        </label>
                        <div class="file-input-wrapper">
                            <label for="foto" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt file-input-icon"></i>
                                <span>Klik untuk upload atau drag & drop</span>
                                <small>JPG, PNG, GIF | Max 5MB</small>
                            </label>
                            <input 
                                type="file" 
                                id="foto" 
                                name="foto" 
                                accept="image/jpeg,image/png,image/gif">
                        </div>
                        <div id="fotoInfo"></div>
                    </div>
                    
                    <!-- Button Group -->
                    <div class="button-group">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Ajukan Pengaduan
                        </button>
                        <button type="reset" class="btn-reset">
                            <i class="fas fa-redo"></i> Bersihkan
                        </button>
                    </div>
                    
                </div>
            </form>
        </div>
        
        <!-- INFO BOX -->
        <div class="info-box">
            <h5>
                <i class="fas fa-clock"></i>
                Informasi Proses Pengaduan
            </h5>
            <ul>
                <li><strong>Verifikasi:</strong> Pengaduan Anda akan diverifikasi oleh admin dalam 1x24 jam kerja</li>
                <li><strong>Tracking:</strong> Pantau status pengaduan melalui halaman "Riwayat Pengaduan"</li>
                <li><strong>Tanggapan:</strong> Anda akan mendapat balasan untuk setiap pengaduan yang diajukan</li>
                <li><strong>Rahasia:</strong> Data dan privasi Anda dijaga dengan aman</li>
            </ul>
        </div>
        
    </div>
    
    <?php include '../frontend/layout/footer.html'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Character counter untuk judul
        document.getElementById('judul').addEventListener('input', function() {
            document.getElementById('judulCount').textContent = this.value.length;
        });
        
        // Character counter untuk deskripsi
        document.getElementById('deskripsi').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('deskripsiCount').textContent = count;
            
            // Warning jika mendekati max
            if (count > 4500) {
                document.getElementById('deskripsiCount').parentElement.classList.add('warning');
            } else {
                document.getElementById('deskripsiCount').parentElement.classList.remove('warning');
            }
        });
        
        // Handle file input
        document.getElementById('foto').addEventListener('change', function() {
            const fotoInfo = document.getElementById('fotoInfo');
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fotoInfo.innerHTML = `<span class="file-selected">✓ ${file.name} (${fileSize}MB)</span>`;
            } else {
                fotoInfo.innerHTML = '';
            }
        });
        
        // Drag and drop
        const fileInputLabel = document.querySelector('.file-input-label');
        const fileInput = document.getElementById('foto');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, () => {
                fileInputLabel.style.borderColor = 'var(--lampung-green)';
                fileInputLabel.style.backgroundColor = '#f0f7f4';
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileInputLabel.addEventListener(eventName, () => {
                fileInputLabel.style.borderColor = '#ddd';
                fileInputLabel.style.backgroundColor = '#f9f9f9';
            });
        });
        
        fileInputLabel.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        });
        
        // Form validation
        document.getElementById('formPengaduan').addEventListener('submit', function(e) {
            if (!this.checkValidity() === false) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html>