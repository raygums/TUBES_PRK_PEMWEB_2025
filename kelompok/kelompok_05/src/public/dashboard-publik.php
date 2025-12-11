<?php
/**
 * LampungSmart - Dashboard Publik Real-Time
 * Public Dashboard & Priority Voting
 * Zero-Auth | WCAG 2.2 AA Compliant
 */

session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Publik LampungSmart - Pantau aktivitas pengaduan secara real-time dan bantu prioritaskan pengaduan mendesak">
    <title>Dashboard Publik - LampungSmart</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- LampungSmart Theme -->
    <link href="../assets/css/lampung-theme.css" rel="stylesheet">
    <link href="../assets/css/landing-page.css" rel="stylesheet">
    <link href="../assets/css/logo-navbar.css" rel="stylesheet">
    <link href="../assets/css/dashboard-voting.css" rel="stylesheet">
</head>
<body>

    <?php include '../layouts/navbar-landing.php'; ?>

    <!-- Hero Section -->
    <section class="hero-lampung" style="padding: 80px 0;">
        <div class="container">
            <div class="hero-content text-center">
                <div class="mb-4">
                    <span class="badge bg-lampung-gold text-dark px-4 py-2 fs-6">
                        <i class="bi bi-broadcast"></i> Dashboard Real-Time
                    </span>
                </div>
                <h1 class="hero-title" style="font-size: 2.5rem;">Dashboard Publik LampungSmart</h1>
                <p class="hero-subtitle">
                    Pantau aktivitas pengaduan secara transparan dan bantu prioritaskan penanganan yang paling mendesak
                </p>
            </div>
        </div>
    </section>

    <!-- Public Dashboard Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2 class="section-title">Metrik Real-Time</h2>
                    <p class="section-subtitle">
                        Pantau aktivitas platform secara langsung
                        <span class="badge bg-lampung-gold-light text-lampung-gold ms-2">
                            <i class="bi bi-broadcast"></i> Data Simulasi
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Live Processing Counter -->
                <div class="col-md-6">
                    <div class="card shadow-lampung-md border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title text-lampung-blue-dark mb-1">
                                        <i class="bi bi-hourglass-split"></i> Sedang Diproses
                                    </h5>
                                    <p class="text-muted small mb-0">Pengaduan dalam antrian</p>
                                </div>
                                <span class="badge bg-lampung-blue-light text-lampung-blue">
                                    <i class="bi bi-arrow-clockwise"></i> Live
                                </span>
                            </div>
                            <div class="text-center py-4">
                                <div id="live-counter" class="display-2 fw-bold text-lampung-blue mb-2" 
                                     aria-live="polite" aria-atomic="true">47</div>
                                <div class="text-muted">pengaduan aktif</div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-clock-history"></i> Update setiap 5 detik
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Response Time Gauge -->
                <div class="col-md-6">
                    <div class="card shadow-lampung-md border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title text-lampung-green-dark mb-1">
                                        <i class="bi bi-speedometer2"></i> Waktu Respons Rata-Rata
                                    </h5>
                                    <p class="text-muted small mb-0">Target: &lt; 4 jam</p>
                                </div>
                                <span class="badge bg-lampung-green-light text-lampung-green">
                                    <i class="bi bi-check-circle"></i> Optimal
                                </span>
                            </div>
                            <div class="text-center py-4">
                                <div class="response-gauge-container position-relative d-inline-block">
                                    <svg width="180" height="180" viewBox="0 0 180 180" class="response-gauge">
                                        <circle cx="90" cy="90" r="75" fill="none" stroke="#e9ecef" stroke-width="15"/>
                                        <circle id="response-gauge" cx="90" cy="90" r="75" fill="none" 
                                                stroke="#009639" stroke-width="15" stroke-linecap="round"
                                                stroke-dasharray="471" stroke-dashoffset="118"
                                                transform="rotate(-90 90 90)" 
                                                style="transition: stroke-dashoffset 0.5s ease;"/>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <div id="response-time" class="fs-1 fw-bold text-lampung-green">2.5</div>
                                        <div class="text-muted small">jam</div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Diperbarui secara real-time
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Priority Voting Widget -->
    <section class="voting-section py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="section-title">Bantu Prioritaskan Pengaduan</h2>
                    <p class="section-subtitle">
                        Vote pengaduan paling mendesak menurut Anda. Suara Anda membantu kami memprioritaskan penanganan.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-lampung-blue text-white px-3 py-2">
                            <i class="bi bi-hand-thumbs-up"></i> <span id="votes-remaining">3 votes remaining</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php
            // Mock complaints data (ethical simulation)
            $mockComplaints = [
                [
                    'id' => 'cmp-001',
                    'title' => 'Jalan Berlubang Besar di Jl. Merdeka (Fiktif)',
                    'description' => 'Lubang ukuran 2x3 meter menyebabkan kemacetan dan risiko kecelakaan',
                    'urgency' => 85,
                    'votes' => 247,
                    'location' => 'Jl. Merdeka No. 100 (Lokasi Contoh)',
                    'icon' => 'cone-striped',
                    'color' => 'red'
                ],
                [
                    'id' => 'cmp-002',
                    'title' => 'Lampu Jalan Mati - Area Pasar (Simulasi)',
                    'description' => 'Total 15 lampu mati, area gelap total sejak 3 hari lalu',
                    'urgency' => 72,
                    'votes' => 189,
                    'location' => 'Dekat Pasar Tradisional (Data Demo)',
                    'icon' => 'lightbulb',
                    'color' => 'blue'
                ],
                [
                    'id' => 'cmp-003',
                    'title' => 'Sampah Menumpuk Jl. Raya Utara (Test Data)',
                    'description' => 'Tumpukan sampah belum diangkut 3 hari, bau menyengat',
                    'urgency' => 68,
                    'votes' => 156,
                    'location' => 'Jl. Raya Utara Km 5 (Contoh)',
                    'icon' => 'trash',
                    'color' => 'green'
                ]
            ];
            ?>
            
            <div class="row g-4">
                <?php foreach ($mockComplaints as $index => $complaint): ?>
                <div class="col-lg-4">
                    <div class="complaint-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="complaint-icon bg-lampung-<?php echo $complaint['color']; ?>-light">
                                <i class="bi bi-<?php echo $complaint['icon']; ?> text-lampung-<?php echo $complaint['color']; ?>"></i>
                            </div>
                            <span class="badge bg-lampung-gold-light text-lampung-gold">
                                Urgensi: <?php echo $complaint['urgency']; ?>%
                            </span>
                        </div>
                        
                        <h5 class="complaint-title text-lampung-blue-dark mb-2">
                            <?php echo $complaint['title']; ?>
                        </h5>
                        
                        <p class="complaint-description text-muted small mb-3">
                            <?php echo $complaint['description']; ?>
                        </p>
                        
                        <div class="complaint-location text-muted small mb-3">
                            <i class="bi bi-geo-alt"></i> <?php echo $complaint['location']; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="vote-btn btn btn-primary btn-sm" 
                                    data-complaint-id="<?php echo $complaint['id']; ?>"
                                    aria-label="Vote untuk pengaduan ini">
                                <i class="bi bi-hand-thumbs-up"></i> Vote
                            </button>
                            <span class="vote-count-badge badge bg-lampung-gold text-dark" 
                                  id="vote-count-<?php echo $complaint['id']; ?>">
                                <i class="bi bi-people-fill"></i> <?php echo $complaint['votes']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <div class="alert alert-lampung-info border-left-lampung-blue">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Demo Mode:</strong> Voting menggunakan sessionStorage. Data akan reset setelah browser ditutup.
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- ARIA Live Region for Accessibility -->
    <div id="vote-announcer" class="visually-hidden" role="status" aria-live="polite" aria-atomic="true"></div>

        <!-- CTA Section -->
    <section class="py-5 bg-lampung-gradient-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Ingin Laporkan Pengaduan Sendiri?</h2>
                    <p class="lead mb-4">
                        Daftar sekarang dan mulai laporkan masalah infrastruktur di sekitar Anda
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="../auth/register.php" class="btn btn-warning btn-lg shadow-lampung-lg">
                            <i class="bi bi-person-plus-fill"></i> Daftar Sekarang
                        </a>
                        <a href="../auth/login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Sudah Punya Akun? Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../layouts/footer-landing.php'; ?>

    <!-- Rate Limit Modal -->
    <div class="modal fade" id="rateLimitModal" tabindex="-1" aria-labelledby="rateLimitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-lampung-red text-white">
                    <h5 class="modal-title" id="rateLimitModalLabel">
                        <i class="bi bi-exclamation-triangle"></i> Batas Voting Tercapai
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        <strong>Anda telah menggunakan 3 vote yang tersedia.</strong>
                    </p>
                    <p class="mb-0 text-muted">
                        Untuk mencegah penyalahgunaan sistem voting, setiap pengunjung dibatasi maksimal 3 vote per sesi.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dashboard Live Updates -->
    <script src="../assets/js/dashboard-live.js"></script>
    
    <!-- Voting Widget -->
    <script src="../assets/js/voting-widget.js"></script>
    
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
        
        /* Complaint card hover */
        .complaint-card {
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }
        .complaint-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 48, 143, 0.2) !important;
            border-color: var(--lampung-blue) !important;
        }
        
        /* Icon hover effect */
        .complaint-icon {
            transition: all 0.3s ease-in-out;
        }
        .complaint-card:hover .complaint-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        /* Vote button pulse effect */
        @keyframes pulse-vote {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .vote-btn:hover {
            animation: pulse-vote 0.6s ease-in-out;
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
    </style>

</body>
</html>
