<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: /auth/login.php");
    exit;
}

// TARIK REQUIRE DARI DASHBOARD
require_once "../config/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_usaha    = trim($_POST['nama_usaha']);
    $nama_pemilik  = trim($_POST['nama_pemilik']);
    $bidang_usaha  = trim($_POST['bidang_usaha']);
    $alamat_usaha  = trim($_POST['alamat_usaha']);
    $no_telepon    = trim($_POST['no_telepon']);

    // VALIDASI KOSONG
    if ($nama_usaha == "" || $nama_pemilik == "" || $bidang_usaha == "" || 
        $alamat_usaha == "" || $no_telepon == "") {
        
        header("Location: daftar_umkm.php?error=Semua field harus diisi");
        exit;
    }

    // VALIDASI ANGKA TELEPON
    if (!ctype_digit($no_telepon)) {
        header("Location: daftar_umkm.php?error=Nomor telepon harus angka");
        exit;
    }

    if (strlen($no_telepon) < 10) {
        header("Location: daftar_umkm.php?error=Nomor telepon minimal 10 digit");
        exit;
    }

    // Placeholder user_id (nantinya dari session login)
    $user_id = 1;

    // QUERY INSERT
    $query = "INSERT INTO umkm (user_id, nama_usaha, bidang_usaha, alamat_usaha, nama_pemilik, no_telepon)
              VALUES ('$user_id', '$nama_usaha', '$bidang_usaha', '$alamat_usaha', '$nama_pemilik', '$no_telepon')";

    if (mysqli_query($conn, $query)) {
        header("Location: daftar_umkm.php?success=Pendaftaran UMKM berhasil!");
        exit;
    } else {
        header("Location: daftar_umkm.php?error=Gagal menyimpan data: " . mysqli_error($conn));
        exit;
    }
}
require '../layouts/header.php';
require '../layouts/sidebar.php';
?>

<div class="flex-grow-1 p-4" style="background:#f8f9fa;">

    <h2 class="text-center mt-4 mb-4">Pendaftaran UMKM</h2>

    <div class="container mb-5 pb-5">

        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger"><?= $_GET['error']; ?></div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success"><?= $_GET['success']; ?></div>
        <?php } ?>

        <!-- FORM -->
        <form action="" method="POST" class="mt-4">

            <div class="row">

                <!-- KIRI -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Nama Usaha:</label>
                        <input type="text" name="nama_usaha" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Pemilik:</label>
                        <input type="text" name="nama_pemilik" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Usaha:</label>
                        <textarea name="alamat_usaha" class="form-control" rows="4" required></textarea>
                    </div>

                </div>

                <!-- KANAN -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Bidang Usaha:</label>
                        <input type="text" name="bidang_usaha" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon:</label>
                        <input type="tel" name="no_telepon" placeholder="08xxxxxxxxxx" class="form-control" required>
                    </div>

                </div>

            </div>

            <!-- TOMBOL -->
            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-dark px-4 py-2 shadow">Daftar</button>
            </div>

        </form>
    </div>

    <?php require '../layouts/footer.php'; ?>

</div>


<!-- MODAL SUKSES -->
<div class="modal fade" id="modalSukses" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <h4 class="text-success mb-3">Berhasil!</h4>
            <p>Pendaftaran UMKM telah berhasil dikirim.</p>
            <button type="button" class="btn btn-success mt-2" data-bs-dismiss="modal">OK</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_GET['success'])) { ?>
<script>
    var modal = new bootstrap.Modal(document.getElementById('modalSukses'));
    modal.show();
</script>
<?php } ?>

</body>
</html>
