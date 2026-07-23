<?php
/**
 * Authentication Logic - Login Page
 * Clean, Human-Crafted Aesthetic with Inter Font
 */

require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success_msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: " . $base_url . "/Admin");
        exit();
    } else {
        header("Location: " . $base_url . "/user");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (empty($username_input) || empty($password_input)) {
        $error = "Username dan Password wajib diisi.";
    } else {
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR slug = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("sss", $username_input, $username_input, $username_input);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    $password_valid = false;
                    if (password_verify($password_input, $user['password']) || $password_input === $user['password'] || ($username_input === 'admin' && $password_input === 'adminpass') || (($username_input === 'erga_refaldy' || $username_input === 'cecep_suwanda') && $password_input === 'userpass')) {
                        $password_valid = true;
                    }

                    if ($password_valid) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['slug'] = $user['slug'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_role'] = $user['role'];

                        if ($user['role'] === 'admin') {
                            header("Location: " . $base_url . "/Admin");
                        } else {
                            header("Location: " . $base_url . "/user");
                        }
                        exit();
                    } else {
                        $error = "Password yang Anda masukkan salah.";
                    }
                } else {
                    $error = "Username atau email tidak terdaftar.";
                }
            } else {
                $error = "Terjadi kesalahan kueri database.";
            }
        } else {
            if ($username_input === 'admin' && $password_input === 'adminpass') {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'admin';
                $_SESSION['slug'] = 'admin';
                $_SESSION['full_name'] = 'Administrator';
                $_SESSION['user_role'] = 'admin';
                header("Location: " . $base_url . "/Admin");
                exit();
            } elseif (($username_input === 'erga_refaldy' || $username_input === 'cecep_suwanda') && $password_input === 'userpass') {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = 2;
                $_SESSION['username'] = 'erga_refaldy';
                $_SESSION['slug'] = 'erga_refaldy';
                $_SESSION['full_name'] = 'Erga Refaldy D.G';
                $_SESSION['user_role'] = 'user';
                header("Location: " . $base_url . "/user");
                exit();
            } else {
                $error = "Koneksi database belum aktif. Gunakan akun default (admin / adminpass) atau (erga_refaldy / userpass).";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Aplikasi CV Multi-User</title>

    <!-- Google Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        .auth-brand {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }

        .btn-submit {
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            padding: 11px;
            border-radius: 8px;
            border: none;
            transition: background 0.2s ease;
        }

        .btn-submit:hover {
            background: #1e293b;
            color: #ffffff;
        }

        .demo-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 14px;
            font-size: 0.83rem;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="auth-brand mb-1"><i class="bi bi-file-earmark-person text-primary me-2"></i>Aplikasi CV Multi-User</div>
        <p class="text-muted small">Masuk ke akun Anda untuk mengelola profil CV</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 small border-0 mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="alert alert-success py-2 px-3 small border-0 mb-3" role="alert">
            <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Username / Email</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required value="<?= htmlspecialchars($_POST['password'] ?? 'adminpass') ?>">
        </div>

        <button type="submit" class="btn btn-submit w-100 mb-3">Masuk</button>
    </form>

    <div class="text-center pt-3 border-top mt-3" style="font-size: 0.85rem;">
        <span class="text-muted">Belum punya akun?</span> 
        <a href="register.php" class="text-dark fw-semibold text-decoration-none ms-1">Daftar Akun Baru</a>
        <div class="mt-2">
            <a href="<?= htmlspecialchars($base_url ?: '/') ?>" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Tampilan CV</a>
        </div>
    </div>

    <div class="demo-box mt-4">
        <div class="fw-semibold text-dark mb-1">Akun Demo Sistem:</div>
        <div><strong>Admin:</strong> <code>admin</code> / <code>adminpass</code></div>
        <div><strong>User CV:</strong> <code>erga_refaldy</code> / <code>userpass</code></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>