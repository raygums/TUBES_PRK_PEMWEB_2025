<?php
/**
 * Halaman Admin Cek dan Validasi Pengaduan
 * File: admin_pengaduan.php
 * Anggota: 2 - Fitur Pengaduan (Fullstack)
 * 
 * Fungsi:
 * - Menampilkan daftar semua pengaduan (untuk admin)
 * - Validasi dan verifikasi pengaduan
 * - Memberikan tanggapan pada pengaduan
 * - Update status pengaduan (pending -> proses -> selesai/ditolak)
 */

// Mulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../backend/auth/login.php');
    exit;
}

// Cek role user (hanya admin yang bisa akses)
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../frontend/index.php');
    exit;
}

// Koneksi database
require_once '../backend/config.php';

// Variabel untuk filter dan pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Proses update status pengaduan (AJAX)
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
    
    // Proses tambah tanggapan
    if ($_POST['action'] === 'add_response') {
        $pengaduan_id = intval($_POST['pengaduan_id'] ?? 0);
        $tanggapan = trim($_POST['tanggapan'] ?? '');
        
        if ($pengaduan_id > 0 && !empty($tanggapan) && strlen($tanggapan) >= 10) {
            $query = "INSERT INTO tanggapan (pengaduan_id, admin_id, isi_tanggapan) 
                      VALUES (?, ?, ?)";
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

// Query dasar untuk mendapatkan daftar pengaduan
$query = "SELECT p.*, u.nama as nama_warga, u.email, COUNT(t.id) as jumlah_tanggapan 
          FROM pengaduan p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN tanggapan t ON p.id = t.pengaduan_id 
          WHERE 1=1";
$params = [];
$types = '';

// Tambahkan filter pencarian
if (!empty($search)) {
    $query .= " AND (p.judul LIKE ? OR p.deskripsi LIKE ? OR u.nama LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

// Tambahkan filter status
if (!empty($filter_status) && in_array($filter_status, ['pending', 'proses', 'selesai', 'ditolak'])) {
    $query .= " AND p.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

// Urutkan berdasarkan status pending dulu, kemudian tanggal terbaru
$query .= " GROUP BY p.id 
           ORDER BY 
               CASE WHEN p.status = 'pending' THEN 1 
                    WHEN p.status = 'proses' THEN 2 
                    WHEN p.status = 'ditolak' THEN 3 
                    ELSE 4 
               END,
               p.created_at DESC";

// Eksekusi query
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

// Statistik pengaduan
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
               FROM pengaduan";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Fungsi untuk format tanggal
function format_tanggal($date) {
    $timestamp = strtotime($date);
    setlocale(LC_TIME, 'id_ID.UTF-8');
    return strftime('%d %B %Y %H:%M', $timestamp);
}

// Fungsi untuk menghitung waktu yang lalu
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
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu</span>';
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Validasi Pengaduan - LampungSmart</title>
    <style>

    </style>
</head>
<body>
    <!-- Page Header -->
        <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1><i class="bi bi-check-circle-fill"></i> Validasi Pengaduan</h1>
                    <p>Kelola, validasi, dan berikan tanggapan untuk semua pengaduan dari warga</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        
        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stat-card">
                <i class="bi bi-chat-dots" style="font-size: 24px; color: var(--lampung-green);"></i>
                <div class="label">Total</div>
                <div class="number"><?php echo $stats['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card pending">
                <i class="bi bi-hourglass-split" style="font-size: 24px; color: #ffc107;"></i>
                <div class="label">Pending</div>
                <div class="number"><?php echo $stats['pending'] ?? 0; ?></div>
            </div>
            <div class="stat-card proses">
                <i class="bi bi-arrow-repeat" style="font-size: 24px; color: #17a2b8;"></i>
                <div class="label">Diproses</div>
                <div class="number"><?php echo $stats['proses'] ?? 0; ?></div>
            </div>
            <div class="stat-card selesai">
                <i class="bi bi-check-circle" style="font-size: 24px; color: #28a745;"></i>
                <div class="label">Selesai</div>
                <div class="number"><?php echo $stats['selesai'] ?? 0; ?></div>
            </div>
            <div class="stat-card ditolak">
                <i class="bi bi-x-circle" style="font-size: 24px; color: var(--lampung-red);"></i>
                <div class="label">Ditolak</div>
                <div class="number"><?php echo $stats['ditolak'] ?? 0; ?></div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari Pengaduan</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        placeholder="Cari judul, warga, atau lokasi..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">Filter Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="proses" <?php echo $filter_status === 'proses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                        <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-filter flex-grow-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="admin_pengaduan.php" class="btn btn-reset-filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Daftar Pengaduan -->
        <?php if (count($pengaduan_list) > 0): ?>
            <div class="pengaduan-list">
                <?php foreach ($pengaduan_list as $pengaduan): ?>
                    <div class="pengaduan-card <?php echo $pengaduan['status']; ?>">
                        
                        <!-- Header -->
                        <div class="pengaduan-header">
                            <h5><?php echo htmlspecialchars($pengaduan['judul']); ?></h5>
                            <?php echo get_status_badge($pengaduan['status']); ?>
                        </div>
                        
                        <!-- Info Warga -->
                        <div class="pengaduan-warga">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($pengaduan['nama_warga']); ?> 
                            (<?php echo htmlspecialchars($pengaduan['email']); ?>)
                        </div>
                        
                        <!-- Meta Info -->
                        <div class="pengaduan-meta">
                            <div>
                                <i class="bi bi-calendar"></i>
                                <?php echo format_tanggal($pengaduan['created_at']); ?>
                            </div>
                            <div>
                                <i class="bi bi-clock"></i>
                                <?php echo time_ago($pengaduan['created_at']); ?>
                            </div>
                            <div>
                                <i class="bi bi-chat-dots"></i>
                                <?php echo $pengaduan['jumlah_tanggapan']; ?> Tanggapan
                            </div>
                        </div>
                        
                        <!-- Lokasi -->
                        <div class="pengaduan-lokasi">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($pengaduan['lokasi']); ?>
                        </div>
                        
                        <!-- Deskripsi -->
                        <div style="color: #555; line-height: 1.6; margin-bottom: 15px; font-size: 14px;">
                            <strong>Deskripsi:</strong>
                            <br><?php echo htmlspecialchars(substr($pengaduan['deskripsi'], 0, 300)); ?>
                            <?php if (strlen($pengaduan['deskripsi']) > 300): ?>...<?php endif; ?>
                        </div>
                        
                        <!-- Foto -->
                        <?php if (!empty($pengaduan['foto'])): ?>
                            <div class="pengaduan-foto">
                                <strong style="display: block; margin-bottom: 8px; font-size: 13px;">Foto Pendukung:</strong>
                                <img src="../../uploads/pengaduan/<?php echo htmlspecialchars($pengaduan['foto']); ?>" alt="Foto Pengaduan">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Update Status -->
                        <div class="status-selector">
                            <label>Update Status:</label>
                            <div class="input-group">
                                <select class="form-select status-dropdown" data-id="<?php echo $pengaduan['id']; ?>">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="pending" <?php echo $pengaduan['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                                    <option value="proses" <?php echo $pengaduan['status'] === 'proses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                    <option value="selesai" <?php echo $pengaduan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="ditolak" <?php echo $pengaduan['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                                <button type="button" class="btn-update-status" onclick="updateStatus(<?php echo $pengaduan['id']; ?>)">
                                    <i class="bi bi-check"></i> Update
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tanggapan Section -->
                        <div class="response-section">
                            <label for="response-<?php echo $pengaduan['id']; ?>">Berikan Tanggapan:</label>
                            <textarea 
                                id="response-<?php echo $pengaduan['id']; ?>" 
                                class="form-control response-textarea"
                                placeholder="Ketik tanggapan Anda di sini..."
                                data-id="<?php echo $pengaduan['id']; ?>"
                                minlength="10"
                                maxlength="5000"></textarea>
                            <div class="char-count">
                                <span class="char-count-num">0</span>/5000 karakter
                            </div>
                            <button type="button" class="btn-add-response mt-3" onclick="addResponse(<?php echo $pengaduan['id']; ?>)">
                                <i class="bi bi-send-fill"></i> Kirim Tanggapan
                            </button>
                            
                            <!-- Response List -->
                            <?php if ($pengaduan['jumlah_tanggapan'] > 0): ?>
                                <div class="response-list">
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
                                        <div class="response-item">
                                            <div class="admin-name">👤 <?php echo htmlspecialchars($tanggapan['nama']); ?></div>
                                            <div class="admin-time"><?php echo format_tanggal($tanggapan['created_at']); ?></div>
                                            <div><?php echo nl2br(htmlspecialchars($tanggapan['isi_tanggapan'])); ?></div>
                                        </div>
                                    <?php 
                                    endwhile;
                                    $stmt_tanggapan->close();
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Pengaduan</h4>
                <p>Saat ini tidak ada pengaduan yang masuk.</p>
            </div>
        <?php endif; ?>
        
    </div>
</body>
</html>