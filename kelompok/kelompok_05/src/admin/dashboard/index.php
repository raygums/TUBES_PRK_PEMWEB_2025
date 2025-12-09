<?php
/**
 * Admin Dashboard - Halaman Utama
 * Menampilkan overview dan statistik pengaduan, UMKM, dan users
 */

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Cek role user (hanya admin yang bisa akses)
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../public/index.php');
    exit;
}

// Koneksi database
require_once '../../config/config.php';

// ==================== PENGADUAN STATISTICS ====================
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

// ==================== UMKM STATISTICS ====================
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

// ==================== USER STATISTICS ====================
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

// ==================== PENGADUAN TREND (30 hari) ====================
$pengaduan_trend = [];
$trend_query = "SELECT 
                DATE(created_at) as tanggal,
                COUNT(*) as jumlah
               FROM pengaduan
               WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               GROUP BY DATE(created_at)
               ORDER BY created_at ASC";
$result = $conn->query($trend_query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pengaduan_trend[] = $row;
    }
}

// ==================== RECENT PENGADUAN ====================
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

// Fungsi untuk format tanggal
function format_tanggal($date) {
    $timestamp = strtotime($date);
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_index = date('n', $timestamp) - 1;
    return date('d', $timestamp) . ' ' . $months[$month_index] . ' ' . date('Y H:i', $timestamp);
}

// Fungsi untuk time ago
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

// Fungsi untuk badge status
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
        case 'approved':
            return '<span class="badge bg-success text-white"><i class="bi bi-check-circle"></i> Approved</span>';
        case 'rejected':
            return '<span class="badge bg-danger text-white"><i class="bi bi-x-circle"></i> Rejected</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}
?>

<?php include '../layouts/admin_header.php'; ?>

