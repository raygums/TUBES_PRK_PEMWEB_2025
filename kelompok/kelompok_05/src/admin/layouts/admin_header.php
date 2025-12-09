<?php
/**
 * Admin Header/Navbar
 * Navigasi khusus untuk halaman admin
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LampungSmart</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- LampungSmart Theme -->
    <link href="../../assets/css/lampung-theme.css" rel="stylesheet">
    
    <style>
        :root {
            --lampung-green: #009639;
            --lampung-red: #D60000;
            --lampung-blue: #00308F;
            --lampung-gold: #FFD700;
            --lampung-charcoal: #212121;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        /* ==================== NAVBAR ==================== */
        .admin-navbar {
            background: linear-gradient(135deg, var(--lampung-blue) 0%, var(--lampung-green) 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .admin-navbar .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-navbar .navbar-brand i {
            font-size: 2rem;
        }
        
        .admin-navbar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin: 0 5px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .admin-navbar .nav-link:hover {
            color: white !important;
        }
        
        .admin-navbar .nav-link.active {
            color: var(--lampung-gold) !important;
        }
        
        .admin-navbar .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--lampung-gold);
            border-radius: 2px;
        }
        
        /* User Profile Dropdown */
        .admin-navbar .dropdown-menu {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .admin-navbar .dropdown-item {
            color: var(--lampung-charcoal);
            font-weight: 500;
            padding: 0.7rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .admin-navbar .dropdown-item:hover {
            background-color: var(--lampung-green-light);
            color: var(--lampung-green);
        }
        
        .admin-navbar .dropdown-item.logout {
            color: var(--lampung-red);
            border-top: 1px solid #ddd;
        }
        
        .admin-navbar .dropdown-item.logout:hover {
            background-color: var(--lampung-red-light);
        }
        
        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 12px;
            border-radius: 6px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .user-profile-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .user-profile-btn img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="admin-navbar navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-alt"></i>
                <span>LampungSmart Admin</span>
            </a>
            
            <!-- Navbar Toggle for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="filter: brightness(0) invert(1);"></span>
            </button>
            
            <!-- Navbar Content -->
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <!-- Left Nav Links -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pengaduan.php">
                            <i class="bi bi-chat-dots"></i> Pengaduan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="umkm.php">
                            <i class="bi bi-shop"></i> UMKM
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                </ul>
                
                <!-- Right Side: User Profile -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <button class="btn user-profile-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="../../assets/images/default-avatar.png" alt="Avatar" class="rounded-circle" style="width: 28px; height: 28px;">
                            <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                            <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../../profile/profile.php"><i class="bi bi-person-circle"></i> Profil Saya</a></li>
                            <li><a class="dropdown-item" href="../../profile/profile.php?tab=security"><i class="bi bi-lock"></i> Keamanan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item logout" href="../../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
