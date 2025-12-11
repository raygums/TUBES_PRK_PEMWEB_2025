<?php
/**
 * LampungSmart - Halaman FAQ (Frequently Asked Questions)
 * Dengan form untuk warga bertanya ke admin
 */

session_start();

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_question'])) {
    // Validate input
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');
    
    if (empty($nama) || empty($email) || empty($pertanyaan)) {
        $error_message = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid!';
    } else {
        // TODO: Simpan ke database (tabel faq_questions)
        // Untuk sekarang, tampilkan success message
        $success_message = 'Pertanyaan Anda telah terkirim! Admin akan merespon melalui email dalam 1x24 jam.';
        
        // Clear form
        $nama = $email = $pertanyaan = '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pertanyaan Umum (FAQ) tentang LampungSmart - Temukan jawaban atau ajukan pertanyaan Anda">
    <title>FAQ - LampungSmart</title>
    
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

    <?php include '../layouts/navbar-landing.php'; ?>

    <!-- Hero Section -->
    <section class="hero-lampung" style="padding: 80px 0;">
        <div class="container">
            <div class="hero-content text-center">
                <div class="mb-4">
                    <span class="badge bg-lampung-gold text-dark px-4 py-2 fs-6">
                        <i class="bi bi-question-circle"></i> Pertanyaan Umum
                    </span>
                </div>
                <h1 class="hero-title" style="font-size: 2.5rem;">Frequently Asked Questions</h1>
                <p class="hero-subtitle">
                    Temukan jawaban untuk pertanyaan yang sering diajukan tentang LampungSmart
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ 1 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apa itu LampungSmart?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    LampungSmart adalah platform Good Governance digital yang menghubungkan masyarakat dengan 
                                    pemerintah Provinsi Lampung. Platform ini menyediakan layanan pelaporan infrastruktur dan 
                                    perizinan UMKM secara online, transparan, dan mudah diakses.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 2 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Siapa yang bisa menggunakan platform ini?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Semua warga Lampung yang berusia minimal 17 tahun dan memiliki KTP dapat 
                                    mendaftar dan menggunakan layanan LampungSmart. Untuk UMKM, Anda harus 
                                    memiliki usaha yang berdomisili di Lampung.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 3 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Berapa lama proses pengaduan ditindaklanjuti?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Setelah pengaduan diverifikasi, tim terkait akan merespons maksimal dalam 2x24 jam. 
                                    Untuk proses penyelesaian, tergantung pada jenis dan kompleksitas masalah. Anda dapat 
                                    memantau status pengaduan secara real-time melalui dashboard.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 4 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apakah data saya aman?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, sangat aman! LampungSmart menggunakan enkripsi tingkat tinggi untuk melindungi data pribadi Anda. 
                                    Semua informasi disimpan di server aman Pemerintah Provinsi Lampung dan tidak akan dibagikan ke pihak ketiga 
                                    tanpa izin Anda.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 5 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Bagaimana cara melaporkan pengaduan?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <ol class="mb-0">
                                        <li>Daftar atau login ke akun Anda</li>
                                        <li>Pilih menu "Lapor Jalan/Sampah" di dashboard</li>
                                        <li>Isi formulir dengan lengkap dan jelas</li>
                                        <li>Upload foto sebagai bukti (opsional tapi disarankan)</li>
                                        <li>Submit dan pantau status di menu "Riwayat Laporan"</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 6 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apakah ada biaya untuk menggunakan platform ini?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Tidak ada biaya sama sekali!</strong> LampungSmart adalah layanan gratis dari 
                                    Pemerintah Provinsi Lampung untuk masyarakat. Hati-hati terhadap penipuan yang mengatasnamakan 
                                    LampungSmart dan meminta biaya.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 7 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Bagaimana cara mengajukan izin UMKM?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Setelah login, pilih menu "Daftar UMKM" dan lengkapi formulir dengan dokumen persyaratan 
                                    (KTP, NPWP, foto usaha, dll). Proses verifikasi memakan waktu 3-7 hari kerja. Anda akan 
                                    mendapat notifikasi via email dan SMS.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 8 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Bagaimana jika lupa password?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Klik "Lupa Password" di halaman login, masukkan email terdaftar Anda. Link reset password 
                                    akan dikirim ke email dalam beberapa menit. Ikuti instruksi untuk membuat password baru.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Question Form Section -->
    <section class="py-5 bg-lampung-gray-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-lampung-lg border-0">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h3 class="text-lampung-blue-dark fw-bold mb-3">
                                    <i class="bi bi-chat-left-quote"></i> Masih Ada Pertanyaan?
                                </h3>
                                <p class="text-muted">
                                    Jika pertanyaan Anda belum terjawab di atas, silakan ajukan pertanyaan ke admin. 
                                    Kami akan merespon melalui email dalam 1x24 jam.
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
                            
                            <form method="POST" action="faq.php" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <label for="nama" class="form-label fw-semibold">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="nama" 
                                           name="nama" 
                                           placeholder="Masukkan nama lengkap Anda"
                                           value="<?php echo htmlspecialchars($nama ?? ''); ?>"
                                           required>
                                    <div class="invalid-feedback">
                                        Nama lengkap wajib diisi
                                    </div>
                                </div>
                                
                                <div class="mb-4">
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
                                
                                <div class="mb-4">
                                    <label for="pertanyaan" class="form-label fw-semibold">
                                        Pertanyaan Anda <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="pertanyaan" 
                                              name="pertanyaan" 
                                              rows="6" 
                                              placeholder="Tulis pertanyaan Anda secara detail..."
                                              required><?php echo htmlspecialchars($pertanyaan ?? ''); ?></textarea>
                                    <div class="invalid-feedback">
                                        Pertanyaan wajib diisi
                                    </div>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i> Admin akan merespon ke email Anda dalam 1x24 jam
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="submit_question" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send-fill"></i> Kirim Pertanyaan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../layouts/footer-landing.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form Validation -->
    <script>
        // Bootstrap form validation
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
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 48, 143, 0.15) !important;
        }
        
        /* Accordion item hover */
        .accordion-item {
            transition: all 0.3s ease-in-out;
        }
        .accordion-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 16px rgba(0, 48, 143, 0.12) !important;
        }
        
        /* Accordion button hover */
        .accordion-button {
            transition: all 0.3s ease-in-out;
        }
        .accordion-button:not(.collapsed) {
            background-color: var(--lampung-blue-light) !important;
            color: var(--lampung-blue-dark) !important;
        }
        .accordion-button:hover {
            background-color: var(--lampung-blue-light) !important;
        }
        
        /* Form input focus effects */
        .form-control:focus, .form-select:focus {
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
            transform: translateY(-2px);
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
        
        /* Icon bounce on accordion expand */
        @keyframes bounce-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .accordion-button:not(.collapsed) i {
            animation: bounce-icon 0.6s ease-in-out;
        }
    </style>

</body>
</html>
