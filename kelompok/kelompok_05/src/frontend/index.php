<?php
/**
 * LampungSmart - Landing Page Publik
 * Jobdesk Anggota 4: Landing Page & Profil User
 * 
 * Halaman ini hanya dapat diakses oleh pengunjung yang BELUM login.
 * Jika sudah login, akan diarahkan ke profile.php
 */

// Start session untuk cek login status
session_start();

// Redirect ke profile jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LampungSmart - Platform Good Governance digital terintegrasi untuk melayani masyarakat Lampung dalam pelaporan infrastruktur dan perizinan UMKM">
    <meta name="keywords" content="lampung smart, good governance, pengaduan infrastruktur, perizinan umkm, pemerintah lampung">
    <meta name="author" content="Pemerintah Provinsi Lampung">
    <title>LampungSmart - Platform Digital Good Governance Provinsi Lampung</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- LampungSmart Theme (Tema Resmi Provinsi Lampung) -->
    <link href="../assets/css/lampung-theme.css" rel="stylesheet">
    
    <!-- Landing Page Custom Styles -->
    <link href="../assets/css/landing-page.css" rel="stylesheet">
    
    <!-- Logo Navbar Styles -->
    <link href="../assets/css/logo-navbar.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar dengan Tema Lampung -->
    <nav class="navbar navbar-expand-lg navbar-lampung sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="../assets/images/logo-lampung.png" alt="Logo Lampung" class="logo-lampung-navbar"> LampungSmart
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#layanan">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cara-kerja">Cara Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../backend/auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning text-dark px-3 ms-2 rounded" href="../backend/auth/register.php">
                            <i class="bi bi-person-plus"></i> Daftar Sekarang
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section dengan Tema Lampung -->
    <section class="hero-lampung">
        <div class="container">
            <div class="hero-content text-center">
                <div class="mb-4">
                    <span class="badge bg-lampung-gold text-dark px-4 py-2 fs-6">
                        <i class="bi bi-award"></i> Platform Good Governance Provinsi Lampung
                    </span>
                </div>
                <h1 class="hero-title">
                    Satu Platform untuk<br>Kemajuan Lampung
                </h1>
                <p class="hero-subtitle">
                    LampungSmart adalah platform digital terintegrasi yang menghubungkan 
                    masyarakat dengan pemerintah untuk mewujudkan tata kelola yang lebih baik, 
                    transparan, dan responsif dalam melayani kebutuhan infrastruktur dan perizinan UMKM.
                </p>
                <div class="mt-5">
                    <a href="../backend/auth/register.php" class="btn btn-warning btn-lg me-3 shadow-lampung-md">
                        <i class="bi bi-person-plus-fill"></i> Daftar Gratis
                    </a>
                    <a href="#tentang" class="btn btn-outline-light btn-lg shadow-lampung-sm">
                        <i class="bi bi-info-circle"></i> Pelajari Lebih Lanjut
                    </a>
                </div>
                <div class="mt-4">
                    <small class="opacity-75">
                        <i class="bi bi-shield-check"></i> Aman, Terpercaya, dan Resmi dari Pemerintah Provinsi Lampung
                    </small>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card fade-in-lampung">
                        <div class="stat-number">
                            <i class="bi bi-people-fill"></i> 1000+
                        </div>
                        <div class="stat-label">Pengguna Terdaftar</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card fade-in-lampung" style="animation-delay: 0.2s;">
                        <div class="stat-number">
                            <i class="bi bi-file-earmark-check-fill"></i> 500+
                        </div>
                        <div class="stat-label">Pengaduan Terselesaikan</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card fade-in-lampung" style="animation-delay: 0.4s;">
                        <div class="stat-number">
                            <i class="bi bi-shop-window"></i> 200+
                        </div>
                        <div class="stat-label">UMKM Terbantu</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="tentang">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Tentang LampungSmart</h2>
                    <p class="section-subtitle">
                        Platform digital yang mempermudah akses layanan publik untuk masyarakat Lampung
                    </p>
                </div>
            </div>
            
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card shadow-lampung-lg border-0">
                        <div class="card-body p-5">
                            <h3 class="text-lampung-blue-dark fw-bold mb-4">
                                <i class="bi bi-bullseye text-lampung-gold"></i> Visi Kami
                            </h3>
                            <p class="text-lampung-charcoal lead">
                                Menjadi platform good governance digital terdepan di Indonesia yang 
                                menghubungkan masyarakat dan pemerintah untuk menciptakan Lampung yang 
                                lebih maju, sejahtera, dan berkelanjutan.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-lampung-lg border-0">
                        <div class="card-body p-5">
                            <h3 class="text-lampung-blue-dark fw-bold mb-4">
                                <i class="bi bi-flag text-lampung-gold"></i> Misi Kami
                            </h3>
                            <ul class="list-unstyled text-lampung-charcoal">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle-fill text-lampung-green me-2"></i>
                                    Menyediakan akses layanan publik yang mudah, cepat, dan transparan
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle-fill text-lampung-green me-2"></i>
                                    Meningkatkan partisipasi masyarakat dalam pembangunan daerah
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle-fill text-lampung-green me-2"></i>
                                    Mendukung pertumbuhan UMKM lokal melalui digitalisasi perizinan
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill text-lampung-green me-2"></i>
                                    Meningkatkan akuntabilitas dan responsivitas pemerintah daerah
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5 bg-lampung-gray-50" id="layanan">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="section-title">Layanan Kami</h2>
                    <p class="section-subtitle">
                        Dua layanan utama untuk mendukung kemajuan masyarakat Lampung
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Card Pengaduan -->
                <div class="col-lg-6">
                    <div class="card-feature-lampung">
                        <div class="feature-icon">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <h3 class="feature-title">Pengaduan Infrastruktur</h3>
                        <p class="feature-description">
                            Laporkan masalah infrastruktur seperti jalan rusak, lampu jalan mati, 
                            sampah menumpuk, atau fasilitas umum yang rusak. Pengaduan Anda akan 
                            langsung diteruskan ke instansi terkait dan dapat Anda pantau prosesnya 
                            secara real-time.
                        </p>
                        
                        <div class="text-start mt-4">
                            <h5 class="text-lampung-blue-dark mb-3">Kategori Pengaduan:</h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="badge bg-lampung-blue-light text-lampung-blue w-100 py-2">
                                        <i class="bi bi-cone-striped"></i> Jalan Rusak
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-blue-light text-lampung-blue w-100 py-2">
                                        <i class="bi bi-lightbulb"></i> Lampu Mati
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-blue-light text-lampung-blue w-100 py-2">
                                        <i class="bi bi-trash"></i> Sampah Menumpuk
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-blue-light text-lampung-blue w-100 py-2">
                                        <i class="bi bi-tools"></i> Fasilitas Rusak
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <span class="badge badge-success px-3 py-2">
                                <i class="bi bi-clock-history"></i> Respons Maksimal 2x24 Jam
                            </span>
                            <span class="badge badge-primary px-3 py-2">
                                <i class="bi bi-eye"></i> Tracking Real-Time
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card UMKM -->
                <div class="col-lg-6">
                    <div class="card-feature-lampung">
                        <div class="feature-icon">
                            <i class="bi bi-shop-window"></i>
                        </div>
                        <h3 class="feature-title">Perizinan UMKM Digital</h3>
                        <p class="feature-description">
                            Ajukan berbagai jenis izin usaha untuk UMKM Anda secara online tanpa 
                            harus datang ke kantor dan mengantri. Proses yang lebih cepat, transparan, 
                            dan mudah dipantau untuk mendukung pertumbuhan usaha Anda.
                        </p>
                        
                        <div class="text-start mt-4">
                            <h5 class="text-lampung-blue-dark mb-3">Jenis Izin Tersedia:</h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="badge bg-lampung-green-light text-lampung-green w-100 py-2">
                                        <i class="bi bi-file-earmark-text"></i> SIUP
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-green-light text-lampung-green w-100 py-2">
                                        <i class="bi bi-shield-check"></i> PIRT
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-green-light text-lampung-green w-100 py-2">
                                        <i class="bi bi-building"></i> Izin Gangguan
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-lampung-green-light text-lampung-green w-100 py-2">
                                        <i class="bi bi-award"></i> NIB
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <span class="badge badge-warning px-3 py-2">
                                <i class="bi bi-lightning"></i> Proses 3-7 Hari Kerja
                            </span>
                            <span class="badge badge-primary px-3 py-2">
                                <i class="bi bi-file-check"></i> 100% Digital
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5 bg-white" id="cara-kerja">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="section-title">Cara Kerja Platform</h2>
                    <p class="section-subtitle">
                        Hanya 4 langkah mudah untuk menggunakan layanan LampungSmart
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Step 1 -->
                <div class="col-md-6 col-lg-3">
                    <div class="process-card">
                        <div class="process-number">1</div>
                        <h4 class="process-title">Daftar & Login</h4>
                        <p class="process-description">
                            Buat akun dengan mengisi formulir pendaftaran sederhana. 
                            Gunakan email dan nomor HP yang aktif untuk verifikasi.
                        </p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="col-md-6 col-lg-3">
                    <div class="process-card">
                        <div class="process-number">2</div>
                        <h4 class="process-title">Pilih Layanan</h4>
                        <p class="process-description">
                            Pilih layanan yang Anda butuhkan: Pengaduan Infrastruktur 
                            atau Perizinan UMKM.
                        </p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="col-md-6 col-lg-3">
                    <div class="process-card">
                        <div class="process-number">3</div>
                        <h4 class="process-title">Isi Formulir</h4>
                        <p class="process-description">
                            Lengkapi formulir dengan informasi yang jelas dan akurat. 
                            Upload dokumen pendukung jika diperlukan.
                        </p>
                    </div>
                </div>
                
                <!-- Step 4 -->
                <div class="col-md-6 col-lg-3">
                    <div class="process-card">
                        <div class="process-number">4</div>
                        <h4 class="process-title">Pantau Status</h4>
                        <p class="process-description">
                            Pantau perkembangan pengaduan atau permohonan izin Anda 
                            secara real-time melalui dashboard.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <div class="alert alert-lampung-info border-left-lampung-blue">
                        <h5 class="text-lampung-blue-dark mb-3">
                            <i class="bi bi-lightbulb-fill"></i> Tips Penting
                        </h5>
                        <p class="mb-0">
                            Pastikan data yang Anda masukkan akurat dan lengkap. Sertakan foto atau dokumen 
                            pendukung untuk mempercepat proses verifikasi. Anda akan menerima notifikasi melalui 
                            email dan SMS setiap ada update status.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="row mb-5">
                    <div class="col-lg-8 mx-auto text-center">
                        <h2 class="display-5 fw-bold mb-4">Mengapa Memilih LampungSmart?</h2>
                        <p class="lead opacity-90">
                            Platform ini dirancang dengan fokus pada kemudahan, transparansi, dan efisiensi 
                            untuk memberikan pengalaman terbaik bagi masyarakat Lampung
                        </p>
                    </div>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-lightning-charge-fill benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Cepat & Efisien</h5>
                                    <p class="mb-0 opacity-90">
                                        Tidak perlu lagi mengantri atau datang ke kantor. 
                                        Semua proses dapat dilakukan secara online kapan saja, di mana saja.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-eye-fill benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Transparan</h5>
                                    <p class="mb-0 opacity-90">
                                        Pantau setiap tahapan proses secara real-time. 
                                        Anda akan tahu persis status pengaduan atau permohonan izin Anda.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-shield-check benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Aman & Terpercaya</h5>
                                    <p class="mb-0 opacity-90">
                                        Data Anda dilindungi dengan enkripsi tingkat tinggi. 
                                        Platform resmi dari Pemerintah Provinsi Lampung.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-headset benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Dukungan 24/7</h5>
                                    <p class="mb-0 opacity-90">
                                        Tim support kami siap membantu Anda melalui email, telepon, 
                                        atau live chat jika mengalami kendala.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-graph-up-arrow benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Akuntabel</h5>
                                    <p class="mb-0 opacity-90">
                                        Setiap tindakan dan keputusan dicatat dengan baik untuk 
                                        memastikan akuntabilitas pemerintah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-phone benefit-icon"></i>
                                <div>
                                    <h5 class="fw-bold mb-2">Mobile Friendly</h5>
                                    <p class="mb-0 opacity-90">
                                        Akses dari smartphone, tablet, atau komputer. 
                                        Antarmuka responsif yang mudah digunakan di semua perangkat.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-12 text-center">
                        <h3 class="mb-4">Siap Berkontribusi untuk Lampung yang Lebih Baik?</h3>
                        <a href="../backend/auth/register.php" class="btn btn-warning btn-lg shadow-lampung-lg">
                            <i class="bi bi-rocket-takeoff-fill"></i> Mulai Sekarang Gratis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="testimonial-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="section-title">Apa Kata Pengguna Kami</h2>
                    <p class="section-subtitle">
                        Testimoni nyata dari masyarakat yang telah menggunakan LampungSmart
                    </p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card fade-in-lampung">
                        <p class="testimonial-text">
                            "Sangat membantu! Saya melaporkan jalan rusak di depan rumah dan 
                            dalam 3 hari sudah ditindaklanjuti. Prosesnya transparan dan bisa 
                            dipantau secara real-time."
                        </p>
                        <div class="testimonial-author">Budi Santoso</div>
                        <div class="testimonial-role">Warga Bandar Lampung</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card fade-in-lampung" style="animation-delay: 0.2s;">
                        <p class="testimonial-text">
                            "Mengurus izin UMKM jadi lebih mudah dan cepat. Tidak perlu lagi 
                            bolak-balik ke kantor. Semua bisa dilakukan dari rumah. Terima kasih 
                            LampungSmart!"
                        </p>
                        <div class="testimonial-author">Siti Rahayu</div>
                        <div class="testimonial-role">Pengusaha UMKM - Metro</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card fade-in-lampung" style="animation-delay: 0.4s;">
                        <p class="testimonial-text">
                            "Platform yang sangat user-friendly. Sebagai pengusaha muda, saya 
                            terbantu sekali dengan proses perizinan yang digital dan transparan. 
                            Highly recommended!"
                        </p>
                        <div class="testimonial-author">Ahmad Fauzi</div>
                        <div class="testimonial-role">Pengusaha Muda - Lampung Selatan</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Pertanyaan Umum (FAQ)</h2>
                    <p class="section-subtitle">
                        Jawaban untuk pertanyaan yang sering diajukan
                    </p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ 1 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apakah LampungSmart gratis?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya, seluruh layanan LampungSmart 100% gratis untuk semua masyarakat Lampung. 
                                    Tidak ada biaya pendaftaran, biaya langganan, atau biaya tersembunyi lainnya.
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
                                    Pengaduan akan diverifikasi maksimal 2x24 jam. Setelah verifikasi, 
                                    pengaduan akan diteruskan ke instansi terkait dan proses penyelesaian 
                                    bergantung pada tingkat urgensi dan kompleksitas masalah (biasanya 3-14 hari kerja).
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 4 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apa saja dokumen yang diperlukan untuk perizinan UMKM?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Dokumen yang diperlukan bervariasi tergantung jenis izin. Umumnya meliputi: 
                                    KTP, NPWP, Surat Keterangan Domisili Usaha, dan dokumen pendukung lainnya. 
                                    Daftar lengkap akan ditampilkan saat Anda memilih jenis izin yang diajukan.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ 5 -->
                        <div class="accordion-item mb-3 border-0 shadow-lampung-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="bi bi-question-circle text-lampung-blue me-2"></i>
                                    Apakah data saya aman?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Keamanan data adalah prioritas kami. Semua data dienkripsi dan disimpan di 
                                    server yang aman. Kami mematuhi undang-undang perlindungan data pribadi dan 
                                    tidak akan membagikan informasi Anda kepada pihak ketiga tanpa persetujuan Anda.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-5 bg-lampung-gradient-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Bergabunglah dengan Ribuan Pengguna Lainnya</h2>
                    <p class="lead mb-4">
                        Mari bersama-sama membangun Lampung yang lebih baik melalui partisipasi aktif 
                        dan transparansi dalam pelayanan publik
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="../backend/auth/register.php" class="btn btn-warning btn-lg shadow-lampung-lg">
                            <i class="bi bi-person-plus-fill"></i> Daftar Sekarang
                        </a>
                        <a href="../backend/auth/login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Sudah Punya Akun? Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer dengan Tema Lampung -->
    <footer class="footer-lampung">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-lampung-gold mb-3">
                        <i class="bi bi-geo-alt-fill"></i> LampungSmart
                    </h5>
                    <p class="mb-3">
                        Platform Good Governance digital terintegrasi untuk melayani masyarakat Lampung 
                        dalam pelaporan infrastruktur dan perizinan UMKM secara transparan dan efisien.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-lampung-gold mb-3">Navigasi</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#tentang">Tentang Kami</a></li>
                        <li class="mb-2"><a href="#layanan">Layanan</a></li>
                        <li class="mb-2"><a href="#cara-kerja">Cara Kerja</a></li>
                        <li class="mb-2"><a href="../backend/auth/login.php">Login</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5 class="text-lampung-gold mb-3">Layanan</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Pengaduan Infrastruktur</a></li>
                        <li class="mb-2"><a href="#">Perizinan UMKM</a></li>
                        <li class="mb-2"><a href="#">FAQ</a></li>
                        <li class="mb-2"><a href="#">Hubungi Kami</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-lampung-gold mb-3">Kontak</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:admin@lampungsmart.go.id">admin@lampungsmart.go.id</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:0721123456">0721-123456</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-whatsapp me-2"></i>
                            <a href="https://wa.me/6282112345678">0821-1234-5678</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-building me-2"></i>
                            Pemerintah Provinsi Lampung
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary opacity-25">
            
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <p class="mb-0 small opacity-75">
                        &copy; <?php echo date('Y'); ?> LampungSmart. All Rights Reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 small opacity-75">
                        <a href="#" class="text-white me-3">Kebijakan Privasi</a>
                        <a href="#" class="text-white me-3">Syarat & Ketentuan</a>
                        <a href="#" class="text-white">Sitemap</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        /**
         * Smooth Scroll untuk anchor links
         */
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        const offsetTop = target.offsetTop - 70; // Offset untuk fixed navbar
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        /**
         * Navbar scroll effect - transparansi saat di top
         */
        const navbar = document.querySelector('.navbar-lampung');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lampung-lg');
            } else {
                navbar.classList.remove('shadow-lampung-lg');
            }
        });

        /**
         * Animate on scroll - untuk fade-in animations
         */
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe semua elemen dengan class fade-in-lampung
        document.querySelectorAll('.fade-in-lampung').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });

        /**
         * Counter animation untuk stats section
         */
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 50; // 50 steps
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + '+';
                }
            }, 30);
        }

        // Trigger counter animation saat stats section terlihat
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            const statsObserver = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const statNumbers = entry.target.querySelectorAll('.stat-number');
                        statNumbers.forEach((stat, index) => {
                            const targets = [1000, 500, 200]; // Nilai target untuk setiap stat
                            setTimeout(() => {
                                const icon = stat.querySelector('i');
                                const iconHTML = icon ? icon.outerHTML + ' ' : '';
                                stat.innerHTML = iconHTML;
                                animateCounter(stat, targets[index]);
                            }, index * 200);
                        });
                        statsObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            statsObserver.observe(statsSection);
        }

        /**
         * Active nav link highlight based on scroll position
         */
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });

        /**
         * Hover effect untuk process cards
         */
        document.querySelectorAll('.process-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.borderColor = 'var(--lampung-blue)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.borderColor = 'var(--lampung-gray-200)';
            });
        });

        /**
         * Console welcome message
         */
        console.log('%c🎉 Selamat Datang di LampungSmart! ', 'background: #00308F; color: #FFD700; font-size: 20px; padding: 10px;');
        console.log('%cPlatform Good Governance Digital Provinsi Lampung', 'color: #009639; font-size: 14px;');
        console.log('%c💻 Dikembangkan dengan ❤️ untuk kemajuan Lampung', 'color: #666; font-size: 12px;');

        /**
         * Prevent double form submission (untuk halaman selanjutnya)
         */
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                    setTimeout(() => {
                        submitBtn.disabled = false;
                    }, 3000);
                }
            });
        });

        /**
         * Back to top button (smooth)
         */
        const backToTopBtn = document.createElement('button');
        backToTopBtn.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i>';
        backToTopBtn.className = 'btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle d-none';
        backToTopBtn.style.width = '50px';
        backToTopBtn.style.height = '50px';
        backToTopBtn.style.zIndex = '1000';
        document.body.appendChild(backToTopBtn);

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.remove('d-none');
            } else {
                backToTopBtn.classList.add('d-none');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
