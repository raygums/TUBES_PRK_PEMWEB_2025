<!-- Navbar Landing Page -->
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
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard-publik.php') ? 'active' : ''; ?>" href="dashboard-publik.php">Dashboard Publik</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'faq.php') ? 'active' : ''; ?>" href="faq.php">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'hubungi-kami.php') ? 'active' : ''; ?>" href="hubungi-kami.php">Hubungi Kami</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../backend/auth/login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-warning text-dark px-3 ms-2 rounded" href="../backend/auth/register.php">
                        <i class="bi bi-person-plus"></i> Daftar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
// Fix navbar active state yang tidak hilang saat scroll
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan hanya current page yang active
    const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });
    
    // Navbar shadow effect on scroll
    const navbar = document.querySelector('.navbar-lampung');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('shadow-lampung-lg');
        } else {
            navbar.classList.remove('shadow-lampung-lg');
        }
    });
});
</script>
