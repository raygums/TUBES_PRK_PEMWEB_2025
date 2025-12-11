<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../public/index.php');
    exit;
}

require_once '../config/config.php';

$pengaduan_stats = [];
$pengaduan_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
                   FROM pengaduan";
$result = $conn->query($pengaduan_query);
if ($result) {
    $pengaduan_stats = $result->fetch_assoc();
}

$umkm_stats = [];
$umkm_query = "SELECT 
               COUNT(*) as total,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
               SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
               SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
              FROM umkm";
$result = $conn->query($umkm_query);
if ($result) {
    $umkm_stats = $result->fetch_assoc();
}

$user_stats = [];
$user_query = "SELECT 
               COUNT(*) as total,
               SUM(CASE WHEN role = 'warga' THEN 1 ELSE 0 END) as warga,
               SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin
              FROM users";
$result = $conn->query($user_query);
if ($result) {
    $user_stats = $result->fetch_assoc();
}

$recent_pengaduan = [];
$recent_query = "SELECT p.*, u.nama as nama_warga, COUNT(t.id) as jumlah_tanggapan
                 FROM pengaduan p
                 JOIN users u ON p.user_id = u.id
                 LEFT JOIN tanggapan t ON p.id = t.pengaduan_id
                 GROUP BY p.id
                 ORDER BY p.created_at DESC
                 LIMIT 5";
$result = $conn->query($recent_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_pengaduan[] = $row;
    }
}

function format_tanggal($date) {
    $timestamp = strtotime($date);
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_index = date('n', $timestamp) - 1;
    return date('d', $timestamp) . ' ' . $months[$month_index] . ' ' . date('Y H:i', $timestamp);
}

function time_ago($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "baru saja";
    } elseif ($diff < 3600) {
        return floor($diff / 60) . " menit lalu";
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . " jam lalu";
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . " hari lalu";
    } else {
        return date('d M Y', $timestamp);
    }
}

function get_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending</span>';
        case 'proses':
            return '<span class="badge bg-info text-white"><i class="bi bi-arrow-repeat"></i> Proses</span>';
        case 'selesai':
            return '<span class="badge bg-success text-white"><i class="bi bi-check-circle"></i> Selesai</span>';
        case 'ditolak':
            return '<span class="badge bg-danger text-white"><i class="bi bi-x-circle"></i> Ditolak</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

require '../layouts/header.php';
require '../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-end mb-5">
    <div>
        <h2 class="fw-bold text-brand-primary mb-2">Dashboard Admin</h2>
        <p class="text-muted mb-0">Selamat datang, <span class="text-brand-primary fw-bold"><?php echo htmlspecialchars($_SESSION['nama']); ?></span> 👋</p>
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
                    <i class="fas fa-bullhorn fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 text-small">Total Pengaduan</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $pengaduan_stats['total'] ?? 0; ?> Laporan</h5>
                    <small>
                        <span class="badge bg-warning text-dark"><?php echo $pengaduan_stats['pending'] ?? 0; ?> Pending</span>
                        <span class="badge bg-info"><?php echo $pengaduan_stats['proses'] ?? 0; ?> Proses</span>
                    </small>
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
                    <p class="text-muted mb-1 text-small">Total UMKM</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $umkm_stats['total'] ?? 0; ?> Usaha</h5>
                    <small>
                        <span class="badge bg-warning text-dark"><?php echo $umkm_stats['pending'] ?? 0; ?> Pending</span>
                        <span class="badge bg-success"><?php echo $umkm_stats['approved'] ?? 0; ?> Approved</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-dashboard border-0 h-100 p-2">
            <div class="card-body d-flex align-items-center p-4">
                <div class="bg-warning bg-opacity-10 p-3 rounded-4 me-3 text-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted mb-1 text-small">Total Users</p>
                    <h5 class="fw-bold mb-0 text-brand-primary"><?php echo $user_stats['total'] ?? 0; ?> User</h5>
                    <small>
                        <span class="badge bg-primary"><?php echo $user_stats['warga'] ?? 0; ?> Warga</span>
                        <span class="badge bg-secondary"><?php echo $user_stats['admin'] ?? 0; ?> Admin</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold text-brand-primary mb-4">Aksi Cepat</h5>
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card card-dashboard border-0 text-white overflow-hidden" 
             style="background: linear-gradient(135deg, #0d1b3e 0%, #1a3c7d 100%); border-radius: 15px;">
            <div class="card-body p-5 position-relative">
                <h3 class="fw-bold mb-3">Validasi Pengaduan</h3>
                <p class="opacity-75 mb-4" style="max-width: 80%;">
                    Ada <?php echo $pengaduan_stats['pending'] ?? 0; ?> pengaduan menunggu validasi. 
                    Segera proses agar warga mendapat tanggapan.
                </p>
                <a href="../pengaduan/admin_pengaduan.php" class="btn border-0 fw-bold px-4 py-2" 
                   style="background-color: #ffffff1a; color: white; backdrop-filter: blur(5px);">
                    <i class="fas fa-check-double me-2"></i> Lihat Pengaduan
                </a>
                <i class="fas fa-clipboard-check position-absolute opacity-25" 
                   style="font-size: 10rem; right: -30px; bottom: -30px; transform: rotate(-15deg);"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-dashboard border-0 bg-white overflow-hidden" style="border-radius: 15px;">
            <div class="card-body p-5 position-relative">
                <h3 class="fw-bold text-brand-primary mb-3">Validasi UMKM</h3>
                <p class="text-muted mb-4" style="max-width: 80%;">
                    Ada <?php echo $umkm_stats['pending'] ?? 0; ?> pengajuan UMKM menunggu persetujuan.
                    Verifikasi data untuk memberikan izin usaha.
                </p>
                <a href="../umkm/admin_umkm.php" class="btn btn-outline-primary fw-bold px-4 py-2">
                    <i class="fas fa-store me-2"></i> Kelola UMKM
                </a>
                <i class="fas fa-store position-absolute text-muted opacity-10" 
                   style="font-size: 12rem; right: -40px; bottom: -40px; transform: rotate(0deg);"></i>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold text-brand-primary mb-4">Pengaduan Terbaru</h5>
<div class="card card-dashboard border-0 bg-white" style="border-radius: 15px;">
    <div class="card-body p-4">
        <?php if (count($recent_pengaduan) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul</th>
                            <th>Warga</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_pengaduan as $p): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars(substr($p['judul'], 0, 35)); ?></strong>
                                    <?php if (strlen($p['judul']) > 35): ?>...<?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['nama_warga']); ?></td>
                                <td><small class="text-muted"><?php echo time_ago($p['created_at']); ?></small></td>
                                <td><?php echo get_status_badge($p['status']); ?></td>
                                <td>
                                    <a href="../pengaduan/admin_pengaduan.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada pengaduan masuk</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="text-center text-muted mt-5 mb-3">
    <small>&copy; 2025 LampungSmart - Pemerintah Provinsi Lampung</small>
</div>

<?php require '../layouts/footer.php'; ?>
