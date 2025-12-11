<?php
session_start();
require '../config/config.php';

// OPTIONAL: aktifkan kembali jika sudah login normal
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID UMKM tidak ditemukan.");
}

$id = $_GET['id'];

$query = "SELECT * FROM umkm WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data UMKM tidak ditemukan!");
}

include '../admin/layouts/admin_header.php';
?>

<div class="admin-container">

    <?php include '../admin/layouts/admin_sidebar.php'; ?>

    <div class="main-content">

        <div class="page-header">
            <h1><i class="bi bi-shop"></i> Detail UMKM</h1>
            <p>Informasi lengkap tentang UMKM terdaftar.</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <h3><?= htmlspecialchars($data['nama_usaha']); ?></h3>
                <p class="text-muted mb-3">Pemilik: <?= htmlspecialchars($data['nama_pemilik']); ?></p>

                <table class="table table-bordered">
                    <tr>
                        <th width="200">Nama Usaha</th>
                        <td><?= htmlspecialchars($data['nama_usaha']); ?></td>
                    </tr>
                    <tr>
                        <th>Bidang Usaha</th>
                        <td><?= htmlspecialchars($data['bidang_usaha']); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat Usaha</th>
                        <td><?= htmlspecialchars($data['alamat_usaha']); ?></td>
                    </tr>
                    <tr>
                        <th>Nomor Telepon</th>
                        <td><?= htmlspecialchars($data['no_telepon']); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php
                                $s = $data['status'];
                                if ($s == 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                else if ($s == 'approved') echo '<span class="badge bg-success">Approved</span>';
                                else echo '<span class="badge bg-danger">Rejected</span>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Tanggal Dibuat</th>
                        <td><?= date("d M Y H:i", strtotime($data['created_at'])); ?></td>
                    </tr>
                </table>

                <div class="mt-4 d-flex gap-2">

                    <a href="admin_umkm.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                    <?php if ($data['status'] === 'pending'): ?>
                        <a href="umkm_status.php?action=approve&id=<?= $data['id']; ?>" 
                            class="btn btn-success"
                            onclick="return confirm('Setujui UMKM ini?');">
                            <i class="bi bi-check-circle"></i> Setujui
                        </a>

                        <a href="umkm_status.php?action=reject&id=<?= $data['id']; ?>" 
                            class="btn btn-danger"
                            onclick="return confirm('Tolak UMKM ini?');">
                            <i class="bi bi-x-circle"></i> Tolak
                        </a>
                    <?php endif; ?>

                </div>

            </div>
        </div>

    </div>

</div>

<?php include '../admin/layouts/admin_footer.php'; ?>
