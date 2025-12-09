<?php
/**
 * Admin Sidebar
 * Sidebar navigasi khusus admin dengan menu untuk berbagai fitur
 */
?>
<!-- ==================== ADMIN SIDEBAR ==================== -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h5 class="mb-0">Menu Admin</h5>
        <button class="btn-close-sidebar d-lg-none" id="closeSidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <div class="nav-section">
            <a href="index.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <!-- Pengaduan Management -->
        <div class="nav-section">
            <div class="nav-section-title">Manajemen Pengaduan</div>
            <a href="pengaduan.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) === 'pengaduan.php') ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                <span>Daftar Pengaduan</span>
            </a>
            <a href="pengaduan.php?filter=pending" class="nav-item-sub">
                <i class="bi bi-hourglass-split"></i>
                <span>Pending</span>
            </a>
            <a href="pengaduan.php?filter=proses" class="nav-item-sub">
                <i class="bi bi-arrow-repeat"></i>
                <span>Diproses</span>
            </a>
            <a href="pengaduan.php?filter=selesai" class="nav-item-sub">
                <i class="bi bi-check-circle"></i>
                <span>Selesai</span>
            </a>
        </div>
        
        <!-- UMKM Management -->
        <div class="nav-section">
            <div class="nav-section-title">Manajemen UMKM</div>
            <a href="umkm.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) === 'umkm.php') ? 'active' : ''; ?>">
                <i class="bi bi-shop"></i>
                <span>Daftar UMKM</span>
            </a>
            <a href="umkm.php?filter=pending" class="nav-item-sub">
                <i class="bi bi-hourglass-split"></i>
                <span>Pending</span>
            </a>
            <a href="umkm.php?filter=approved" class="nav-item-sub">
                <i class="bi bi-check-circle"></i>
                <span>Approved</span>
            </a>
        </div>
        
        <!-- User Management -->
        <div class="nav-section">
            <div class="nav-section-title">Manajemen User</div>
            <a href="users.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) === 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                <span>Daftar User</span>
            </a>
            <a href="users.php?role=warga" class="nav-item-sub">
                <i class="bi bi-person-check"></i>
                <span>Warga</span>
            </a>
            <a href="users.php?role=admin" class="nav-item-sub">
                <i class="bi bi-shield-check"></i>
                <span>Admin</span>
            </a>
        </div>
        
        <!-- Settings -->
        <div class="nav-section mt-5">
            <div class="nav-section-title">Pengaturan</div>
            <a href="../../profile/profile.php" class="nav-item">
                <i class="bi bi-person-circle"></i>
                <span>Profil Saya</span>
            </a>
            <a href="../../auth/logout.php" class="nav-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<!-- Sidebar Overlay untuk Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    :root {
        --lampung-green: #009639;
        --lampung-blue: #00308F;
        --lampung-charcoal: #212121;
    }
    
    /* ==================== SIDEBAR ==================== */
    .admin-sidebar {
        background: white;
        border-right: 1px solid #e0e0e0;
        height: calc(100vh - 70px);
        position: fixed;
        left: 0;
        top: 70px;
        width: 280px;
        overflow-y: auto;
        padding: 20px 0;
        z-index: 999;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
    }
    
    .sidebar-header {
        padding: 0 20px 20px 20px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sidebar-header h5 {
        color: var(--lampung-charcoal);
        font-weight: 700;
        margin: 0;
    }
    
    .btn-close-sidebar {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: var(--lampung-charcoal);
        cursor: pointer;
    }
    
    .sidebar-nav {
        display: flex;
        flex-direction: column;
    }
    
    .nav-section {
        padding: 10px 0;
    }
    
    .nav-section:not(:first-child) {
        border-top: 1px solid #f0f0f0;
        padding-top: 15px;
    }
    
    .nav-section-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #999;
        padding: 10px 20px;
        letter-spacing: 0.5px;
    }
    
    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        color: #555;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .nav-item:hover {
        background-color: #f8f9fa;
        color: var(--lampung-green);
        padding-left: 25px;
    }
    
    .nav-item.active {
        background: linear-gradient(90deg, var(--lampung-green-light) 0%, transparent 100%);
        color: var(--lampung-green);
        border-left: 4px solid var(--lampung-green);
        padding-left: 20px;
    }
    
    .nav-item i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }
    
    .nav-item-sub {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 20px 8px 50px;
        color: #888;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .nav-item-sub:hover {
        color: var(--lampung-green);
        background-color: #f8f9fa;
    }
    
    .nav-item-sub i {
        width: 16px;
        font-size: 0.95rem;
    }
    
    .nav-item.text-danger {
        color: #D60000;
    }
    
    .nav-item.text-danger:hover {
        background-color: #FFEBEE;
    }
    
    /* Sidebar Overlay untuk Mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 70px;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
    }
    
    .sidebar-overlay.show {
        display: block;
    }
    
    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 992px) {
        .admin-sidebar {
            left: -280px;
            transition: left 0.3s ease;
        }
        
        .admin-sidebar.show {
            left: 0;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
    }
    
    @media (max-width: 768px) {
        .admin-sidebar {
            width: 250px;
        }
        
        .nav-item {
            padding: 10px 15px;
        }
        
        .nav-item-sub {
            padding: 6px 15px 6px 45px;
        }
    }
</style>

<script>
    // Toggle sidebar pada mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        
        // Close sidebar saat overlay diklik
        overlay?.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
        
        // Close sidebar saat tombol close diklik
        closeSidebarBtn?.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    });
</script>
