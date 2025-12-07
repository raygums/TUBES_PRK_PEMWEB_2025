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
    <div class="container">
        <h2>Form Pengaduan</h2>
        
        <?php if ($success_message): ?>
            <div class="alert success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="judul">Judul Pengaduan:</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($judul ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea id="deskripsi" name="deskripsi" rows="5" required><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="lokasi">Lokasi:</label>
                <input type="text" id="lokasi" name="lokasi" value="<?php echo htmlspecialchars($lokasi ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto (opsional):</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>
            <button type="submit">Kirim Pengaduan</button>
        </form>
    </div>
</body>
</html>