<?php
session_start();
require '../config/config.php';

if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../dashboard/dashboard_admin.php");
    } else {
        header("Location: ../dashboard/dashboard_warga.php");
    }
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['profile_photo'] = $row['profile_photo'] ?? 'default.jpg'; // Simpan foto profil ke session

            if ($row['role'] == 'admin') {
                header("Location: ../dashboard/dashboard_admin.php");
            } else {
                header("Location: ../dashboard/dashboard_warga.php");
            }
            exit;
        }
    }
    $error = "Email atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LampungSmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/logo-navbar.css">
</head>

<body class="bg-auth"> 

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card card-login p-4 p-md-5 bg-white">
                    <div class="card-body px-0">
                        <div class="text-center mb-5">
                            <img src="../../assets/images/logo-lampung.png" alt="Logo Lampung" class="logo-lampung-sidebar"> 
                            <h3 class="fw-bold text-brand-primary">LampungSmart</h3>
                            <p class="text-muted">Masuk untuk mengakses layanan</p>
                        </div>

                        <?php if($error): ?>
                            <div class="alert alert-danger py-2 text-center mb-4">
                                <small><?php echo $error; ?></small>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-4">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control fw-medium" placeholder="nama@email.com" required>
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label">Password</label>
                                    <a href="#" class="text-decoration-none text-muted text-small" style="font-size: 0.9rem;">Lupa password?</a>
                                </div>
                                <input type="password" name="password" class="form-control fw-medium" placeholder="••••••" required>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" name="login" class="btn btn-brand-yellow btn-lg">MASUK SEKARANG</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">Belum punya akun?</span>
                            <a href="register.php" class="link-brand ms-1">Daftar Warga</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>