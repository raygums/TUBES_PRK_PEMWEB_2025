<?php
// session_start();
require '../config/config.php';

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../auth/login.php");
//     exit;
// }

// if ($_SESSION['role'] !== 'admin') {
//     header("Location: ../dashboard/dashboard_warga.php");
//     exit;
// }

// Filter
$filter = $_GET['filter'] ?? 'all';

$search = $_GET['search'] ?? '';

$query = "SELECT * FROM umkm WHERE 1=1";

// Filter status
if ($filter !== "all") {
    $query .= " AND status = '$filter'";
}

// Filter pencarian
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (
        nama_usaha LIKE '%$search%' OR
        bidang_usaha LIKE '%$search%' OR
        alamat_usaha LIKE '%$search%' OR
        nama_pemilik LIKE '%$search%'
    )";
}

$query .= " ORDER BY created_at DESC";


$result = mysqli_query($conn, $query);

// Badge fungsi
function badgeStatus($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'approved':
            return '<span class="badge bg-success">Approved</span>';
        case 'rejected':
            return '<span class="badge bg-danger">Rejected</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

include '../admin/layouts/admin_header.php';
?>

<!-- WRAPPER UTAMA ADMIN -->
<div class="admin-container">

    <!-- SIDEBAR -->
    <?php include '../admin/layouts/admin_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="page-header">
            <h1><i class="bi bi-shop"></i> Daftar UMKM</h1>
            <p>Kelola data UMKM yang terdaftar di LampungSmart.</p>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <form method="GET" class="d-flex" style="max-width: 300px;">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="text" name="search" class="form-control" 
                    placeholder="Cari UMKM..." 
                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button class="btn btn-primary ms-2" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>


        <!-- Filter -->
        <div class="mb-3">
            <a href="admin_umkm.php?filter=all" class="btn btn-secondary btn-sm <?= $filter=='all'?'active':'' ?>">Semua</a>
            <a href="admin_umkm.php?filter=pending" class="btn btn-warning btn-sm <?= $filter=='pending'?'active':'' ?>">Pending</a>
            <a href="admin_umkm.php?filter=approved" class="btn btn-success btn-sm <?= $filter=='approved'?'active':'' ?>">Approved</a>
            <a href="admin_umkm.php?filter=rejected" class="btn btn-danger btn-sm <?= $filter=='rejected'?'active':'' ?>">Rejected</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body table-responsive">

                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Usaha</th>
                            <th>Bidang Usaha</th>
                            <th>Alamat Usaha</th>
                            <th>Pemilik</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_usaha']); ?></td>
                                <td><?= htmlspecialchars($row['bidang_usaha']); ?></td>
                                <td><?= htmlspecialchars($row['alamat_usaha']); ?></td>
                                <td><?= htmlspecialchars($row['nama_pemilik']); ?></td>
                                <td><?= badgeStatus($row['status']); ?></td>
                                <td><?= date('d M Y H:i', strtotime($row['created_at'])); ?></td>

                                <td>
                                    <div class="btn-group">

                                        <!-- View detail -->
                                        <a href="admin_detail_umkm.php?id=<?= $row['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <?php if ($row['status'] === 'pending'): ?>

                                        <!-- Approve -->
                                        <a href="admin_status_umkm.php?action=approve&id=<?= $row['id']; ?>" 
                                           onclick="return confirm('Setujui UMKM ini?')"
                                           class="btn btn-success btn-sm">
                                            <i class="bi bi-check-circle"></i>
                                        </a>

                                        <!-- Reject -->
                                        <a href="admin_status_umkm.php?action=reject&id=<?= $row['id']; ?>" 
                                           onclick="return confirm('Tolak UMKM ini?')"
                                           class="btn btn-danger btn-sm">
                                            <i class="bi bi-x-circle"></i>
                                        </a>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <div>Belum ada data UMKM.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>

            </div>
        </div>

    </div> <!-- end main-content -->

</div> <!-- end admin-container -->

<?php include '../admin/layouts/admin_footer.php'; ?>
