<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: /auth/login.php");
    exit;
}
$total_laporan = 0; 
$total_umkm = 0;
$status_akun = "Terverifikasi"; 

require '../frontend/layout/header.html';
require '../frontend/layout/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-end mb-5">
    <div>
        <h2 class="fw-bold text-brand-primary mb-2">Dashboard</h2>
        <p class="text-muted mb-0">Selamat datang kembali, <span class="text-brand-primary fw-bold"><?php echo htmlspecialchars($_SESSION['nama']); ?></span> 👋</p>
    </div>
    <div class="d-none d-md-block">
        <button class="btn btn-white border shadow-sm px-3 py-2 rounded-3 text-muted">
            <i class="far fa-calendar-alt me-2"></i> <?php echo date('d F Y'); ?>
        </button>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card card-dashboard border-0 h-100 p-2">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-user-shield fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 text-small">Status Akun</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $status_akun; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-dashboard border-0 h-100 p-2">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3 text-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-bullhorn fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 text-small">Total Laporan</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $total_laporan; ?> Pengaduan</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-dashboard border-0 h-100 p-2">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-success bg-opacity-10 p-3 rounded-4 me-3 text-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-store fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 text-small">UMKM Terdaftar</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $total_umkm; ?> Usaha</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold text-brand-primary mb-4">Layanan Cepat</h5>
<div class="row g-4 mb-5">
    
    <div class="col-md-6">
        <div class="card card-dashboard border-0 text-white overflow-hidden" 
             style="background: linear-gradient(135deg, #0d1b3e 0%, #1a3c7d 100%); border-radius: 15px;">
            <div class="card-body p-5 position-relative">
                <h3 class="fw-bold mb-3">Lapor Masalah?</h3>
                <p class="opacity-75 mb-4" style="max-width: 80%;">
                    Temukan jalan rusak, sampah menumpuk, atau lampu jalan mati? 
                    Laporkan sekarang agar segera ditindaklanjuti.
                </p>
                
                <a href="pengaduan_form.php" class="btn border-0 fw-bold px-4 py-2" 
                   style="background-color: #ffffff1a; color: white; backdrop-filter: blur(5px);">
                    <i class="fas fa-plus-circle me-2"></i> Buat Laporan Baru
                </a>

                <i class="fas fa-bullhorn position-absolute opacity-25" 
                   style="font-size: 10rem; right: -30px; bottom: -30px; transform: rotate(-15deg);"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-dashboard border-0 bg-white overflow-hidden" style="border-radius: 15px;">
            <div class="card-body p-5 position-relative">
                <h3 class="fw-bold text-brand-primary mb-3">Daftar UMKM</h3>
                <p class="text-muted mb-4" style="max-width: 80%;">
                    Punya usaha mikro/kecil? Daftarkan usaha Anda agar terdata resmi di pemerintah 
                    dan dapatkan kemudahan izin.
                </p>
                
                <a href="umkm_daftar.php" class="btn btn-outline-primary fw-bold px-4 py-2">
                    <i class="fas fa-file-signature me-2"></i> Daftar Sekarang
                </a>

                <i class="fas fa-store position-absolute text-muted opacity-10" 
                   style="font-size: 12rem; right: -40px; bottom: -40px; transform: rotate(0deg);"></i>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold text-brand-primary mb-4">Akun Saya</h5>
<div class="row g-4 mb-5">
    <div class="col-md-12">
        <div class="card card-dashboard border-0 bg-white overflow-hidden" style="border-radius: 15px;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <img src="../../uploads/profile/<?php echo isset($_SESSION['profile_photo']) ? htmlspecialchars($_SESSION['profile_photo']) : 'default.jpg'; ?>" 
                             alt="Foto Profil" 
                             style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #00308F;"
                             onerror="this.src='../../uploads/profile/default.jpg'">
                    </div>
                    <div class="col-md-7">
                        <h4 class="fw-bold text-brand-primary mb-1"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-user-shield me-1"></i> Status: <span class="badge bg-success">Terverifikasi</span>
                        </p>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-info-circle me-1"></i> Kelola profil, ubah password, dan pengaturan akun Anda
                        </p>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="../frontend/profile.php" class="btn btn-primary fw-bold px-4 py-2">
                            <i class="fas fa-user-edit me-2"></i> Lihat Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center text-muted mt-5 mb-3">
    <small>&copy; 2025 LampungSmart - Pemerintah Provinsi Lampung</small>
</div>

<?php
require '../frontend/layout/footer.html';
?>