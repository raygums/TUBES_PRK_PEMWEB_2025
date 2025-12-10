<?php
/**
 * LampungSmart - Halaman Profil User
 * Jobdesk Anggota 4: Landing Page & Profil User
 * 
 * Halaman ini hanya dapat diakses oleh user dengan role "warga" yang sudah login.
 * Fitur: Edit profil (nama, foto) dan ubah password
 */

// Start session
session_start();

// Cek apakah user sudah login dan memiliki role warga
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

// Include config untuk koneksi database
require_once '../config/config.php';

// Generate CSRF token jika belum ada
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inisialisasi variabel flash message
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT id, nama, email, profile_photo, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Jika user tidak ditemukan (seharusnya tidak mungkin terjadi)
if (!$user) {
    session_destroy();
    header("Location: ../auth/login.php?error=user_not_found");
    exit();
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Token keamanan tidak valid. Silakan coba lagi.";
        header("Location: profile.php");
        exit();
    }

    // Proses Update Profil
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $nama = trim($_POST['nama']);
        
        // Validasi nama tidak kosong
        if (empty($nama)) {
            $_SESSION['error_message'] = "Nama tidak boleh kosong.";
            header("Location: profile.php");
            exit();
        }
        
        // Sanitasi input
        $nama = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        
        // Proses upload foto profil
        $profile_photo = $user['profile_photo']; // Default: foto lama
        
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];
            
            // Validasi ekstensi file
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_ext)) {
                $_SESSION['error_message'] = "Format file tidak valid. Hanya JPG, JPEG, dan PNG yang diperbolehkan.";
                header("Location: profile.php");
                exit();
            }
            
            // Validasi ukuran file (max 2MB)
            if ($file_size > 2097152) {
                $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 2MB.";
                header("Location: profile.php");
                exit();
            }
            
            // Buat folder upload jika belum ada
            $upload_dir = '../assets/uploads/profile/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate nama file unik
            $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus foto lama jika bukan default
                if ($user['profile_photo'] !== 'default.jpg' && file_exists($upload_dir . $user['profile_photo'])) {
                    unlink($upload_dir . $user['profile_photo']);
                }
                
                $profile_photo = $new_file_name;
            } else {
                $_SESSION['error_message'] = "Gagal mengupload foto. Silakan coba lagi.";
                header("Location: profile.php");
                exit();
            }
        }
        
        // Update database
        $update_query = "UPDATE users SET nama = ?, profile_photo = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $nama, $profile_photo, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Profil berhasil diperbarui!";
            // Update data user di session
            $_SESSION['nama'] = $nama;
            $_SESSION['profile_photo'] = $profile_photo; // Update foto di session
            $user['nama'] = $nama;
            $user['profile_photo'] = $profile_photo;
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui profil. Silakan coba lagi.";
        }
        
        $update_stmt->close();
        header("Location: profile.php");
        exit();
    }
    
    // Proses Update Password
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validasi input tidak kosong
        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error_message'] = "Semua field password harus diisi.";
            header("Location: profile.php");
            exit();
        }
        
        // Validasi password baru minimal 8 karakter dan kombinasi huruf + angka
        if (strlen($new_password) < 8) {
            $_SESSION['error_message'] = "Password baru minimal 8 karakter.";
            header("Location: profile.php");
            exit();
        }
        
        if (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $_SESSION['error_message'] = "Password baru harus mengandung kombinasi huruf dan angka.";
            header("Location: profile.php");
            exit();
        }
        
        // Validasi konfirmasi password cocok
        if ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
            header("Location: profile.php");
            exit();
        }
        
        // Ambil password lama dari database
        $pwd_query = "SELECT password FROM users WHERE id = ?";
        $pwd_stmt = $conn->prepare($pwd_query);
        $pwd_stmt->bind_param("i", $user_id);
        $pwd_stmt->execute();
        $pwd_result = $pwd_stmt->get_result();
        $pwd_data = $pwd_result->fetch_assoc();
        $pwd_stmt->close();
        
        // Verifikasi password lama
        if (!password_verify($old_password, $pwd_data['password'])) {
            $_SESSION['error_message'] = "Password lama tidak sesuai.";
            header("Location: profile.php");
            exit();
        }
        
        // Hash password baru
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password di database
        $update_pwd_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_pwd_stmt = $conn->prepare($update_pwd_query);
        $update_pwd_stmt->bind_param("si", $new_password_hash, $user_id);
        
        if ($update_pwd_stmt->execute()) {
            $_SESSION['success_message'] = "Password berhasil diperbarui!";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui password. Silakan coba lagi.";
        }
        
        $update_pwd_stmt->close();
        header("Location: profile.php");
        exit();
    }
}

// Set path foto profil
$profile_photo_path = '../assets/uploads/profile/' . ($user['profile_photo'] ?: 'default.jpg');

// Calculate profile completeness
$completeness = 0;
$completeness += !empty($user['email']) ? 25 : 0;
$completeness += !empty($user['nama']) ? 50 : 0;
$completeness += ($user['profile_photo'] && $user['profile_photo'] !== 'default.jpg') ? 25 : 0;

