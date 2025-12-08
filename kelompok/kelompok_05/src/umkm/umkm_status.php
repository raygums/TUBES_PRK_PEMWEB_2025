<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require "../config/config.php";
require "../layouts/header.php";
require "../layouts/sidebar.php";

// Ambil SEMUA pengajuan UMKM user
$sql = "SELECT * FROM umkm WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="p-4">
    <h3 class="fw-bold text-brand-primary mb-4">Status Izin UMKM</h3>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class='alert alert-info'>
            Anda belum pernah mendaftar UMKM.
        </div>
        <a href='../frontend/daftar_umkm.php' class='btn btn-primary'>Daftar UMKM Sekarang</a>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Usaha</th>
                        <th>Pemilik</th>
                        <th>Bidang</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):

                        $status = $row['status'];
                        $badge = "secondary";

                        if ($status == "pending") $badge = "warning";
                        if ($status == "approved") $badge = "success";
                        if ($status == "rejected") $badge = "danger";
                    ?>
                    
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $row['nama_usaha']; ?></td>
                        <td><?= $row['nama_pemilik']; ?></td>
                        <td><?= $row['bidang_usaha']; ?></td>

                        <td>
                            <span class="badge bg-<?= $badge; ?>"><?= ucfirst($status); ?></span>
                        </td>

                        <td><?= $row['created_at']; ?></td>

                        <td>
                            <?php if ($status == "rejected"): ?>
                                <a href="../frontend/daftar_umkm.php" class="btn btn-sm btn-primary">
                                    Ajukan Ulang
                                </a>
                            <?php elseif ($status == "approved"): ?>
                                <button class="btn btn-sm btn-success" disabled>Download (Soon)</button>
                            <?php else: ?>
                                <span class="text-muted">Menunggu...</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<?php require "../frontend/layout/footer.html"; ?>
