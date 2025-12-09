<?php
session_start();
require '../config/config.php'; 
$success = "";
$error = "";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = 'warga'; 

    $cek_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed_password', '$role')";

        if (mysqli_query($conn, $query)) {
            $success = "Registrasi Berhasil! Silakan Login.";
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - LampungSmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/logo-navbar.css">
</head>

<body class="bg-auth">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card card-login p-4 p-md-5 bg-white">
                    <div class="card-body px-0">
                        <div class="text-center mb-5">
                            <h3 class="fw-bold text-brand-primary">Buat Akun Baru</h3>
                            <p class="text-muted">Bergabung dengan LampungSmart</p>
                        </div>

                        <?php if($success): ?>
                            <div class="alert alert-success text-center mb-4">
                                <?php echo $success; ?> <br>
                                <a href="login.php" class="fw-bold text-success text-decoration-none">Klik disini untuk Login</a>
                            </div>
                        <?php endif; ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger text-center mb-4">
                                <small><?php echo $error; ?></small>
                            </div>
                        <?php endif; ?>

                        <?php if(!$success): ?>
                        <form action="" method="POST">
                            <div class="mb-4">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control fw-medium" placeholder="Contoh: Budi Santoso" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control fw-medium" placeholder="nama@email.com" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control fw-medium" placeholder="••••••" minlength="6" required>
                                <div class="form-text text-muted">Minimal 6 karakter</div>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" name="register" class="btn btn-brand-yellow btn-lg">DAFTAR AKUN</button>
                            </div>
                        </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <span class="text-muted">Sudah punya akun?</span>
                            <a href="login.php" class="link-brand ms-1">Login disini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>