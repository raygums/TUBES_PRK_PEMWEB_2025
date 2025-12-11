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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'terbaru';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_status') {
        $pengaduan_id = intval($_POST['pengaduan_id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');
        
        if ($pengaduan_id > 0 && in_array($new_status, ['pending', 'proses', 'selesai', 'ditolak'])) {
            $query = "UPDATE pengaduan SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $new_status, $pengaduan_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal update status']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'add_response') {
        $pengaduan_id = intval($_POST['pengaduan_id'] ?? 0);
        $tanggapan = trim($_POST['tanggapan'] ?? '');
        
        if ($pengaduan_id > 0 && !empty($tanggapan) && strlen($tanggapan) >= 10) {
            $query = "INSERT INTO tanggapan (pengaduan_id, admin_id, isi_tanggapan) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param('iis', $pengaduan_id, $admin_id, $tanggapan);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tanggapan berhasil ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan tanggapan']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Tanggapan minimal 10 karakter']);
        }
        exit;
    }
}

$query = "SELECT p.*, u.nama as nama_warga, u.email, COUNT(t.id) as jumlah_tanggapan 
          FROM pengaduan p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN tanggapan t ON p.id = t.pengaduan_id 
          WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (p.judul LIKE ? OR p.deskripsi LIKE ? OR u.nama LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

if (!empty($filter_status) && in_array($filter_status, ['pending', 'proses', 'selesai', 'ditolak'])) {
    $query .= " AND p.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$query .= " GROUP BY p.id ORDER BY ";

if ($sort_by === 'oldest') {
    $query .= "p.created_at ASC";
} elseif ($sort_by === 'paling-dikomentar') {
    $query .= "jumlah_tanggapan DESC, p.created_at DESC";
} else {
    $query .= "p.created_at DESC";
}

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

$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
               FROM pengaduan";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

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
            return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Menunggu</span>';
        case 'proses':
            return '<span class="badge bg-info text-white"><i class="fas fa-sync-alt"></i> Proses</span>';
        case 'selesai':
            return '<span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Selesai</span>';
        case 'ditolak':
            return '<span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Ditolak</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

require '../layouts/header.php';
require '../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold text-brand-primary mb-2">Validasi Pengaduan</h2>
        <p class="text-muted mb-0">Kelola dan validasi semua pengaduan dari warga</p>
    </div>
    <div class="d-none d-md-block">
        <button class="btn btn-white border shadow-sm px-3 py-2 rounded-3 text-muted">
            <i class="far fa-calendar-alt me-2"></i> <?php echo date('d F Y'); ?>
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-6 col-md">
        <div class="card card-dashboard border-0 h-100">
            <div class="card-body text-center p-3">
                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-clipboard-list text-primary"></i>
                </div>
                <h3 class="fw-bold mb-0 text-brand-primary"><?php echo $stats['total'] ?? 0; ?></h3>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #ffc107 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-hourglass-half text-warning"></i>
                </div>
                <h3 class="fw-bold mb-0 text-warning"><?php echo $stats['pending'] ?? 0; ?></h3>
                <small class="text-muted">Pending</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #17a2b8 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-info bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-sync-alt text-info"></i>
                </div>
                <h3 class="fw-bold mb-0 text-info"><?php echo $stats['proses'] ?? 0; ?></h3>
                <small class="text-muted">Diproses</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #28a745 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success"><?php echo $stats['selesai'] ?? 0; ?></h3>
                <small class="text-muted">Selesai</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #dc3545 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-times-circle text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0 text-danger"><?php echo $stats['ditolak'] ?? 0; ?></h3>
                <small class="text-muted">Ditolak</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-dashboard border-0 mb-4" style="border-radius: 15px;">
    <div class="card-body p-4">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-brand-primary">Cari Pengaduan</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, warga, atau lokasi..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-brand-primary">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="proses" <?php echo $filter_status === 'proses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-brand-primary">Urutkan</label>
                    <select name="sort" class="form-select">
                        <option value="terbaru" <?php echo $sort_by === 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">Total: <?php echo count($pengaduan_list); ?> pengaduan</span>
    <a href="admin_pengaduan.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-sync-alt me-1"></i> Reset
    </a>
</div>

<?php if (empty($pengaduan_list)): ?>
    <div class="card card-dashboard border-0 text-center py-5" style="border-radius: 15px;">
        <div class="card-body">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak Ada Pengaduan</h5>
            <p class="text-muted">Belum ada pengaduan yang sesuai dengan filter Anda.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($pengaduan_list as $pengaduan): ?>
        <div class="card card-dashboard border-0 mb-3" style="border-radius: 15px; border-left: 4px solid <?php 
            echo $pengaduan['status'] === 'pending' ? '#ffc107' : 
                ($pengaduan['status'] === 'proses' ? '#17a2b8' : 
                ($pengaduan['status'] === 'selesai' ? '#28a745' : '#dc3545')); 
        ?> !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-brand-primary mb-1"><?php echo htmlspecialchars($pengaduan['judul']); ?></h5>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($pengaduan['nama_warga']); ?> 
                            (<?php echo htmlspecialchars($pengaduan['email']); ?>)
                        </p>
                    </div>
                    <?php echo get_status_badge($pengaduan['status']); ?>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo htmlspecialchars($pengaduan['lokasi']); ?></p>
                        <p class="text-muted mb-0" style="line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars(substr($pengaduan['deskripsi'], 0, 200))); ?>
                            <?php if (strlen($pengaduan['deskripsi']) > 200): ?>...<?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-muted d-block mb-1">
                            <i class="fas fa-calendar me-1"></i> <?php echo format_tanggal($pengaduan['created_at']); ?>
                        </small>
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-clock me-1"></i> <?php echo time_ago($pengaduan['created_at']); ?>
                        </small>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-comments me-1"></i> <?php echo $pengaduan['jumlah_tanggapan']; ?> Tanggapan
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($pengaduan['foto'])): ?>
                    <div class="mb-3">
                        <img src="../assets/uploads/pengaduan/<?php echo htmlspecialchars($pengaduan['foto']); ?>" 
                             class="img-thumbnail" style="max-height: 150px; cursor: pointer;"
                             onclick="window.open(this.src, '_blank')">
                    </div>
                <?php endif; ?>
                
                <div class="border-top pt-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Ubah Status:</label>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm <?php echo $pengaduan['status'] === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>" 
                                        onclick="updateStatus(<?php echo $pengaduan['id']; ?>, 'pending')">Pending</button>
                                <button type="button" class="btn btn-sm <?php echo $pengaduan['status'] === 'proses' ? 'btn-info' : 'btn-outline-info'; ?>" 
                                        onclick="updateStatus(<?php echo $pengaduan['id']; ?>, 'proses')">Proses</button>
                                <button type="button" class="btn btn-sm <?php echo $pengaduan['status'] === 'selesai' ? 'btn-success' : 'btn-outline-success'; ?>" 
                                        onclick="updateStatus(<?php echo $pengaduan['id']; ?>, 'selesai')">Selesai</button>
                                <button type="button" class="btn btn-sm <?php echo $pengaduan['status'] === 'ditolak' ? 'btn-danger' : 'btn-outline-danger'; ?>" 
                                        onclick="updateStatus(<?php echo $pengaduan['id']; ?>, 'ditolak')">Tolak</button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <button class="btn btn-sm btn-primary" onclick="toggleResponse(<?php echo $pengaduan['id']; ?>)">
                                <i class="fas fa-reply me-1"></i> Beri Tanggapan
                            </button>
                        </div>
                    </div>
                    
                    <div class="response-form mt-3" id="response-form-<?php echo $pengaduan['id']; ?>" style="display: none;">
                        <textarea class="form-control mb-2" id="tanggapan-<?php echo $pengaduan['id']; ?>" 
                                  rows="3" placeholder="Tulis tanggapan untuk pengaduan ini... (min. 10 karakter)"></textarea>
                        <button class="btn btn-success btn-sm" onclick="submitResponse(<?php echo $pengaduan['id']; ?>)">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Tanggapan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="text-center text-muted mt-5 mb-3">
    <small>&copy; 2025 LampungSmart - Pemerintah Provinsi Lampung</small>
</div>

<script>
function updateStatus(pengaduanId, status) {
    if (!confirm('Yakin ingin mengubah status pengaduan ini?')) return;
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('pengaduan_id', pengaduanId);
    formData.append('status', status);
    
    fetch('admin_pengaduan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function toggleResponse(pengaduanId) {
    const form = document.getElementById('response-form-' + pengaduanId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function submitResponse(pengaduanId) {
    const tanggapan = document.getElementById('tanggapan-' + pengaduanId).value.trim();
    
    if (tanggapan.length < 10) {
        alert('Tanggapan minimal 10 karakter');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add_response');
    formData.append('pengaduan_id', pengaduanId);
    formData.append('tanggapan', tanggapan);
    
    fetch('admin_pengaduan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>

<?php require '../layouts/footer.php'; ?>
