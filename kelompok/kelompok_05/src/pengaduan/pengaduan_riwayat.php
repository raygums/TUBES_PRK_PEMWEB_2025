<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'warga') {
    header('Location: ../public/index.php');
    exit;
}

require_once '../config/config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "SELECT p.*, COUNT(t.id) as jumlah_tanggapan 
          FROM pengaduan p 
          LEFT JOIN tanggapan t ON p.id = t.pengaduan_id 
          WHERE p.user_id = ?";
$params = [$_SESSION['user_id']];
$types = 'i';

if (!empty($search)) {
    $query .= " AND (p.judul LIKE ? OR p.deskripsi LIKE ? OR p.lokasi LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

if (!empty($filter_status) && in_array($filter_status, ['pending', 'proses', 'selesai', 'ditolak'])) {
    $query .= " AND p.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$pengaduan_list = [];

while ($row = $result->fetch_assoc()) {
    $pengaduan_list[] = $row;
}
$stmt->close();

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
            return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Menunggu Verifikasi</span>';
        case 'proses':
            return '<span class="badge bg-info text-white"><i class="fas fa-sync-alt me-1"></i> Sedang Diproses</span>';
        case 'selesai':
            return '<span class="badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
        case 'ditolak':
            return '<span class="badge bg-danger text-white"><i class="fas fa-times-circle me-1"></i> Ditolak</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<style>
.page-hero {
    background: linear-gradient(135deg, #f7ba06ff 10%, #04225dff 70%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}
.page-hero h2 {
    font-weight: 800;
    margin-bottom: 10px;
}
.page-hero p {
    opacity: 0.9;
    margin-bottom: 0;
}
.btn-new-pengaduan {
    background-color: white;
    color: #009639;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}
.btn-new-pengaduan:hover {
    background-color: #f8f9fa;
    color: #007a2f;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.pengaduan-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-left: 4px solid #009639;
    transition: all 0.3s ease;
}
.pengaduan-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.pengaduan-card.pending { border-left-color: #ffc107; }
.pengaduan-card.proses { border-left-color: #17a2b8; }
.pengaduan-card.selesai { border-left-color: #28a745; }
.pengaduan-card.ditolak { border-left-color: #dc3545; }
.pengaduan-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.pengaduan-header h5 {
    font-weight: 700;
    color: #212121;
    margin: 0;
    flex: 1;
    margin-right: 15px;
}
.pengaduan-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    flex-wrap: wrap;
}
.pengaduan-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #666;
}
.pengaduan-meta-item i {
    color: #009639;
}
.pengaduan-deskripsi {
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}
.pengaduan-lokasi {
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    color: #555;
    margin-bottom: 15px;
}
.pengaduan-lokasi i {
    color: #009639;
    margin-right: 8px;
}
.pengaduan-foto img {
    max-width: 100%;
    max-height: 250px;
    border-radius: 8px;
    margin-bottom: 15px;
}
.pengaduan-action {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}
.btn-detail {
    background-color: #009639;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}
.btn-detail:hover {
    background-color: #007a2f;
    color: white;
}
.tanggapan-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    display: none;
}
.tanggapan-section.show {
    display: block;
}
.tanggapan-item {
    background: white;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 10px;
    border-left: 3px solid #00308F;
}
.tanggapan-item:last-child {
    margin-bottom: 0;
}
.tanggapan-header {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}
.tanggapan-content {
    color: #333;
    line-height: 1.5;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
}
</style>

<div class="page-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-comments me-2"></i> Riwayat Pengaduan</h2>
            <p>Lihat status dan tanggapan dari pengaduan yang telah Anda ajukan</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="pengaduan_form.php" class="btn-new-pengaduan">
                <i class="fas fa-plus-circle"></i> Ajukan Pengaduan Baru
            </a>
        </div>
    </div>
</div>

<div class="card card-dashboard border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Cari Pengaduan</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Cari judul, deskripsi, atau lokasi..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Filter Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                    <option value="proses" <?php echo $filter_status === 'proses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                    <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="pengaduan_riwayat.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<?php if (count($pengaduan_list) > 0): ?>
    <div class="pengaduan-list">
        <?php foreach ($pengaduan_list as $pengaduan): ?>
            <div class="pengaduan-card <?php echo $pengaduan['status']; ?>">
                <div class="pengaduan-header">
                    <h5><?php echo htmlspecialchars($pengaduan['judul']); ?></h5>
                    <?php echo get_status_badge($pengaduan['status']); ?>
                </div>
                <div class="pengaduan-meta">
                    <div class="pengaduan-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo format_tanggal($pengaduan['created_at']); ?></span>
                    </div>
                    <div class="pengaduan-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo time_ago($pengaduan['created_at']); ?></span>
                    </div>
                    <div class="pengaduan-meta-item">
                        <i class="fas fa-comments"></i>
                        <span><?php echo $pengaduan['jumlah_tanggapan']; ?> Tanggapan</span>
                    </div>
                </div>
                <div class="pengaduan-deskripsi">
                    <?php echo htmlspecialchars(substr($pengaduan['deskripsi'], 0, 200)); ?>
                    <?php if (strlen($pengaduan['deskripsi']) > 200): ?>...<?php endif; ?>
                </div>
                <div class="pengaduan-lokasi">
                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($pengaduan['lokasi']); ?>
                </div>
                <?php if (!empty($pengaduan['foto'])): ?>
                    <div class="pengaduan-foto">
                        <img src="../assets/uploads/pengaduan/<?php echo htmlspecialchars($pengaduan['foto']); ?>" alt="Foto Pengaduan">
                    </div>
                <?php endif; ?>
                <div class="pengaduan-action">
                    <a href="#" class="btn-detail" onclick="showDetail(<?php echo $pengaduan['id']; ?>); return false;">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <?php if ($pengaduan['jumlah_tanggapan'] > 0): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleTanggapan(this)">
                            <i class="fas fa-comment-dots"></i> Lihat Tanggapan (<?php echo $pengaduan['jumlah_tanggapan']; ?>)
                        </button>
                    <?php else: ?>
                        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Belum ada tanggapan</span>
                    <?php endif; ?>
                </div>
                <?php if ($pengaduan['jumlah_tanggapan'] > 0): ?>
                    <div class="tanggapan-section" data-pengaduan-id="<?php echo $pengaduan['id']; ?>">
                        <?php 
                        $stmt_tanggapan = $conn->prepare(
                            "SELECT t.*, u.nama 
                             FROM tanggapan t 
                             JOIN users u ON t.admin_id = u.id 
                             WHERE t.pengaduan_id = ? 
                             ORDER BY t.created_at DESC"
                        );
                        $stmt_tanggapan->bind_param('i', $pengaduan['id']);
                        $stmt_tanggapan->execute();
                        $tanggapan_result = $stmt_tanggapan->get_result();
                        
                        while ($tanggapan = $tanggapan_result->fetch_assoc()):
                        ?>
                            <div class="tanggapan-item">
                                <div class="tanggapan-header">
                                    <i class="fas fa-user-shield me-1"></i> Admin: <?php echo htmlspecialchars($tanggapan['nama']); ?> - <?php echo format_tanggal($tanggapan['created_at']); ?>
                                </div>
                                <div class="tanggapan-content">
                                    <?php echo nl2br(htmlspecialchars($tanggapan['isi_tanggapan'])); ?>
                                </div>
                            </div>
                        <?php 
                        endwhile;
                        $stmt_tanggapan->close();
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h4 class="text-muted">Belum Ada Pengaduan</h4>
        <p class="text-muted">Anda belum pernah mengajukan pengaduan. Mulai dengan mengajukan pengaduan baru.</p>
        <a href="pengaduan_form.php" class="btn btn-primary mt-3">
            <i class="fas fa-plus-circle me-2"></i> Ajukan Pengaduan Pertama
        </a>
    </div>
<?php endif; ?>

<script>
function toggleTanggapan(button) {
    const card = button.closest('.pengaduan-card');
    const tanggapanSection = card.querySelector('.tanggapan-section');
    if (tanggapanSection) {
        tanggapanSection.classList.toggle('show');
    }
}

function showDetail(id) {
    alert('Fitur detail akan segera hadir!');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

