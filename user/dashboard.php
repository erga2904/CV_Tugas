<?php
/**
 * Regular User Dashboard (Self CV Management) - Custom Styled Dropdowns
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$root_dir = (basename($script_dir) === 'user') ? dirname($script_dir) : $script_dir;
$base_url = ($root_dir === '/' || $root_dir === '\\') ? '' : $root_dir;

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ' . $base_url . '/login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? 2;
$tab = $_GET['tab'] ?? 'profile';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $title     = trim($_POST['title'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $address   = trim($_POST['address'] ?? '');
        $summary   = trim($_POST['summary'] ?? '');

        // Fetch existing photo path
        $photo_path = NULL;
        $res_p = $db->query("SELECT photo FROM users WHERE id = {$user_id}");
        if ($res_p && $row_p = $res_p->fetch_assoc()) {
            $photo_path = $row_p['photo'];
        }

        // Process photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_name = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $new_name = 'profile_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $target_dir = __DIR__ . '/../uploads/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                    $photo_path = 'uploads/' . $new_name;
                }
            }
        }

        $stmt = $db->prepare("UPDATE users SET full_name=?, title=?, email=?, phone=?, address=?, summary=?, photo=? WHERE id=?");
        $stmt->bind_param("sssssssi", $full_name, $title, $email, $phone, $address, $summary, $photo_path, $user_id);
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $message = "Success: Profil berhasil diperbarui!";
        } else {
            $message = "Error: Gagal menyimpan profil - " . $stmt->error;
        }
    } elseif ($action === 'add_exp') {
        $company     = trim($_POST['company_name']);
        $job_title   = trim($_POST['job_title']);
        $start_date  = $_POST['start_date'];
        $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
        $is_current  = isset($_POST['is_current']) ? 1 : 0;
        $description = trim($_POST['description']);

        if ($is_current) $end_date = NULL;

        $stmt = $db->prepare("INSERT INTO experiences (user_id, company_name, job_title, start_date, end_date, is_current, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $user_id, $company, $job_title, $start_date, $end_date, $is_current, $description);
        if ($stmt->execute()) {
            $message = "Success: Pengalaman baru berhasil ditambahkan!";
        }
    } elseif ($action === 'add_edu') {
        $school      = trim($_POST['school_name']);
        $degree      = trim($_POST['degree_obtained']);
        $major       = trim($_POST['major']);
        $start_year  = !empty($_POST['start_year']) ? (int)$_POST['start_year'] : NULL;
        $grad_year   = !empty($_POST['graduation_year']) ? (int)$_POST['graduation_year'] : NULL;
        $description = trim($_POST['description']);

        $stmt = $db->prepare("INSERT INTO education (user_id, school_name, degree_obtained, major, start_year, graduation_year, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssiis", $user_id, $school, $degree, $major, $start_year, $grad_year, $description);
        if ($stmt->execute()) {
            $message = "Success: Pendidikan baru berhasil ditambahkan!";
        }
    } elseif ($action === 'add_skill') {
        $skill_name  = trim($_POST['skill_name']);
        $level       = trim($_POST['proficiency_level']);
        $percentage  = (int)$_POST['percentage'];
        $category    = trim($_POST['category']);

        $stmt = $db->prepare("INSERT INTO skills (user_id, skill_name, proficiency_level, percentage, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_id, $skill_name, $level, $percentage, $category);
        if ($stmt->execute()) {
            $message = "Success: Skill baru berhasil ditambahkan!";
        }
    }
}

$user_data = null;
$experiences = [];
$education = [];
$skills = [];

if ($db) {
    $res = $db->query("SELECT * FROM users WHERE id = {$user_id}");
    if ($res) $user_data = $res->fetch_assoc();

    $res_e = $db->query("SELECT * FROM experiences WHERE user_id = {$user_id} ORDER BY start_date DESC");
    while ($r = $res_e->fetch_assoc()) $experiences[] = $r;

    $res_edu = $db->query("SELECT * FROM education WHERE user_id = {$user_id} ORDER BY graduation_year DESC");
    while ($r = $res_edu->fetch_assoc()) $education[] = $r;

    $res_s = $db->query("SELECT * FROM skills WHERE user_id = {$user_id} ORDER BY id DESC");
    while ($r = $res_s->fetch_assoc()) $skills[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Pengguna — Kelola CV Saya</title>

    <!-- Google Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .card-custom { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .nav-pills .nav-link { font-size: 0.9rem; font-weight: 500; color: #475569; border-radius: 6px; }
        .nav-pills .nav-link.active { background-color: #0f172a; color: #ffffff; }

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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 border-bottom border-secondary border-opacity-25">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#" style="font-size: 1.05rem;">
            <i class="bi bi-person-workspace text-primary"></i> Pengelola CV
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small">Pengguna: <strong><?= htmlspecialchars($user_data['full_name'] ?? 'User') ?></strong></span>
            <a href="<?= htmlspecialchars($base_url ?: '/') ?>/<?= htmlspecialchars($user_data['slug'] ?? '') ?>" target="_blank" class="btn btn-outline-light btn-sm">
                <i class="bi bi-eye me-1"></i> Lihat CV Saya
            </a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show border-0" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Navigation Tabs Sidebar -->
        <div class="col-md-3">
            <div class="card card-custom p-3">
                <div class="nav flex-column nav-pills gap-1">
                    <a href="dashboard.php?tab=profile" class="nav-link <?= $tab === 'profile' ? 'active' : '' ?>">
                        <i class="bi bi-person me-2"></i> Profil Utama
                    </a>
                    <a href="dashboard.php?tab=experiences" class="nav-link <?= $tab === 'experiences' ? 'active' : '' ?>">
                        <i class="bi bi-briefcase me-2"></i> Pengalaman Kerja
                    </a>
                    <a href="dashboard.php?tab=education" class="nav-link <?= $tab === 'education' ? 'active' : '' ?>">
                        <i class="bi bi-journal-text me-2"></i> Pendidikan
                    </a>
                    <a href="dashboard.php?tab=skills" class="nav-link <?= $tab === 'skills' ? 'active' : '' ?>">
                        <i class="bi bi-tools me-2"></i> Keahlian (Skills)
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="col-md-9">
            <?php if ($tab === 'profile'): ?>
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3">Edit Profil Utama</h5>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Photo Upload Field -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto Profil (Gambar CV)</label>
                            <?php if (!empty($user_data['photo'])): ?>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <img src="../<?= htmlspecialchars($user_data['photo']) ?>" alt="Photo" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover;">
                                    <small class="text-muted">Foto profil saat ini</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <small class="text-muted" style="font-size: 0.75rem;">Pilih gambar foto profil baru (JPG/PNG/WEBP).</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" name="full_name" required value="<?= htmlspecialchars($user_data['full_name'] ?? '') ?>" placeholder="Masukkan nama lengkap Anda">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Gelar / Profesi</label>
                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($user_data['title'] ?? '') ?>" placeholder="Masukkan profesi / job title Anda">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" placeholder="Masukkan email Anda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Telepon / WA</label>
                                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" placeholder="Masukkan nomor telepon Anda">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user_data['address'] ?? '') ?>" placeholder="Masukkan kota / alamat Anda">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Ringkasan Profil (Summary)</label>
                            <textarea class="form-control" name="summary" rows="4" placeholder="Tuliskan ringkasan profil Anda..."><?= htmlspecialchars($user_data['summary'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark fw-medium px-4">Simpan Perubahan</button>
                    </form>
                </div>

            <?php elseif ($tab === 'experiences'): ?>
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Tambah Pengalaman Kerja</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_exp">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Perusahaan</label>
                                <input type="text" class="form-control" name="company_name" required placeholder="Masukkan nama perusahaan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jabatan</label>
                                <input type="text" class="form-control" name="job_title" required placeholder="Masukkan jabatan">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_current" id="is_curr">
                            <label class="form-check-label" for="is_curr">Masih Bekerja di Sini</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Pekerjaan</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Tuliskan deskripsi pekerjaan Anda..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark fw-medium">Tambah Pengalaman</button>
                    </form>
                </div>

            <?php elseif ($tab === 'education'): ?>
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Tambah Riwayat Pendidikan</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_edu">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Sekolah / Kampus</label>
                            <input type="text" class="form-control" name="school_name" required placeholder="Masukkan nama institusi">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gelar / Jenjang</label>
                                <input type="text" class="form-control" name="degree_obtained" required placeholder="Masukkan gelar / jenjang">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <input type="text" class="form-control" name="major" placeholder="Masukkan jurusan">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Mulai</label>
                                <input type="number" class="form-control" name="start_year" placeholder="2018">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Lulus</label>
                                <input type="number" class="form-control" name="graduation_year" placeholder="2022">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Tuliskan keterangan pendidikan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark fw-medium">Tambah Pendidikan</button>
                    </form>
                </div>

            <?php elseif ($tab === 'skills'): ?>
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Tambah Keahlian (Skill)</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_skill">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Skill</label>
                                <input type="text" class="form-control" name="skill_name" required placeholder="Masukkan nama skill">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tingkat Kemahiran</label>
                                <select name="proficiency_level" class="form-select">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate" selected>Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kategori</label>
                                <input type="text" class="form-control" name="category" value="Backend">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Persentase (1-100%)</label>
                                <input type="number" class="form-control" name="percentage" value="85" min="10" max="100">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark fw-medium">Tambah Skill</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
