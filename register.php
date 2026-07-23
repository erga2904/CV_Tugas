<?php
/**
 * User Self-Registration Page - Clean Inter Font Theme
 */

require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm_p = trim($_POST['confirm_password'] ?? '');

    $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '_', $username));

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_p) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 4) {
        $error = "Password minimal 4 karakter.";
    } else {
        if ($db) {
            $check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR slug = ? LIMIT 1");
            $check->bind_param("sss", $username, $email, $slug);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "Username, Email, atau Slug sudah digunakan oleh akun lain.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $role = 'user';
                $is_default = 0;
                $title = 'Web Developer & Specialist';

                $stmt = $db->prepare("INSERT INTO users (username, slug, full_name, title, email, password, role, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssi", $username, $slug, $full_name, $title, $email, $hashed_password, $role, $is_default);

                if ($stmt->execute()) {
                    header("Location: " . $base_url . "/login.php?msg=" . urlencode("Pendaftaran berhasil! Silakan login dengan akun baru Anda."));
                    exit();
                } else {
                    $error = "Gagal mendaftar: " . $stmt->error;
                }
            }
        } else {
            $error = "Database tidak terhubung. Silakan periksa file config/db.php dan impor database.sql.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru — Aplikasi CV Multi-User</title>

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
            padding: 30px 20px;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            padding: 40px;
            width: 100%;
            max-width: 480px;
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
            font-size: 0.92rem;
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
    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="auth-brand mb-1"><i class="bi bi-person-plus text-primary me-2"></i>Daftar Akun CV Baru</div>
        <p class="text-muted small">Buat CV profesional Anda dalam beberapa langkah</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 small border-0 mb-3" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="full_name" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="contoh: Erga Refaldy D.G" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">Username (URL Slug CV Anda)</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="contoh: erga_refaldy" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="erga@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-submit w-100 mb-3">Buat Akun CV Saya</button>
    </form>

    <div class="text-center pt-3 border-top mt-3" style="font-size: 0.85rem;">
        <span class="text-muted">Sudah punya akun?</span> 
        <a href="login.php" class="text-dark fw-semibold text-decoration-none ms-1">Login Sekarang</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