// Get recent activity (simple version - just registration date)
$activities = [
    [
        'action_type' => 'registration',
        'action_desc' => 'Mendaftar di LampungSmart',
        'action_date' => $user['created_at']
    ]
];
?>
<?php
require '../layouts/header.php';
?>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- LampungSmart Theme -->
    <link rel="stylesheet" href="../assets/css/lampung-theme.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/profile-custom.css">
<?php
require '../layouts/sidebar.php';
?>

    <!-- Profile Header -->
    <div class="bg-lampung-gradient-primary text-white py-4 mb-4 rounded-3">
        <div class="text-center">
            <div class="profile-photo-wrapper d-inline-block position-relative mb-3">
                <img src="<?php echo htmlspecialchars($profile_photo_path); ?>" 
                     alt="Foto Profil" 
                     style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
                     onerror="this.src='../assets/uploads/profile/default.jpg'">
                <span class="profile-photo-badge" style="position: absolute; bottom: 5px; right: 5px; background-color: var(--lampung-green); color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <i class="bi bi-check-lg"></i>
                </span>
            </div>
            <h3 class="mb-2 fw-bold"><?php echo htmlspecialchars($user['nama']); ?></h3>
            <p class="mb-2">
                <span class="badge bg-white bg-opacity-25 px-3 py-2">
                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                </span>
            </p>
            <p class="mb-0 opacity-75 small">
                <i class="bi bi-calendar3"></i> Bergabung sejak <?php echo date('d M Y', strtotime($user['created_at'])); ?>
            </p>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="mb-5">
        
        <!-- Profile Completeness Card -->
        <div class="card-custom mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-custom-title mb-0">
                    <i class="bi bi-speedometer2 text-primary"></i> Kelengkapan Profil
                </h5>
                <span class="badge badge-custom-primary fs-6"><?php echo $completeness; ?>%</span>
            </div>
            <div class="progress" style="height: 25px; border-radius: var(--radius-sm);">
                <div class="progress-bar bg-lampung-blue" role="progressbar" 
                     style="width: <?php echo $completeness; ?>%" 
                     aria-valuenow="<?php echo $completeness; ?>" aria-valuemin="0" aria-valuemax="100">
                    <span class="fw-bold"><?php echo $completeness; ?>% Lengkap</span>
                </div>
            </div>
            <?php if ($completeness < 100): ?>
            <div class="alert alert-custom-info mt-3 mb-0">
                <i class="bi bi-info-circle"></i> <strong>Tips:</strong> 
                <?php if ($user['profile_photo'] === 'default.jpg' || empty($user['profile_photo'])): ?>
                    Upload foto profil Anda untuk melengkapi profil.
                <?php elseif (empty($user['nama'])): ?>
                    Lengkapi nama lengkap Anda.
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Flash Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs-custom mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                data-bs-target="#profile" type="button" role="tab">
                            <i class="bi bi-person-circle"></i> Data Diri
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" 
                                data-bs-target="#password" type="button" role="tab">
                            <i class="bi bi-key"></i> Ubah Password
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" 
                                data-bs-target="#activity" type="button" role="tab">
                            <i class="bi bi-clock-history"></i> Aktivitas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" 
                                data-bs-target="#settings" type="button" role="tab">
                            <i class="bi bi-gear"></i> Pengaturan
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content">
                    
                    <!-- Tab Data Diri -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <form method="POST" enctype="multipart/form-data" id="profileForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label-lampung">Email</label>
                                        <input type="email" class="form-control-lampung" id="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="text-muted">Email tidak dapat diubah</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nama" class="form-label-lampung">Nama Lengkap *</label>
                                        <input type="text" class="form-control-lampung" id="nama" name="nama" 
                                               value="<?php echo htmlspecialchars($user['nama']); ?>" 
                                               required maxlength="100">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="profile_photo" class="form-label-lampung">Foto Profil</label>
                                        <input type="file" class="form-control-lampung" id="profile_photo" name="profile_photo" 
                                               accept="image/jpeg,image/jpg,image/png" onchange="previewPhoto(event)">
                                        <small class="text-muted">Format: JPG, JPEG, PNG (Max 2MB)</small>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <img id="photoPreview" 
                                             src="<?php echo htmlspecialchars($profile_photo_path); ?>" 
                                             alt="Preview" 
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--lampung-blue); margin: 15px auto; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
                                             onerror="this.src='../assets/uploads/profile/default.jpg'">
                                        <p class="small text-muted">Preview Foto Profil</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary btn-lg shadow-lampung-md">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Ubah Password -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <form method="POST" id="passwordForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="update_password">
                            
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="mb-3">
                                        <label for="old_password" class="form-label-lampung">Password Lama *</label>
                                        <input type="password" class="form-control-lampung" id="old_password" 
                                               name="old_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label-lampung">Password Baru *</label>
                                        <input type="password" class="form-control-lampung" id="new_password" 
                                               name="new_password" required minlength="8">
                                        <small class="text-muted">
                                            Minimal 8 karakter, kombinasi huruf dan angka
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label-lampung">Konfirmasi Password Baru *</label>
                                        <input type="password" class="form-control-lampung" id="confirm_password" 
                                               name="confirm_password" required minlength="8">
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Tips Keamanan:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Gunakan kombinasi huruf besar, huruf kecil, dan angka</li>
                                            <li>Hindari menggunakan password yang mudah ditebak</li>
                                            <li>Jangan gunakan informasi pribadi seperti tanggal lahir</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="text-end mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-lampung-md">
                                            <i class="bi bi-shield-check"></i> Update Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Aktivitas -->
                    <div class="tab-pane fade" id="activity" role="tabpanel">
                        <h5 class="card-custom-title">
                            <i class="bi bi-clock-history text-primary"></i> Riwayat Aktivitas
                        </h5>
                        <p class="text-muted mb-4">Aktivitas terbaru Anda di LampungSmart</p>
                        
                        <div class="list-group">
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item list-group-item-action d-flex gap-3 py-3 border-0 mb-2" style="background-color: var(--lampung-gray-100); border-radius: var(--radius-sm);">
                                    <div class="d-flex gap-2 w-100 justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['action_desc']); ?></h6>
                                            <p class="mb-0 opacity-75 small">
                                                <i class="bi bi-calendar3"></i> 
                                                <?php echo date('d M Y, H:i', strtotime($activity['action_date'])); ?>
                                            </p>
                                        </div>
                                        <small class="opacity-50 text-nowrap">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-custom-info">
                                    <i class="bi bi-info-circle"></i> Belum ada aktivitas yang tercatat.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tab Pengaturan -->
                    <div class="tab-pane fade" id="settings" role="tabpanel">
                        <h5 class="card-custom-title">
                            <i class="bi bi-gear text-primary"></i> Pengaturan Akun
                        </h5>
                        <p class="text-muted mb-4">Kelola preferensi dan keamanan akun Anda</p>
                        
                        <div class="row g-4">
                            <!-- Account Info Card -->
                            <div class="col-md-6">
                                <div class="card-custom h-100">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person-badge"></i> Informasi Akun
                                    </h6>
                                    <div class="mb-2">
                                        <small class="text-muted">User ID</small>
                                        <p class="mb-0 fw-bold">#<?php echo $user['id']; ?></p>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Role</small>
                                        <p class="mb-0">
                                            <span class="badge badge-custom-primary">Warga</span>
                                        </p>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted">Status Akun</small>
                                        <p class="mb-0">
                                            <span class="badge badge-custom-success">
                                                <i class="bi bi-check-circle"></i> Aktif
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Security Card -->
                            <div class="col-md-6">
                                <div class="card-custom h-100">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-shield-check"></i> Keamanan
                                    </h6>
                                    <div class="mb-3">
                                        <small class="text-muted">Autentikasi Dua Faktor</small>
                                        <p class="mb-0">
                                            <span class="badge bg-secondary">Belum Aktif</span>
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Terakhir Ubah Password</small>
                                        <p class="mb-0 text-muted small">-</p>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" disabled>
                                        <i class="bi bi-shield-plus"></i> Aktifkan 2FA (Segera Hadir)
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Privacy Card -->
                            <div class="col-md-12">
                                <div class="card-custom">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-eye-slash"></i> Privasi & Data
                                    </h6>
                                    <div class="alert alert-custom-warning mb-3">
                                        <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong> 
                                        Tindakan berikut bersifat permanen dan tidak dapat dibatalkan.
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-outline-secondary" disabled>
                                            <i class="bi bi-download"></i> Download Data Saya
                                        </button>
                                        <button class="btn btn-outline-danger" disabled>
                                            <i class="bi bi-trash"></i> Hapus Akun
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        Fitur ini akan tersedia dalam pembaruan mendatang.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * Preview foto profil sebelum upload
         */
        function previewPhoto(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                // Validasi ukuran file (2MB = 2097152 bytes)
                if (file.size > 2097152) {
                    alert('Ukuran file terlalu besar! Maksimal 2MB.');
                    event.target.value = '';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak valid! Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
                    event.target.value = '';
                    return;
                }
                
                // Tampilkan preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        
        /**
         * Validasi form password sebelum submit
         */
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Cek apakah password cocok
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
            
            // Cek minimal 8 karakter
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return false;
            }
            
            // Cek kombinasi huruf dan angka
            const hasLetter = /[A-Za-z]/.test(newPassword);
            const hasNumber = /[0-9]/.test(newPassword);
            
            if (!hasLetter || !hasNumber) {
                e.preventDefault();
                alert('Password harus mengandung kombinasi huruf dan angka!');
                return false;
            }
            
            return true;
        });
        
        /**
         * Auto-dismiss alert setelah 5 detik
         */
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>

<?php
require '../layouts/footer.php';
?>
