<?php
session_start();
require '../config/config.php';

$success = "";
$error = "";
$reset_link = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Email tidak boleh kosong!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        $stmt = $conn->prepare("SELECT id, nama FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $check_table = $conn->query("SHOW TABLES LIKE 'password_resets'");
            if ($check_table->num_rows === 0) {
                $conn->query("CREATE TABLE password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )");
            }
            
            $conn->query("DELETE FROM password_resets WHERE user_id = " . $user['id']);
            
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $token, $expires);
            
            if ($stmt->execute()) {
                $reset_link = "reset_password.php?token=" . $token;
                $success = "Link reset password telah dibuat untuk " . htmlspecialchars($user['nama']) . ". Link akan kadaluarsa dalam 1 jam.";
            } else {
                $error = "Gagal membuat token reset. Silakan coba lagi.";
            }
        } else {
            $error = "Email tidak terdaftar dalam sistem!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - LampungSmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/logo-navbar.css">
    <style>
        .reset-link-box {
            background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%);
            border: 1px solid #4CAF50;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            word-break: break-all;
        }
        .reset-link-box a {
            color: #1B5E20;
            font-weight: 600;
            text-decoration: none;
        }
        .reset-link-box a:hover {
            text-decoration: underline;
        }
        .info-note {
            background: #FFF3E0;
            border-left: 4px solid #FF9800;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card card-login p-4 p-md-5 bg-white">
                    <div class="card-body px-0">
                        <div class="text-center mb-4">
                            <img src="../assets/images/logo-lampung.png" alt="Logo Lampung" class="logo-lampung-sidebar">
                            <h3 class="fw-bold text-brand-primary">Lupa Password</h3>
                            <p class="text-muted">Masukkan email terdaftar untuk reset password</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 text-center mb-4">
                                <small><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></small>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success py-2 text-center mb-3">
                                <small><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></small>
                            </div>
                            
                            <?php if ($reset_link): ?>
                                <div class="reset-link-box">
                                    <p class="mb-2 text-muted small"><i class="fas fa-link me-1"></i> Klik link berikut untuk reset password:</p>
                                    <a href="<?php echo $reset_link; ?>"><?php echo $reset_link; ?></a>
                                </div>
                                <!-- <div class="info-note">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Catatan:</strong> Pada implementasi produksi, link ini akan dikirim ke email Anda.
                                </div> -->
                            <?php endif; ?>
                        <?php else: ?>
                            <form action="" method="POST">
                                <div class="mb-4">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control fw-medium" placeholder="nama@email.com" required>
                                    </div>
                                </div>

                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-brand-yellow btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i> Kirim Link Reset
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="login.php" class="text-decoration-none text-muted">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke halaman login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
