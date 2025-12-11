<?php
session_start();
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
    <link href="../assets/css/chatbot.css" rel="stylesheet">
    <link href="../assets/css/faq-custom.css" rel="stylesheet">
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

    <?php include '../layouts/footer-landing.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chatbot -->
    <script src="../assets/js/chatbot.js"></script>

</body>
</html>
