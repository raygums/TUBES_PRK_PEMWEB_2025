<?php
$role = $_SESSION['role'] ?? 'guest'; 
?>

<div class="sidebar p-3 d-flex flex-column" style="width: 260px; min-height: 100vh;">
    
    <div class="text-center mt-3 mb-5">
        <?php 
        $is_frontend = strpos($_SERVER['PHP_SELF'], 'frontend') !== false;
        $dashboard_link = $is_frontend ? '../backend/dashboard_warga.php' : 'dashboard_warga.php';
        $logo_path = '../assets/images/logo-lampung.png';
        ?>
        <a href="<?php echo $dashboard_link; ?>" class="text-decoration-none d-flex align-items-center justify-content-center sidebar-logo-link">
            <img src="<?php echo $logo_path; ?>" alt="Logo Lampung" class="logo-lampung-sidebar"> 
            <span class="text-white fw-bold fs-5 ms-2">Lampung<span class="text-warning">Smart</span></span>
        </a>
    </div>
    
    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a href="<?php echo (strpos($_SERVER['PHP_SELF'], 'frontend') !== false) ? '../backend/dashboard_warga.php' : 'dashboard_warga.php'; ?>" class="nav-link d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_warga.php' ? 'active' : ''; ?>">
                <i class="fas fa-home me-3"></i> Dashboard
            </a>
        </li>

        <?php if ($role == 'warga'): ?>
            <li class="nav-header text-uppercase text-white-50 fs-7 fw-bold mt-3 mb-2 px-3" style="font-size: 0.75rem;">Layanan Warga</li>
            
            <li class="nav-item">
                <a href="../backend/pengaduan_form.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-bullhorn me-3"></i> Lapor Jalan/Sampah
                </a>
            </li>
            <li class="nav-item">
                <a href="../backend/pengaduan_riwayat.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-history me-3"></i> Riwayat Laporan
                </a>
            </li>
            
            <li class="nav-header text-uppercase text-white-50 fs-7 fw-bold mt-3 mb-2 px-3" style="font-size: 0.75rem;">UMKM</li>
            
            <li class="nav-item">
                <a href="../backend/umkm_daftar.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-store me-3"></i> Daftar UMKM
                </a>
            </li>
            <li class="nav-item">
                <a href="../backend/umkm_status.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-file-contract me-3"></i> Status Izin
                </a>
            </li>
            
            <li class="nav-header text-uppercase text-white-50 fs-7 fw-bold mt-3 mb-2 px-3" style="font-size: 0.75rem;">Akun</li>
            
            <li class="nav-item">
                <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'profile.php' : '../frontend/profile.php'; ?>" class="nav-link d-flex align-items-center <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle me-3"></i> Profil Saya
                </a>
            </li>
        
        <?php elseif ($role == 'admin'): ?>
            <li class="nav-header text-uppercase text-white-50 fs-7 fw-bold mt-3 mb-2 px-3" style="font-size: 0.75rem;">Panel Admin</li>
            
            <li class="nav-item">
                <a href="../backend/admin_pengaduan.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-check-double me-3"></i> Validasi Laporan
                </a>
            </li>
            <li class="nav-item">
                <a href="../backend/admin_umkm.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-user-check me-3"></i> Validasi UMKM
                </a>
            </li>
            <li class="nav-item">
                <a href="../backend/kelola_user.php" class="nav-link d-flex align-items-center">
                    <i class="fas fa-users me-3"></i> Kelola User
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="mt-auto mb-4">
        <hr class="border-secondary opacity-50">
        <a href="<?php echo (strpos($_SERVER['PHP_SELF'], 'frontend') !== false) ? '../backend/auth/logout.php' : 'auth/logout.php'; ?>" class="nav-link text-danger d-flex align-items-center fw-bold" onclick="return confirm('Yakin mau keluar?')">
            <i class="fas fa-sign-out-alt me-3"></i> Logout
        </a>
    </div>
</div>

<div class="flex-grow-1" style="background-color: #f8f9fa;">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm d-md-none p-3 mb-3">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold text-primary">LampungSmart</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
    
    <div class="p-4">