<?php
/**
 * LampungSmart - Halaman Hubungi Kami
 * Contact page with form and contact information
 */

session_start();

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // Validate input
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $error_message = 'Nama, email, subjek, dan pesan wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid!';
    } else {
        // TODO: Simpan ke database (tabel contact_messages)
        // TODO: Kirim email notifikasi ke admin
        $success_message = 'Pesan Anda telah terkirim! Kami akan menghubungi Anda segera.';
        
        // Clear form
        $nama = $email = $telepon = $subjek = $pesan = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hubungi LampungSmart - Kontak informasi dan formulir untuk menghubungi tim kami">
    <title>Hubungi Kami - LampungSmart</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- LampungSmart Theme -->
    <link href="../assets/css/lampung-theme.css" rel="stylesheet">
    <link href="../assets/css/landing-page.css" rel="stylesheet">
    <link href="../assets/css/logo-navbar.css" rel="stylesheet">
</head>
<body>

    <?php include 'layout/navbar-landing.php'; ?>

    <!-- Hero Section -->
    <section class="hero-lampung" style="padding: 80px 0;">
        <div class="container">
            <div class="hero-content text-center">
                <div class="mb-4">
                    <span class="badge bg-lampung-gold text-dark px-4 py-2 fs-6">
                        <i class="bi bi-telephone"></i> Kontak Kami
                    </span>
                </div>
                <h1 class="hero-title" style="font-size: 2.5rem;">Hubungi LampungSmart</h1>
                <p class="hero-subtitle">
                    Kami siap membantu Anda. Hubungi kami melalui form di bawah atau kontak langsung
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Info Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row g-4">
                <!-- Office Location -->
                <div class="col-md-4">
                    <div class="card shadow-lampung-md border-0 h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #00308F, #001A4D); border-radius: 50%;">
                                    <i class="bi bi-building text-white" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold text-lampung-blue-dark mb-3">Kantor Kami</h5>
                            <p class="text-muted mb-0">
                                Gedung Pemerintah Provinsi Lampung<br>
                                Jl. Jenderal Sudirman No. 1<br>
                                Bandar Lampung, 35214
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Numbers -->
                <div class="col-md-4">
                    <div class="card shadow-lampung-md border-0 h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #009639, #006B28); border-radius: 50%;">
                                    <i class="bi bi-telephone-fill text-white" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold text-lampung-green-dark mb-3">Telepon & WhatsApp</h5>
                            <p class="text-muted mb-2">
                                <strong>Telepon:</strong><br>
                                <a href="tel:0721123456" class="text-decoration-none text-lampung-blue">0721-123456</a>
                            </p>
                            <p class="text-muted mb-0">
                                <strong>WhatsApp:</strong><br>
                                <a href="https://wa.me/6282112345678" class="text-decoration-none text-lampung-green">
                                    0821-1234-5678
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Email & Social Media -->
                <div class="col-md-4">
                    <div class="card shadow-lampung-md border-0 h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #FFD700, #FFA500); border-radius: 50%;">
                                    <i class="bi bi-envelope-fill text-white" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold text-lampung-gold-dark mb-3">Email & Media Sosial</h5>
                            <p class="text-muted mb-3">
                                <a href="mailto:admin@lampungsmart.go.id" class="text-decoration-none text-lampung-blue">
                                    admin@lampungsmart.go.id
                                </a>
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="text-lampung-blue fs-4"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="text-lampung-blue fs-4"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="text-lampung-blue fs-4"><i class="bi bi-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5 bg-lampung-gray-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-lampung-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h3 class="text-lampung-blue-dark fw-bold mb-3">
                                    <i class="bi bi-chat-dots"></i> Kirim Pesan
                                </h3>
                                <p class="text-muted">
                                    Isi formulir di bawah ini dan tim kami akan menghubungi Anda dalam 1x24 jam
                                </p>
                            </div>
                            
                            <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="hubungi-kami.php" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nama" class="form-label fw-semibold">
                                            Nama Lengkap <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control form-control-lg" 
                                               id="nama" 
                                               name="nama" 
                                               placeholder="Nama Anda"
                                               value="<?php echo htmlspecialchars($nama ?? ''); ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Nama wajib diisi
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">
                                            Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" 
                                               class="form-control form-control-lg" 
                                               id="email" 
                                               name="email" 
                                               placeholder="nama@email.com"
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Email valid wajib diisi
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label fw-semibold">
                                            Nomor Telepon <span class="text-muted">(Opsional)</span>
                                        </label>
                                        <input type="tel" 
                                               class="form-control form-control-lg" 
                                               id="telepon" 
                                               name="telepon" 
                                               placeholder="08xx-xxxx-xxxx"
                                               value="<?php echo htmlspecialchars($telepon ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="subjek" class="form-label fw-semibold">
                                            Subjek <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-select-lg" id="subjek" name="subjek" required>
                                            <option value="">Pilih Subjek</option>
                                            <option value="teknis" <?php echo (isset($subjek) && $subjek === 'teknis') ? 'selected' : ''; ?>>
                                                Masalah Teknis Website
                                            </option>
                                            <option value="kerjasama" <?php echo (isset($subjek) && $subjek === 'kerjasama') ? 'selected' : ''; ?>>
                                                Kerjasama & Partnership
                                            </option>
                                            <option value="lainnya" <?php echo (isset($subjek) && $subjek === 'lainnya') ? 'selected' : ''; ?>>
                                                Lainnya
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Subjek wajib dipilih
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4 mt-3">
                                    <label for="pesan" class="form-label fw-semibold">
                                        Pesan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="pesan" 
                                              name="pesan" 
                                              rows="6" 
                                              placeholder="Tulis pesan Anda..."
                                              required><?php echo htmlspecialchars($pesan ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Pesan wajib diisi
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="submit_contact" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send-fill"></i> Kirim Pesan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Working Hours Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="card border-lampung-blue border-2">
                        <div class="card-body text-center p-4">
                            <h5 class="text-lampung-blue-dark fw-bold mb-3">
                                <i class="bi bi-clock-history"></i> Jam Operasional
                            </h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <p class="mb-1 fw-semibold">Senin - Jumat</p>
                                    <p class="text-muted mb-0">08:00 - 16:00 WIB</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <p class="mb-1 fw-semibold">Sabtu</p>
                                    <p class="text-muted mb-0">08:00 - 12:00 WIB</p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1 fw-semibold text-danger">Minggu & Hari Libur</p>
                                    <p class="text-muted mb-0">Tutup</p>
                                </div>
                            </div>
                            <hr class="my-3">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle"></i> Pesan di luar jam operasional akan dibalas pada hari kerja berikutnya
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'layout/footer-landing.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form Validation -->
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
    
    <!-- Custom Hover Effects -->
    <style>
        /* Card hover animations */
        .card {
            transition: all 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 48, 143, 0.15) !important;
        }
        
        /* Icon circle pulse effect */
        .card:hover .d-inline-flex {
            animation: pulse-icon 0.6s ease-in-out;
        }
        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Icon rotation on hover */
        .card:hover i {
            animation: rotate-icon 0.5s ease-in-out;
        }
        @keyframes rotate-icon {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Form input focus effects */
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: var(--lampung-blue) !important;
            box-shadow: 0 0 0 0.25rem rgba(0, 48, 143, 0.15) !important;
            transform: translateY(-2px);
            transition: all 0.3s ease-in-out;
        }
        
        /* Button hover effects */
        .btn {
            transition: all 0.3s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 48, 143, 0.2);
        }
        
        /* Social media links hover */
        .d-flex a {
            transition: all 0.3s ease-in-out;
        }
        .d-flex a:hover {
            transform: scale(1.3) rotate(5deg);
        }
        
        /* Footer links hover */
        footer a {
            transition: all 0.3s ease-in-out;
        }
        footer a:hover {
            color: var(--lampung-gold) !important;
            transform: translateX(5px);
            display: inline-block;
        }
        
        /* Social media icons hover */
        footer .d-flex a {
            transition: all 0.3s ease-in-out;
        }
        footer .d-flex a:hover {
            transform: translateY(-3px) scale(1.2);
            color: var(--lampung-gold) !important;
        }
        
        /* Working hours card border animation */
        .border-lampung-blue {
            transition: all 0.3s ease-in-out;
        }
        .border-lampung-blue:hover {
            border-width: 3px !important;
            box-shadow: 0 8px 16px rgba(0, 48, 143, 0.2);
            transform: scale(1.02);
        }
    </style>

</body>
</html>
