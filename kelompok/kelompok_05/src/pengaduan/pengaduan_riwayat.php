<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'warga') {
    header('Location: ../index.php');
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
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu Verifikasi</span>';
        case 'proses':
            return '<span class="badge bg-info text-white"><i class="bi bi-arrow-repeat"></i> Sedang Diproses</span>';
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
    <title>Riwayat Pengaduan - LampungSmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/lampung-theme.css" rel="stylesheet">
    
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
            background: linear-gradient(135deg, var(--lampung-green) 0%, var(--lampung-blue) 100%);
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
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .filter-section .row {
            align-items: flex-end;
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
            padding: 25px;
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
        }
        
        .pengaduan-header h5 {
            color: var(--lampung-charcoal);
            font-weight: 700;
            margin: 0;
            flex: 1;
        }
        
        .pengaduan-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
        }
        
        .pengaduan-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #666;
        }
        
        .pengaduan-meta-item i {
            color: var(--lampung-green);
        }
        
        .pengaduan-deskripsi {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .pengaduan-foto {
            margin: 15px 0;
        }
        
        .pengaduan-foto img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .pengaduan-lokasi {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 14px;
            color: #555;
        }
        
        .pengaduan-lokasi i {
            color: var(--lampung-green);
            margin-right: 8px;
        }
        
        .pengaduan-action {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-detail {
            background-color: var(--lampung-green);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-detail:hover {
            background-color: #007a2f;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .btn-lihat-tanggapan {
            background-color: var(--lampung-blue);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-lihat-tanggapan:hover {
            background-color: #002060;
            color: white;
            transform: translateY(-2px);
        }
        
        .tanggapan-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .tanggapan-section.show {
            max-height: 500px;
        }
        
        .tanggapan-item {
            background-color: white;
            padding: 12px;
            border-left: 3px solid var(--lampung-blue);
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .tanggapan-header {
            color: var(--lampung-blue);
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .tanggapan-content {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
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
        
        .btn-new-pengaduan {
            background-color: var(--lampung-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-new-pengaduan:hover {
            background-color: #007a2f;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 150, 57, 0.3);
        }
        
        .badge-count {
            background-color: var(--lampung-red);
            color: white;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }
    </style>
</head>
<body>
        <?php include '../layouts/header.php'; ?>
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="bi bi-chat-dots-fill"></i> Riwayat Pengaduan</h1>
                    <p>Lihat status dan tanggapan dari pengaduan yang telah Anda ajukan</p>
                </div>
                <div class="col-md-4 text-md-end" style="margin-top: 15px;">
                    <a href="pengaduan_form.php" class="btn-new-pengaduan">
                        <i class="bi bi-plus-circle"></i> Ajukan Pengaduan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="filter-section">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari Pengaduan</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        placeholder="Cari judul, deskripsi, atau lokasi..."
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
                    <a href="pengaduan_list.php" class="btn btn-reset-filter">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
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
                                <i class="bi bi-calendar"></i>
                                <span><?php echo format_tanggal($pengaduan['created_at']); ?></span>
                            </div>
                            <div class="pengaduan-meta-item">
                                <i class="bi bi-clock"></i>
                                <span><?php echo time_ago($pengaduan['created_at']); ?></span>
                            </div>
                            <div class="pengaduan-meta-item">
                                <i class="bi bi-chat-dots"></i>
                                <span><?php echo $pengaduan['jumlah_tanggapan']; ?> Tanggapan</span>
                            </div>
                        </div>
                        <div class="pengaduan-deskripsi">
                            <?php echo htmlspecialchars(substr($pengaduan['deskripsi'], 0, 200)); ?>
                            <?php if (strlen($pengaduan['deskripsi']) > 200): ?>
                                ...
                            <?php endif; ?>
                        </div>
                        <div class="pengaduan-lokasi">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($pengaduan['lokasi']); ?>
                        </div>
                        <?php if (!empty($pengaduan['foto'])): ?>
                            <div class="pengaduan-foto">
                                <img src="../assets/uploads/pengaduan/<?php echo htmlspecialchars($pengaduan['foto']); ?>" alt="Foto Pengaduan">
                            </div>
                        <?php endif; ?>

                        <div class="pengaduan-action">
                            <a href="#" class="btn-detail" onclick="showDetail(<?php echo $pengaduan['id']; ?>); return false;">
                                <i class="bi bi-eye"></i> Lihat Detail
                            </a>
                            <?php if ($pengaduan['jumlah_tanggapan'] > 0): ?>
                                <button type="button" class="btn-lihat-tanggapan" onclick="toggleTanggapan(this)">
                                    <i class="bi bi-chat"></i> Lihat Tanggapan 
                                    <span class="badge-count"><?php echo $pengaduan['jumlah_tanggapan']; ?></span>
                                </button>
                            <?php else: ?>
                                <p style="margin: 0; color: #999; font-size: 13px;">
                                    <i class="bi bi-info-circle"></i> Belum ada tanggapan
                                </p>
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
                                            Admin: <?php echo htmlspecialchars($tanggapan['nama']); ?> - <?php echo format_tanggal($tanggapan['created_at']); ?>
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
                <i class="bi bi-inbox"></i>
                <h4>Belum Ada Pengaduan</h4>
                <p>Anda belum pernah mengajukan pengaduan. Mulai dengan mengajukan pengaduan baru untuk menyampaikan keluhan atau saran Anda.</p>
                <a href="pengaduan_form.php" class="btn-new-pengaduan" style="margin-top: 20px;">
                    <i class="bi bi-plus-circle"></i> Ajukan Pengaduan Pertama Anda
                </a>
            </div>
        <?php endif; ?>
        
    </div>
    
    <?php include '../layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleTanggapan(button) {
            const card = button.closest('.pengaduan-card');
            const tanggapanSection = card.querySelector('.tanggapan-section');
            
            if (tanggapanSection) {
                tanggapanSection.classList.toggle('show');
                button.style.backgroundColor = tanggapanSection.classList.contains('show') 
                    ? '#002060' 
                    : 'var(--lampung-blue)';
            }
        }
        
        function showDetail(id) {
            console.log('Show detail pengaduan:', id);
            alert('Fitur detail akan segera hadir!');
        }
    </script>
</body>

