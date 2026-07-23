<?php
/**
 * Admin Dashboard Main Controller - Custom Styled Dropdowns & Clean Inter Font Design
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if (basename($script_dir) === 'admin') {
    $root_dir = dirname($script_dir);
} else {
    $root_dir = $script_dir;
}
$base_url = ($root_dir === '/' || $root_dir === '\\') ? '' : $root_dir;

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ' . $base_url . '/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

$page = $_GET['page'] ?? 'dashboard';

$flash_message = '';
if (isset($_POST['set_default_user_id']) && $db) {
    $def_id = (int)$_POST['set_default_user_id'];
    $db->query("UPDATE users SET is_default = 0");
    $stmt = $db->prepare("UPDATE users SET is_default = 1 WHERE id = ?");
    $stmt->bind_param("i", $def_id);
    if ($stmt->execute()) {
        $flash_message = "CV Default berhasil diubah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Dashboard Pengelola CV</title>

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
        }

        .card-stat {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.03em;
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        /* CUSTOM DROPDOWN STYLING ACROSS ALL SELECT MENUS */
        .form-select, .form-select-sm, .form-select-lg {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%3c0f172a' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 1rem center !important;
            background-size: 14px 12px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding-right: 40px !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            transition: all 0.2s ease-in-out !important;
            cursor: pointer !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        }

        .form-select:hover {
            border-color: #0f172a !important;
            background-color: #f8fafc !important;
        }

        .form-select:focus {
            border-color: #0f172a !important;
            outline: 0 !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12) !important;
            background-color: #ffffff !important;
        }

        .form-select option {
            padding: 10px 14px;
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-size: 0.92rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="min-vh-100">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/includes/sidebar_nav.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
                <h1 class="h3 fw-bold text-dark mb-0">Admin Panel</h1>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white px-3 py-2 fw-medium">
                        <i class="bi bi-person me-1"></i> Admin: <?= htmlspecialchars($_SESSION['full_name'] ?? 'Administrator') ?>
                    </span>
                </div>
            </div>

            <?php if ($flash_message): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($flash_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Content Area Module Loader -->
            <div id="content-area">
                <?php 
                if ($page === 'experiences') {
                    include __DIR__ . '/includes/experience_management.php';
                } elseif ($page === 'users') {
                    include __DIR__ . '/includes/user_management.php';
                } elseif ($page === 'education') {
                    include __DIR__ . '/includes/education_management.php';
                } elseif ($page === 'skills') {
                    include __DIR__ . '/includes/skill_management.php';
                } else {
                    // Default dashboard overview
                    ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="stat-label">Total Users</div>
                                <?php 
                                $user_count = 0;
                                if ($db) {
                                    $res = $db->query("SELECT COUNT(*) as cnt FROM users");
                                    if ($res) $user_count = $res->fetch_assoc()['cnt'];
                                }
                                ?>
                                <div class="stat-value"><?= $user_count ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="stat-label">Pengalaman</div>
                                <?php 
                                $exp_count = 0;
                                if ($db) {
                                    $res = $db->query("SELECT COUNT(*) as cnt FROM experiences");
                                    if ($res) $exp_count = $res->fetch_assoc()['cnt'];
                                }
                                ?>
                                <div class="stat-value"><?= $exp_count ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="stat-label">Pendidikan</div>
                                <?php 
                                $edu_count = 0;
                                if ($db) {
                                    $res = $db->query("SELECT COUNT(*) as cnt FROM education");
                                    if ($res) $edu_count = $res->fetch_assoc()['cnt'];
                                }
                                ?>
                                <div class="stat-value"><?= $edu_count ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card-stat">
                                <div class="stat-label">Skills</div>
                                <?php 
                                $skill_count = 0;
                                if ($db) {
                                    $res = $db->query("SELECT COUNT(*) as cnt FROM skills");
                                    if ($res) $skill_count = $res->fetch_assoc()['cnt'];
                                }
                                ?>
                                <div class="stat-value"><?= $skill_count ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Default CV Selector Card -->
                    <div class="card card-custom p-4 mb-4">
                        <h5 class="fw-bold mb-2">Pengaturan CV Default</h5>
                        <p class="text-muted small mb-3">Pilih pengguna yang CV-nya ditampilkan otomatis saat membuka <code>http://localhost/project_cv</code>.</p>
                        
                        <form method="post" action="" class="row align-items-center g-3">
                            <div class="col-md-7">
                                <select name="set_default_user_id" class="form-select py-2" required>
                                    <?php 
                                    if ($db) {
                                        $users_res = $db->query("SELECT id, full_name, username, slug, is_default FROM users ORDER BY id ASC");
                                        while ($u = $users_res->fetch_assoc()) {
                                            $selected = $u['is_default'] ? 'selected' : '';
                                            $mark = $u['is_default'] ? ' (SAAT INI DEFAULT)' : '';
                                            echo "<option value='{$u['id']}' {$selected}>{$u['full_name']} (@{$u['username']}) — Slug: {$u['slug']}{$mark}</option>";
                                        }
                                    } else {
                                        echo "<option value='2'>Erga Refaldy D.G (erga_refaldy)</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark w-100 fw-medium py-2">
                                    Simpan Default CV
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Links Card -->
                    <div class="card card-custom p-4">
                        <h6 class="fw-bold mb-3">Tautan Cepat Sesuai Soal Tugas:</h6>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><strong>1. Admin Dashboard:</strong> <code class="ms-2">http://localhost/project_cv/Admin</code></div>
                                <a href="dashboard.php" class="btn btn-sm btn-outline-dark">Buka</a>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><strong>2. Login User/Admin:</strong> <code class="ms-2">http://localhost/project_cv/login.php</code></div>
                                <a href="../login.php" target="_blank" class="btn btn-sm btn-outline-dark">Buka</a>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><strong>3. Tampilkan CV Erga Refaldy D.G:</strong> <code class="ms-2">http://localhost/project_cv/erga_refaldy</code></div>
                                <a href="../erga_refaldy" target="_blank" class="btn btn-sm btn-outline-dark">Buka CV Erga</a>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div><strong>4. Tampilkan CV Default System:</strong> <code class="ms-2">http://localhost/project_cv</code></div>
                                <a href="../" target="_blank" class="btn btn-sm btn-dark">Buka CV Default</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>