<div class="admin-container">
    <?php include '../layouts/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-content">
                <h1><i class="bi bi-speedometer2"></i> Dashboard Admin</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?>! Berikut adalah overview sistem LampungSmart.</p>
                <small class="text-muted">Updated: <?php echo date('d M Y, H:i'); ?></small>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="statistics-grid">
            
            <!-- Pengaduan Card -->
            <div class="stat-card">
                <div class="stat-icon pengaduan">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Pengaduan</div>
                    <div class="stat-number"><?php echo $pengaduan_stats['total'] ?? 0; ?></div>
                    <div class="stat-breakdown">
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> <?php echo $pengaduan_stats['pending'] ?? 0; ?></span>
                        <span class="badge bg-info"><i class="bi bi-arrow-repeat"></i> <?php echo $pengaduan_stats['proses'] ?? 0; ?></span>
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?php echo $pengaduan_stats['selesai'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- UMKM Card -->
            <div class="stat-card">
                <div class="stat-icon umkm">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total UMKM</div>
                    <div class="stat-number"><?php echo $umkm_stats['total'] ?? 0; ?></div>
                    <div class="stat-breakdown">
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> <?php echo $umkm_stats['pending'] ?? 0; ?></span>
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?php echo $umkm_stats['approved'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Users Card -->
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-number"><?php echo $user_stats['total'] ?? 0; ?></div>
                    <div class="stat-breakdown">
                        <span class="badge bg-primary"><i class="bi bi-person-check"></i> <?php echo $user_stats['warga'] ?? 0; ?> Warga</span>
                        <span class="badge bg-secondary"><i class="bi bi-shield-check"></i> <?php echo $user_stats['admin'] ?? 0; ?> Admin</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="stat-card">
                <div class="stat-icon actions">
                    <i class="bi bi-lightning"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Aksi Cepat</div>
                    <div class="quick-actions">
                        <a href="pengaduan.php?filter=pending" class="btn btn-sm btn-warning">Lihat Pending</a>
                        <a href="umkm.php?filter=pending" class="btn btn-sm btn-info">Approve UMKM</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <h5>Tren Pengaduan (30 Hari Terakhir)</h5>
                <canvas id="trendChart" height="300"></canvas>
            </div>
            
            <div class="chart-container">
                <h5>Distribusi Status Pengaduan</h5>
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Recent Pengaduan -->
        <div class="recent-section">
            <div class="section-header">
                <h5>Pengaduan Terbaru</h5>
                <a href="pengaduan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            
            <?php if (count($recent_pengaduan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Warga</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Tanggapan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_pengaduan as $p): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars(substr($p['judul'], 0, 40)); ?></strong>
                                        <?php if (strlen($p['judul']) > 40): ?>...<?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nama_warga']); ?></td>
                                    <td>
                                        <small><?php echo time_ago($p['created_at']); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo format_tanggal($p['created_at']); ?></small>
                                    </td>
                                    <td><?php echo get_status_badge($p['status']); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo $p['jumlah_tanggapan']; ?></span></td>
                                    <td>
                                        <a href="pengaduan.php#pengaduan-<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada pengaduan masuk</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    :root {
        --lampung-green: #009639;
        --lampung-red: #D60000;
        --lampung-blue: #00308F;
        --lampung-gold: #FFD700;
        --lampung-charcoal: #212121;
        --lampung-green-light: #E8F5E9;
    }
    
    .admin-container {
        display: flex;
        margin-top: 70px;
    }
    
    .main-content {
        flex: 1;
        margin-left: 280px;
        padding: 30px;
        background-color: #f5f5f5;
        min-height: calc(100vh - 70px);
    }
    
    /* ==================== PAGE HEADER ==================== */
    .page-header {
        background: linear-gradient(135deg, var(--lampung-blue) 0%, var(--lampung-green) 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .page-header p {
        margin: 5px 0;
        opacity: 0.95;
    }
    
    /* ==================== STATISTICS GRID ==================== */
    .statistics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 20px;
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: white;
        flex-shrink: 0;
    }
    
    .stat-icon.pengaduan {
        background: linear-gradient(135deg, var(--lampung-blue), #0066cc);
    }
    
    .stat-icon.umkm {
        background: linear-gradient(135deg, var(--lampung-green), #006b2f);
    }
    
    .stat-icon.users {
        background: linear-gradient(135deg, #FF9800, #F57C00);
    }
    
    .stat-icon.actions {
        background: linear-gradient(135deg, #9C27B0, #7B1FA2);
    }
    
    .stat-info {
        flex: 1;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--lampung-charcoal);
        margin-bottom: 10px;
    }
    
    .stat-breakdown {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .stat-breakdown .badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    
    .quick-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    /* ==================== CHARTS SECTION ==================== */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .chart-container h5 {
        margin-bottom: 20px;
        color: var(--lampung-charcoal);
        font-weight: 700;
    }
    
    /* ==================== RECENT SECTION ==================== */
    .recent-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .section-header h5 {
        margin: 0;
        color: var(--lampung-charcoal);
        font-weight: 700;
    }
    
    .table thead {
        background-color: #f8f9fa;
    }
    
    .table thead th {
        color: var(--lampung-charcoal);
        font-weight: 600;
        border: none;
        padding: 12px;
    }
    
    .table tbody td {
        padding: 12px;
        border-color: #f0f0f0;
    }
    
    .table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
        display: block;
    }
    
    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 992px) {
        .main-content {
            margin-left: 0;
        }
        
        .admin-sidebar {
            left: -280px;
        }
        
        .charts-section {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .page-header {
            padding: 20px;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .statistics-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-card {
            flex-direction: column;
        }
        
        .stat-icon {
            width: 100%;
        }
    }
</style>

<script>
    // Data untuk chart trend
    const trendData = <?php echo json_encode($pengaduan_trend); ?>;
    const trendLabels = trendData.map(item => {
        const date = new Date(item.tanggal);
        return date.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][date.getMonth()];
    });
    const trendValues = trendData.map(item => item.jumlah);
    
    // Trend Chart
    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pengaduan Masuk',
                    data: trendValues,
                    borderColor: '#009639',
                    backgroundColor: 'rgba(0, 150, 57, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#009639',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Diproses', 'Selesai', 'Ditolak'],
                datasets: [{
                    data: [
                        <?php echo $pengaduan_stats['pending'] ?? 0; ?>,
                        <?php echo $pengaduan_stats['proses'] ?? 0; ?>,
                        <?php echo $pengaduan_stats['selesai'] ?? 0; ?>,
                        <?php echo $pengaduan_stats['ditolak'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#FFC107',
                        '#17A2B8',
                        '#28A745',
                        '#DC3545'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>

<?php include '../layouts/admin_footer.php'; ?>
