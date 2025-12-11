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
require_once __DIR__ . '/../config/config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? trim($_GET['role']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_role') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_role = trim($_POST['role'] ?? '');
        
        if ($user_id > 0 && in_array($new_role, ['warga', 'admin'])) {
            if ($user_id == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa mengubah role diri sendiri']);
                exit;
            }
            
            $query = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $new_role, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Role berhasil diperbarui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal update role']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'delete_user') {
        $user_id = intval($_POST['user_id'] ?? 0);
        
        if ($user_id > 0) {
            if ($user_id == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri']);
                exit;
            }
            
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus user']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        }
        exit;
    }
}

$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (nama LIKE ? OR email LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm];
    $types = 'ss';
}

if (!empty($filter_role) && in_array($filter_role, ['warga', 'admin'])) {
    $query .= " AND role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$user_list = [];

while ($row = $result->fetch_assoc()) {
    $user_list[] = $row;
}
$stmt->close();

$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN role = 'warga' THEN 1 ELSE 0 END) as warga,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin
               FROM users";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

function format_tanggal($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $month_index = date('n', $timestamp) - 1;
    return date('d', $timestamp) . ' ' . $months[$month_index] . ' ' . date('Y H:i', $timestamp);
}

$page_title = "Kelola User";
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="container-fluid">
            <div class="mb-4">
                <h2 class="fw-bold text-brand-primary mb-1">
                    <i class="fas fa-users me-2"></i>Kelola User
                </h2>
                <p class="text-muted mb-0">Kelola semua user yang terdaftar di sistem LampungSmart</p>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-users text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 small">Total User</p>
                                <h3 class="fw-bold mb-0 text-brand-primary"><?php echo $stats['total'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                <i class="fas fa-user-check text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 small">Warga</p>
                                <h3 class="fw-bold mb-0 text-info"><?php echo $stats['warga'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashboard border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="fas fa-shield-alt text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-0 small">Admin</p>
                                <h3 class="fw-bold mb-0 text-warning"><?php echo $stats['admin'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-dashboard border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Cari User</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Nama atau email..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">Semua Role</option>
                                    <option value="warga" <?php echo $filter_role === 'warga' ? 'selected' : ''; ?>>Warga</option>
                                    <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="kelola_user.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (empty($user_list)): ?>
                <div class="card card-dashboard border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox text-muted mb-3" style="font-size: 4rem;"></i>
                        <h5 class="text-muted">Tidak Ada User</h5>
                        <p class="text-muted mb-0">Tidak ada user yang sesuai dengan filter Anda.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card card-dashboard border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-brand-primary">
                            <i class="fas fa-list me-2"></i>Daftar User (<?php echo count($user_list); ?>)
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4" style="width: 60px;">No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Terdaftar</th>
                                        <th class="text-center" style="width: 200px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($user_list as $user): ?>
                                        <tr class="<?php echo $user['id'] == $_SESSION['user_id'] ? 'table-success' : ''; ?>">
                                            <td class="ps-4"><?php echo $no++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-success ms-1">Anda</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-shield-alt me-1"></i>Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info"><i class="fas fa-user me-1"></i>Warga</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small class="text-muted"><?php echo format_tanggal($user['created_at']); ?></small></td>
                                            <td class="text-center">
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <?php if ($user['role'] === 'warga'): ?>
                                                        <button class="btn btn-sm btn-warning" onclick="updateRole(<?php echo $user['id']; ?>, 'admin')" title="Jadikan Admin">
                                                            <i class="fas fa-crown"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-info" onclick="updateRole(<?php echo $user['id']; ?>, 'warga')" title="Jadikan Warga">
                                                            <i class="fas fa-user"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['nama'])); ?>')" title="Hapus User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateRole(userId, newRole) {
    const confirmMsg = newRole === 'admin' 
        ? 'Yakin ingin menjadikan user ini sebagai Admin?' 
        : 'Yakin ingin menjadikan user ini sebagai Warga?';
    
    if (!confirm(confirmMsg)) return;
    
    const formData = new FormData();
    formData.append('action', 'update_role');
    formData.append('user_id', userId);
    formData.append('role', newRole);
    
    fetch('kelola_user.php', {
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

function deleteUser(userId, userName) {
    if (!confirm('Yakin ingin menghapus user "' + userName + '"? Semua data terkait akan ikut terhapus.')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('user_id', userId);
    
    fetch('kelola_user.php', {
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
