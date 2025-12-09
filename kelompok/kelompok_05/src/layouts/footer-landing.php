<!-- Footer Landing Page -->
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
                    <a href="#" class="text-white fs-4" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-4" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-4" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-4" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5 class="text-lampung-gold mb-3">Navigasi</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php">Beranda</a></li>
                    <li class="mb-2"><a href="dashboard-publik.php">Dashboard Publik</a></li>
                    <li class="mb-2"><a href="faq.php">FAQ</a></li>
                    <li class="mb-2"><a href="hubungi-kami.php">Hubungi Kami</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <h5 class="text-lampung-gold mb-3">Layanan</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="../dashboard/dashboard_warga.php">Pengaduan Infrastruktur</a></li>
                    <li class="mb-2"><a href="../dashboard/dashboard_warga.php">Perizinan UMKM</a></li>
                    <li class="mb-2"><a href="../auth/login.php">Login</a></li>
                    <li class="mb-2"><a href="../auth/register.php">Daftar</a></li>
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

<style>
/* Footer links hover effects */
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
