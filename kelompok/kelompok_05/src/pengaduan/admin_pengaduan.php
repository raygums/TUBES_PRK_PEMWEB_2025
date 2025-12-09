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
    if ($_POST['action'] === 'export_csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="pengaduan_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Judul', 'Warga', 'Email', 'Lokasi', 'Status', 'Tanggal'], ';');
        
        foreach ($pengaduan_list as $row) {
            fputcsv($output, [
                $row['id'],
                $row['judul'],
                $row['nama_warga'],
                $row['email'],
                $row['lokasi'],
                $row['status'],
                $row['created_at']
            ], ';');
        }
        fclose($output);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../assets/css/lampung-theme.css" rel="stylesheet">
    
    <style>
        :root {
            --lampung-green: #009639;
            --lampung-red: #D60000;
            --lampung-blue: #00308F;
            --lampung-gold: #FFD700;
            --lampung-charcoal: #212121;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--lampung-blue) 0%, var(--lampung-green) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-weight: 700;
            margin: 0;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            border-top: 4px solid var(--lampung-green);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.pending {
            border-top-color: #ffc107;
        }
        
        .stat-card.proses {
            border-top-color: #17a2b8;
        }
        
        .stat-card.selesai {
            border-top-color: #28a745;
        }
        
        .stat-card.ditolak {
            border-top-color: var(--lampung-red);
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: var(--lampung-charcoal);
            margin: 10px 0;
        }
        
        .stat-card .label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            font-weight: 500;
        }
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-section label {
            color: var(--lampung-charcoal);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control,
        .form-select {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--lampung-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 150, 57, 0.25);
        }
        
        .btn-filter {
            background-color: var(--lampung-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-filter:hover {
            background-color: #007a2f;
            transform: translateY(-2px);
        }
        
        .btn-reset-filter {
            background-color: #999;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-reset-filter:hover {
            background-color: #777;
        }
        
        .pengaduan-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--lampung-green);
        }
        
        .pengaduan-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .pengaduan-card.pending {
            border-left-color: #ffc107;
        }
        
        .pengaduan-card.proses {
            border-left-color: #17a2b8;
        }
        
        .pengaduan-card.selesai {
            border-left-color: #28a745;
        }
        
        .pengaduan-card.ditolak {
            border-left-color: var(--lampung-red);
        }
        
        .pengaduan-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .pengaduan-header h5 {
            color: var(--lampung-charcoal);
            font-weight: 700;
            margin: 0;
            flex: 1;
        }
        
        .pengaduan-warga {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 10px;
            color: #555;
        }
        
        .pengaduan-warga i {
            color: var(--lampung-blue);
            margin-right: 5px;
        }
        
        .pengaduan-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
            font-size: 13px;
            color: #666;
        }
        
        .pengaduan-meta i {
            color: var(--lampung-green);
        }
        
        .pengaduan-lokasi {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 13px;
            color: #555;
        }
        
        .pengaduan-lokasi i {
            color: var(--lampung-green);
            margin-right: 5px;
        }
        
        .pengaduan-foto {
            margin: 10px 0;
        }
        
        .pengaduan-foto img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid #ddd;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .pengaduan-foto img:hover {
            transform: scale(1.05);
        }
        
        .status-selector {
            margin-bottom: 15px;
        }
        
        .toolbar-section {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .btn-export {
            background-color: var(--lampung-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-export:hover {
            background-color: #007a2f;
            transform: translateY(-2px);
        }
        
        .sort-selector {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            background-color: white;
        }
        
        .stats-trend {
            display: flex;
            gap: 5px;
            margin-top: 5px;
            font-size: 11px;
        }
        
        .stats-trend .trend-up {
            color: #28a745;
        }
        
        .stats-trend .trend-down {
            color: var(--lampung-red);
        }
        
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            border-left: 4px solid var(--lampung-green);
        }
        
        .toast-notification.success {
            border-left-color: #28a745;
        }
        
        .toast-notification.error {
            border-left-color: var(--lampung-red);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .pagination-section .info {
            color: #666;
            font-size: 14px;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        
        .card-actions button {
            padding: 8px 16px;
            font-size: 13px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .card-actions .btn-primary {
            background-color: var(--lampung-blue);
            color: white;
        }
        
        .card-actions .btn-primary:hover {
            background-color: #002060;
            transform: translateY(-2px);
        }
        
        .card-actions .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .card-actions .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }.status-selector label {
            font-size: 13px;
            font-weight: 600;
            color: var(--lampung-charcoal);
            margin-bottom: 5px;
        }
        
        .status-selector select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .response-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .response-section label {
            font-size: 13px;
            font-weight: 600;
            color: var(--lampung-charcoal);
            margin-bottom: 8px;
        }
        
        .response-section textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            font-family: 'Segoe UI', sans-serif;
            min-height: 80px;
            resize: vertical;
        }
        
        .response-section textarea:focus {
            border-color: var(--lampung-green);
            box-shadow: 0 0 0 0.2rem rgba(0, 150, 57, 0.25);
            outline: none;
        }
        
        .response-section .char-count {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn-update-status {
            background-color: var(--lampung-green);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-update-status:hover {
            background-color: #007a2f;
            transform: translateY(-2px);
        }
        
        .btn-add-response {
            background-color: var(--lampung-blue);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-add-response:hover {
            background-color: #002060;
            transform: translateY(-2px);
        }
        
        .response-list {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .response-item {
            background-color: white;
            padding: 12px;
            border-left: 3px solid var(--lampung-blue);
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .response-item .admin-name {
            color: var(--lampung-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .response-item .admin-time {
            color: #999;
            font-size: 11px;
            margin-bottom: 8px;
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
        }
        
        .empty-state h4 {
            color: var(--lampung-charcoal);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include '../layouts/header.php'; ?>
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
        
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Cari Pengaduan</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        placeholder="Cari judul, warga, atau lokasi..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="proses" <?php echo $filter_status === 'proses' ? 'selected' : ''; ?>>Sedang Diproses</option>
                        <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditolak" <?php echo $filter_status === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="sort" class="form-label">Urutkan</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="terbaru" <?php echo $sort_by === 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="paling-dikomentar" <?php echo $sort_by === 'paling-dikomentar' ? 'selected' : ''; ?>>Paling Dikomentari</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-filter flex-grow-1">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="admin_pengaduan.php" class="btn btn-reset-filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
            
            <div class="toolbar-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="btn-export">
                        <i class="bi bi-download"></i> Export CSV
                    </button>
                </form>
                <div style="font-size: 13px; color: #666;">
                    Total: <strong><?php echo count($pengaduan_list); ?></strong> pengaduan
                </div>
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
                        
                        <div class="pengaduan-warga">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($pengaduan['nama_warga']); ?> 
                            (<?php echo htmlspecialchars($pengaduan['email']); ?>)
                        </div>
                        
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
                        
                        <div class="pengaduan-lokasi">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($pengaduan['lokasi']); ?>
                        </div>
                        
                        <div style="color: #555; line-height: 1.6; margin-bottom: 15px; font-size: 14px;">
                            <strong>Deskripsi:</strong>
                            <br><?php echo htmlspecialchars(substr($pengaduan['deskripsi'], 0, 300)); ?>
                            <?php if (strlen($pengaduan['deskripsi']) > 300): ?>...<?php endif; ?>
                        </div>
                        
                        <?php if (!empty($pengaduan['foto'])): ?>
                            <div class="pengaduan-foto">
                                <strong style="display: block; margin-bottom: 8px; font-size: 13px;">Foto Pendukung:</strong>
                                <img src="../../../uploads/pengaduan/<?php echo htmlspecialchars($pengaduan['foto']); ?>" alt="Foto Pengaduan" onclick="showPhotoModal(this)">
                            </div>
                        <?php endif; ?>
                        
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
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Pengaduan</h4>
                <p>Saat ini tidak ada pengaduan yang masuk.</p>
            </div>
        <?php endif; ?>
        
    </div>
    
    <?php include '../layouts/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <strong>${type === 'success' ? '✓ Sukses' : '✗ Error'}</strong>
                <p style="margin: 5px 0 0 0;">${message}</p>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        document.querySelectorAll('.response-textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const count = this.value.length;
                this.parentElement.querySelector('.char-count-num').textContent = count;
            });
        });
        
        function updateStatus(pengaduanId) {
            const dropdown = document.querySelector(`.status-dropdown[data-id="${pengaduanId}"]`);
            const newStatus = dropdown.value;
            
            if (!newStatus) {
                showToast('Pilih status terlebih dahulu', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('pengaduan_id', pengaduanId);
            formData.append('status', newStatus);
            
            fetch('admin_pengaduan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Status berhasil diperbarui!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat mengubah status', 'error');
            });
        }
        
        function addResponse(pengaduanId) {
            const textarea = document.getElementById(`response-${pengaduanId}`);
            const tanggapan = textarea.value.trim();
            
            if (tanggapan.length < 10) {
                showToast('Tanggapan minimal 10 karakter', 'error');
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
                    showToast('Tanggapan berhasil ditambahkan!');
                    textarea.value = '';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menambahkan tanggapan', 'error');
            });
        }
        
        function showPhotoModal(imgElement) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            `;
            
            const imgContainer = document.createElement('div');
            imgContainer.style.cssText = `
                position: relative;
                max-width: 90%;
                max-height: 90%;
            `;
            
            const img = document.createElement('img');
            img.src = imgElement.src;
            img.style.cssText = `
                max-width: 100%;
                max-height: 100%;
                border-radius: 8px;
            `;
            
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = `
                position: absolute;
                top: -30px;
                right: 0;
                background: white;
                border: none;
                font-size: 28px;
                cursor: pointer;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            closeBtn.onclick = () => modal.remove();
            modal.onclick = (e) => {
                if (e.target === modal) modal.remove();
            };
            
            imgContainer.appendChild(img);
            imgContainer.appendChild(closeBtn);
            modal.appendChild(imgContainer);
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>