<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'warga') {
    header('Location: ../public/index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../config/config.php';

$success_message = '';
$error_message = '';
$judul = '';
$deskripsi = '';
$lokasi = '';

$rate_limit_key = 'pengaduan_last_submit_' . $_SESSION['user_id'];
$last_submit = isset($_SESSION[$rate_limit_key]) ? $_SESSION[$rate_limit_key] : 0;
$current_time = time();
$rate_limit_seconds = 300;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF Token validation failed.');
    }
    
    if ($current_time - $last_submit < $rate_limit_seconds) {
        $error_message = "Mohon tunggu " . ($rate_limit_seconds - ($current_time - $last_submit)) . " detik sebelum mengajukan pengaduan berikutnya.";
    } else {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');

        $errors = [];
        
        if (empty($judul)) {
            $errors[] = "Judul pengaduan tidak boleh kosong";
        } elseif (strlen($judul) < 5) {
            $errors[] = "Judul minimal 5 karakter";
        } elseif (strlen($judul) > 100) {
            $errors[] = "Judul maksimal 100 karakter";
        }
        if (preg_match('/[<>\"\'%;()&+]/i', $judul)) {
            $errors[] = "Judul mengandung karakter yang tidak diizinkan";
        }
        
        if (empty($deskripsi)) {
            $errors[] = "Deskripsi tidak boleh kosong";
        } elseif (strlen($deskripsi) < 10) {
            $errors[] = "Deskripsi minimal 10 karakter";
        } elseif (strlen($deskripsi) > 5000) {
            $errors[] = "Deskripsi maksimal 5000 karakter";
        }
        if (preg_match('/<script|<iframe|<object|onclick|onerror|onload/i', $deskripsi)) {
            $errors[] = "Deskripsi mengandung kode berbahaya";
        }
        
        if (empty($lokasi)) {
            $errors[] = "Lokasi tidak boleh kosong";
        } elseif (strlen($lokasi) < 5) {
            $errors[] = "Lokasi minimal 5 karakter";
        } elseif (strlen($lokasi) > 255) {
            $errors[] = "Lokasi maksimal 255 karakter";
        }
        
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['size'] > 0) {
            $foto = $_FILES['foto'];
            $file_size = $foto['size'];
            $file_tmp = $foto['tmp_name'];
            $file_name = $foto['name'];
            $file_error = $foto['error'];
            
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
                        break;
                    default:
                        $errors[] = "Upload error tidak diketahui";
                }
            }
            
            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = "Ukuran file maksimal 5MB";
            }
            
            $allowed_mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            
            if (function_exists('finfo_file')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
            } else {
                $file_type = mime_content_type($file_tmp);
            }
            
            if (!in_array($file_type, array_keys($allowed_mimes))) {
                $errors[] = "Tipe file harus JPG, PNG, atau GIF";
            }
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "Extension file tidak diizinkan. Hanya JPG, PNG, GIF.";
            }
            
            if (empty($errors)) {
                $upload_dir = '../assets/uploads/pengaduan/';
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0755, true);
                }
                $random_hash = bin2hex(random_bytes(8));
                $new_file_name = 'pengaduan_' . (int)$_SESSION['user_id'] . '_' . $random_hash . '.' . $file_ext;
                $foto_path = $new_file_name;
                
                $upload_path = $upload_dir . $new_file_name;
                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $errors[] = "Gagal menyimpan file. Mohon coba lagi.";
                } else {
                    @chmod($upload_path, 0644);
                }
            }
        }
        
        if (empty($errors)) {
            try {
                $query = "INSERT INTO pengaduan (user_id, judul, deskripsi, lokasi, foto, status) VALUES (?, ?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($query);
                $user_id = (int)$_SESSION['user_id'];
                $stmt->bind_param('issss', $user_id, $judul, $deskripsi, $lokasi, $foto_path);
                
                if ($stmt->execute()) {
                    $pengaduan_id = $stmt->insert_id;
                    $success_message = "Pengaduan berhasil diajukan! Nomor ID: " . $pengaduan_id;
                    $_SESSION[$rate_limit_key] = $current_time;
                    $judul = $deskripsi = $lokasi = '';
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $errors[] = "Gagal menyimpan pengaduan. Mohon hubungi support.";
                }
                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Terjadi kesalahan sistem. Mohon coba lagi nanti.";
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
}

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<style>
.page-hero {
    background: linear-gradient(135deg, #04225dff 35%, #f7ba06ff 100%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}
.page-hero h2 {
    font-weight: 800;
    margin-bottom: 10px;
}
.page-hero p {
    opacity: 0.9;
    margin-bottom: 0;
}
.form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}
.form-body {
    padding: 35px;
}
.form-group-custom {
    margin-bottom: 28px;
}
.form-group-custom label {
    display: block;
    color: #212121;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 0.95rem;
}
.form-group-custom .required-star {
    color: #D60000;
    margin-left: 2px;
}
.form-control-custom {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #fafbfc;
}
.form-control-custom:focus {
    outline: none;
    border-color: #009639;
    background-color: white;
    box-shadow: 0 0 0 4px rgba(0,150,57,0.1);
}
textarea.form-control-custom {
    resize: vertical;
    min-height: 130px;
}
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
    background: linear-gradient(90deg, #009639, #00308F);
    width: 0%;
    transition: width 0.3s ease;
}
.form-text-custom {
    display: block;
    font-size: 0.85rem;
    color: #666;
    margin-top: 6px;
}
.form-text-custom i {
    margin-right: 4px;
    color: #00308F;
}
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
    background: #f9fafb;
}
.file-input-label:hover {
    border-color: #009639;
    background: #f0f7f4;
}
.file-input-icon {
    font-size: 2.5rem;
    color: #009639;
    margin-bottom: 10px;
}
.file-input-label span {
    text-align: center;
    color: #666;
    font-weight: 500;
}
.file-input-label small {
    color: #999;
    font-size: 0.85rem;
}
#fotoInfo {
    margin-top: 12px;
    padding: 10px 12px;
    background-color: #E8F5E9;
    border-left: 3px solid #009639;
    border-radius: 6px;
    display: none;
}
#fotoInfo.show {
    display: block;
}
#fotoInfo i {
    color: #009639;
    margin-right: 6px;
}
.button-group {
    display: flex;
    gap: 12px;
    margin-top: 35px;
}
.btn-submit {
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
    background: linear-gradient(135deg, #009639 0%, #007a2f 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(0,150,57,0.25);
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,150,57,0.35);
}
.btn-reset {
    flex: 1;
    padding: 13px 24px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: #f0f0f0;
    color: #212121;
}
.btn-reset:hover {
    background-color: #e8e8e8;
    border-color: #00308F;
    color: #00308F;
}
.info-box {
    background: linear-gradient(135deg, #E3F2FD 0%, #B3E5FC 100%);
    border-left: 4px solid #00308F;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
}
.info-box h5 {
    color: #00308F;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-box ul {
    list-style: none;
    margin-bottom: 0;
    padding-left: 0;
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
    color: #009639;
    font-weight: bold;
}
.alert-custom {
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 25px;
    display: flex;
    align-items: flex-start;
}
.alert-custom i {
    margin-right: 12px;
    margin-top: 2px;
    font-size: 1.2rem;
}
.alert-success-custom {
    background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
    border-left: 4px solid #009639;
    color: #1b5e20;
}
.alert-danger-custom {
    background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
    border-left: 4px solid #D60000;
    color: #b71c1c;
}
</style>

<div class="page-hero">
    <div class="row align-items-center">
        <div class="col-12">
            <h2><i class="fas fa-bullhorn me-2"></i> Ajukan Pengaduan Anda</h2>
            <p>Sampaikan masalah atau saran kepada pemerintah Provinsi Lampung. Suara Anda penting untuk kami!</p>
        </div>
    </div>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert-custom alert-success-custom">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>Pengaduan Berhasil Diajukan!</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <strong>Terjadi Kesalahan!</strong>
            <p style="margin: 5px 0 0 0; font-size: 0.9rem;"><?php echo $error_message; ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" enctype="multipart/form-data" novalidate id="formPengaduan">
        <div class="form-body">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            
            <div class="form-group-custom">
                <label for="judul">Judul Pengaduan <span class="required-star">*</span></label>
                <input type="text" class="form-control-custom" id="judul" name="judul" 
                       placeholder="Contoh: Jalan Rusak di Jl. Imam Bonjol"
                       value="<?php echo htmlspecialchars($judul ?? ''); ?>"
                       minlength="5" maxlength="100" required>
                <div class="char-count-container">
                    <div class="char-count"><span id="judulCount">0</span>/100</div>
                    <div class="char-count-bar"><div class="char-count-bar-fill" id="judulBar"></div></div>
                </div>
                <small class="form-text-custom">
                    <i class="fas fa-lightbulb"></i> Judul yang ringkas dan jelas membantu admin memahami masalah Anda
                </small>
            </div>
            
            <div class="form-group-custom">
                <label for="deskripsi">Deskripsi Lengkap <span class="required-star">*</span></label>
                <textarea class="form-control-custom" id="deskripsi" name="deskripsi" 
                          placeholder="Jelaskan detail masalah Anda secara lengkap... Apa yang terjadi? Sejak kapan? Siapa yang terlibat?"
                          minlength="10" maxlength="5000" required><?php echo htmlspecialchars($deskripsi ?? ''); ?></textarea>
                <div class="char-count-container">
                    <div class="char-count" id="deskripsiCountLabel"><span id="deskripsiCount">0</span>/5000</div>
                    <div class="char-count-bar"><div class="char-count-bar-fill" id="deskripsiBar"></div></div>
                </div>
                <small class="form-text-custom">
                    <i class="fas fa-info-circle"></i> Semakin detail, semakin cepat kami memproses laporan Anda
                </small>
            </div>
            
            <div class="form-group-custom">
                <label for="lokasi">Lokasi Kejadian <span class="required-star">*</span></label>
                <input type="text" class="form-control-custom" id="lokasi" name="lokasi" 
                       placeholder="Contoh: Jl. Imam Bonjol, Kelurahan Penengahan, Bandar Lampung"
                       value="<?php echo htmlspecialchars($lokasi ?? ''); ?>"
                       minlength="5" maxlength="255" required>
                <small class="form-text-custom">
                    <i class="fas fa-map-marker-alt"></i> Lokasi spesifik membantu kami merespons lebih cepat
                </small>
            </div>
            
            <div class="form-group-custom">
                <label for="foto">Foto Pendukung <span style="color: #999; font-weight: 400;">(Opsional)</span></label>
                <div class="file-input-wrapper">
                    <label for="foto" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt file-input-icon"></i>
                        <span>Klik untuk upload atau drag & drop</span>
                        <small>JPG, PNG, GIF | Max 5MB</small>
                    </label>
                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif">
                </div>
                <div id="fotoInfo"></div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Ajukan Pengaduan
                </button>
                <button type="button" class="btn-reset" id="btnReset">
                    <i class="fas fa-redo"></i> Bersihkan
                </button>
            </div>
        </div>
    </form>
</div>

<div class="info-box">
    <h5><i class="fas fa-clock"></i> Informasi Proses Pengaduan</h5>
    <ul>
        <li><strong>Verifikasi:</strong> Pengaduan Anda akan diverifikasi oleh admin dalam 1x24 jam kerja</li>
        <li><strong>Tracking:</strong> Pantau status pengaduan melalui halaman "Riwayat Pengaduan"</li>
        <li><strong>Tanggapan:</strong> Anda akan mendapat balasan untuk setiap pengaduan yang diajukan</li>
        <li><strong>Rahasia:</strong> Data dan privasi Anda dijaga dengan aman</li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const judulInput = document.getElementById('judul');
    const judulCount = document.getElementById('judulCount');
    const judulBar = document.getElementById('judulBar');
    
    judulInput.addEventListener('input', function() {
        const length = this.value.length;
        judulCount.textContent = length;
        judulBar.style.width = (length / 100) * 100 + '%';
    });
    
    const deskripsiInput = document.getElementById('deskripsi');
    const deskripsiCount = document.getElementById('deskripsiCount');
    const deskripsiBar = document.getElementById('deskripsiBar');
    
    deskripsiInput.addEventListener('input', function() {
        const count = this.value.length;
        deskripsiCount.textContent = count;
        deskripsiBar.style.width = (count / 5000) * 100 + '%';
    });
    
    const fotoInput = document.getElementById('foto');
    const fotoInfo = document.getElementById('fotoInfo');
    
    fotoInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            fotoInfo.innerHTML = '<i class="fas fa-check-circle"></i><span style="color: #009639; font-weight: 600;">' + file.name + ' (' + fileSize + 'MB)</span>';
            fotoInfo.classList.add('show');
        } else {
            fotoInfo.innerHTML = '';
            fotoInfo.classList.remove('show');
        }
    });
    
    const fileInputLabel = document.querySelector('.file-input-label');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, () => {
            fileInputLabel.style.borderColor = '#009639';
            fileInputLabel.style.backgroundColor = '#f0f7f4';
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        fileInputLabel.addEventListener(eventName, () => {
            fileInputLabel.style.borderColor = '#d0d0d0';
            fileInputLabel.style.backgroundColor = '#f9fafb';
        });
    });
    
    fileInputLabel.addEventListener('drop', (e) => {
        fotoInput.files = e.dataTransfer.files;
        fotoInput.dispatchEvent(new Event('change', { bubbles: true }));
    });
    
    document.getElementById('btnReset').addEventListener('click', function() {
        document.getElementById('formPengaduan').reset();
        fotoInfo.innerHTML = '';
        fotoInfo.classList.remove('show');
        judulCount.textContent = '0';
        judulBar.style.width = '0%';
        deskripsiCount.textContent = '0';
        deskripsiBar.style.width = '0%';
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
