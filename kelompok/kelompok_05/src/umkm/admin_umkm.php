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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_status') {
        $umkm_id = intval($_POST['umkm_id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');
        
        if ($umkm_id > 0 && in_array($new_status, ['pending', 'approved', 'rejected'])) {
            $query = "UPDATE umkm SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $new_status, $umkm_id);
            
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
}

$query = "SELECT u.*, us.nama as nama_user, us.email 
          FROM umkm u 
          JOIN users us ON u.user_id = us.id 
          WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (u.nama_usaha LIKE ? OR u.nama_pemilik LIKE ? OR us.nama LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

if (!empty($filter_status) && in_array($filter_status, ['pending', 'approved', 'rejected'])) {
    $query .= " AND u.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$umkm_list = [];

while ($row = $result->fetch_assoc()) {
    $umkm_list[] = $row;
}
$stmt->close();

$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
               FROM umkm";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

function format_tanggal($date) {
    $timestamp = strtotime($date);
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_index = date('n', $timestamp) - 1;
    return date('d', $timestamp) . ' ' . $months[$month_index] . ' ' . date('Y H:i', $timestamp);
}

function get_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>';
        case 'approved':
            return '<span class="badge bg-success text-white"><i class="fas fa-check-circle"></i> Approved</span>';
        case 'rejected':
            return '<span class="badge bg-danger text-white"><i class="fas fa-times-circle"></i> Rejected</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

require '../layouts/header.php';
require '../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold text-brand-primary mb-2">Validasi UMKM</h2>
        <p class="text-muted mb-0">Kelola dan validasi pengajuan izin UMKM dari warga</p>
    </div>
    <div class="d-none d-md-block">
        <button class="btn btn-white border shadow-sm px-3 py-2 rounded-3 text-muted">
            <i class="far fa-calendar-alt me-2"></i> <?php echo date('d F Y'); ?>
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-dashboard border-0 h-100">
            <div class="card-body text-center p-3">
                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-store text-primary"></i>
                </div>
                <h3 class="fw-bold mb-0 text-brand-primary"><?php echo $stats['total'] ?? 0; ?></h3>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
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
    <div class="col-6 col-md-3">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #28a745 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success"><?php echo $stats['approved'] ?? 0; ?></h3>
                <small class="text-muted">Approved</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-dashboard border-0 h-100" style="border-left: 3px solid #dc3545 !important;">
            <div class="card-body text-center p-3">
                <div class="bg-danger bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-times-circle text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0 text-danger"><?php echo $stats['rejected'] ?? 0; ?></h3>
                <small class="text-muted">Rejected</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-dashboard border-0 mb-4" style="border-radius: 15px;">
    <div class="card-body p-4">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-brand-primary">Cari UMKM</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama usaha, pemilik..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-brand-primary">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted">Total: <?php echo count($umkm_list); ?> UMKM</span>
    <a href="admin_umkm.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-sync-alt me-1"></i> Reset
    </a>
</div>

<?php if (empty($umkm_list)): ?>
    <div class="card card-dashboard border-0 text-center py-5" style="border-radius: 15px;">
        <div class="card-body">
            <i class="fas fa-store-slash fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Tidak Ada Data UMKM</h5>
            <p class="text-muted">Belum ada pengajuan UMKM yang sesuai dengan filter Anda.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($umkm_list as $umkm): ?>
        <div class="card card-dashboard border-0 mb-3" style="border-radius: 15px; border-left: 4px solid <?php 
            echo $umkm['status'] === 'pending' ? '#ffc107' : 
                ($umkm['status'] === 'approved' ? '#28a745' : '#dc3545'); 
        ?> !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold text-brand-primary mb-1">
                            <i class="fas fa-store me-2"></i><?php echo htmlspecialchars($umkm['nama_usaha']); ?>
                        </h5>
                        <p class="text-muted small mb-0">
                            Didaftarkan oleh: <?php echo htmlspecialchars($umkm['nama_user']); ?> (<?php echo htmlspecialchars($umkm['email']); ?>)
                        </p>
                    </div>
                    <?php echo get_status_badge($umkm['status']); ?>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Pemilik</small>
                                    <span class="fw-bold"><i class="fas fa-user me-1 text-primary"></i> <?php echo htmlspecialchars($umkm['nama_pemilik']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Bidang Usaha</small>
                                    <span class="fw-bold"><i class="fas fa-briefcase me-1 text-success"></i> <?php echo htmlspecialchars($umkm['bidang_usaha']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">No. Telepon</small>
                                    <span class="fw-bold"><i class="fas fa-phone me-1 text-info"></i> <?php echo htmlspecialchars($umkm['no_telepon']); ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Tanggal Daftar</small>
                                    <span class="fw-bold"><i class="fas fa-calendar me-1 text-warning"></i> <?php echo format_tanggal($umkm['created_at']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3 h-100">
                            <small class="text-muted d-block mb-1">Alamat Usaha</small>
                            <p class="mb-0"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($umkm['alamat_usaha']); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($umkm['status'] === 'pending'): ?>
                    <div class="border-top pt-3">
                        <label class="form-label fw-bold small">Aksi:</label>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?php echo $umkm['id']; ?>, 'approved')">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?php echo $umkm['id']; ?>, 'rejected')">
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="text-center text-muted mt-5 mb-3">
    <small>&copy; 2025 LampungSmart - Pemerintah Provinsi Lampung</small>
</div>

<script>
function updateStatus(umkmId, status) {
    const confirmMsg = status === 'approved' 
        ? 'Yakin ingin menyetujui UMKM ini?' 
        : 'Yakin ingin menolak UMKM ini?';
    
    if (!confirm(confirmMsg)) return;
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('umkm_id', umkmId);
    formData.append('status', status);
    
    fetch('admin_umkm.php', {
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
