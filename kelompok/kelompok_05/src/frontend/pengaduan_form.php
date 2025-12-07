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

// Koneksi database
require_once '../backend/config.php';

// Variable untuk menyimpan pesan error/success
$success_message = '';
$error_message = '';

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    
    // Array untuk menyimpan error validasi
    $errors = [];
    
    // Validasi judul
    if (empty($judul)) {
        $errors[] = "Judul pengaduan tidak boleh kosong";
    } elseif (strlen($judul) < 5) {
        $errors[] = "Judul minimal 5 karakter";
    } elseif (strlen($judul) > 100) {
        $errors[] = "Judul maksimal 100 karakter";
    }
    
    // Validasi deskripsi
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi tidak boleh kosong";
    } elseif (strlen($deskripsi) < 10) {
        $errors[] = "Deskripsi minimal 10 karakter";
    } elseif (strlen($deskripsi) > 5000) {
        $errors[] = "Deskripsi maksimal 5000 karakter";
    }
    
    // Validasi lokasi
    if (empty($lokasi)) {
        $errors[] = "Lokasi tidak boleh kosong";
    } elseif (strlen($lokasi) < 5) {
        $errors[] = "Lokasi minimal 5 karakter";
    } elseif (strlen($lokasi) > 255) {
        $errors[] = "Lokasi maksimal 255 karakter";
    }
    
    // Proses upload foto (opsional)
    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
        $foto = $_FILES['foto'];
        $file_size = $foto['size'];
        $file_tmp = $foto['tmp_name'];
        $file_name = $foto['name'];
        
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file_tmp);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipe file harus JPG, PNG, atau GIF";
        }
        
        // Validasi ukuran file (max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = "Ukuran file maksimal 5MB";
        }
        
        // Jika validasi lolos, upload file
        if (empty($errors)) {
            $upload_dir = '../../uploads/pengaduan/';
            
            // Buat folder jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate nama file unik
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = 'pengaduan_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $foto_path = $new_file_name;
            
            if (!move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $errors[] = "Gagal upload foto";
            }
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $query = "INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, foto, status) 
                      VALUES (?, ?, ?, ?, ?, 'pending')";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issss', $_SESSION['user_id'], $judul, $deskripsi, $lokasi, $foto_path);
            
            if ($stmt->execute()) {
                $success_message = "Pengaduan berhasil diajukan! Nomor ID: " . $stmt->insert_id;
                // Reset form
                $judul = $deskripsi = $lokasi = '';
                $_FILES = [];
            } else {
                $errors[] = "Error database: " . $stmt->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        // Jika ada error, tampilkan semua error
        $error_message = implode('<br>', $errors);
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
</head>
<body>
        <?php include 'layout/header.html'; ?>
    
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
    
    <?php include 'layout/footer.html'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
     <script>
        
     </script>
</body>
</html>