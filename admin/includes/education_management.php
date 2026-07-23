<?php
/**
 * Education Management Module (Dynamic per User)
 */

$message = '';
$selected_user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 2;
$edit_edu = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_edu') {
        $edu_id      = (int)($_POST['edu_id'] ?? 0);
        $user_id     = (int)$_POST['user_id'];
        $school      = trim($_POST['school_name'] ?? '');
        $degree      = trim($_POST['degree_obtained'] ?? '');
        $major       = trim($_POST['major'] ?? '');
        $start_year  = !empty($_POST['start_year']) ? (int)$_POST['start_year'] : NULL;
        $grad_year   = !empty($_POST['graduation_year']) ? (int)$_POST['graduation_year'] : NULL;
        $description = trim($_POST['description'] ?? '');

        if ($edu_id > 0) {
            $stmt = $db->prepare("UPDATE education SET school_name=?, degree_obtained=?, major=?, start_year=?, graduation_year=?, description=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sssiiiii", $school, $degree, $major, $start_year, $grad_year, $description, $edu_id, $user_id);
            if ($stmt->execute()) {
                $message = "Success: Riwayat pendidikan berhasil diperbarui!";
            } else {
                $message = "Error: Gagal menyimpan data - " . $stmt->error;
            }
        } else {
            $stmt = $db->prepare("INSERT INTO education (user_id, school_name, degree_obtained, major, start_year, graduation_year, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiis", $user_id, $school, $degree, $major, $start_year, $grad_year, $description);
            if ($stmt->execute()) {
                $message = "Success: Riwayat pendidikan baru berhasil ditambahkan!";
            } else {
                $message = "Error: Gagal menambah data - " . $stmt->error;
            }
        }
    } elseif ($action === 'delete_edu') {
        $del_id  = (int)$_POST['edu_id'];
        $user_id = (int)$_POST['user_id'];
        $stmt    = $db->prepare("DELETE FROM education WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $del_id, $user_id);
        if ($stmt->execute()) {
            $message = "Success: Data pendidikan berhasil dihapus.";
        } else {
            $message = "Error: Gagal menghapus data.";
        }
    }
}

// Fetch edit item if requested
if (isset($_GET['edit_edu_id']) && $db) {
    $edit_edu_id = (int)$_GET['edit_edu_id'];
    $res = $db->query("SELECT * FROM education WHERE id = {$edit_edu_id}");
    if ($res) {
        $edit_edu = $res->fetch_assoc();
        if ($edit_edu) {
            $selected_user_id = $edit_edu['user_id'];
        }
    }
}

// Get user list for dropdown
$users = [];
if ($db) {
    $res = $db->query("SELECT id, full_name, username FROM users ORDER BY id ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $users[] = $r;
        }
    }
}

// Fetch education for selected user
$education_list = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM education WHERE user_id = ? ORDER BY graduation_year DESC");
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $education_list[] = $r;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold"><i class="bi bi-journal-bookmark-fill text-warning me-2"></i>Manajemen Riwayat Pendidikan</h3>
    
    <!-- User Switcher Filter -->
    <form method="GET" action="dashboard.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="page" value="education">
        <label class="fw-semibold text-nowrap">Pilih User:</label>
        <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= $u['id'] == $selected_user_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['full_name']) ?> (@<?= htmlspecialchars($u['username']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($message): ?>
    <div class="alert <?= strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Form -->
    <div class="col-lg-5">
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold py-3">
                <i class="bi bi-mortarboard me-1"></i> <?= $edit_edu ? 'Edit Pendidikan' : 'Tambah Pendidikan Baru' ?>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_edu">
                    <input type="hidden" name="edu_id" value="<?= $edit_edu['id'] ?? 0 ?>">
                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Institusi / Nama Sekolah / Kampus</label>
                        <input type="text" class="form-control" name="school_name" required value="<?= htmlspecialchars($edit_edu['school_name'] ?? '') ?>" placeholder="Universitas Komputer Indonesia">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gelar / Tingkat Pendidikan</label>
                        <input type="text" class="form-control" name="degree_obtained" required value="<?= htmlspecialchars($edit_edu['degree_obtained'] ?? '') ?>" placeholder="Sarjana Komputer (S.Kom)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jurusan / Program Studi</label>
                        <input type="text" class="form-control" name="major" value="<?= htmlspecialchars($edit_edu['major'] ?? '') ?>" placeholder="Teknik Informatika">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tahun Mulai</label>
                            <input type="number" class="form-control" name="start_year" value="<?= htmlspecialchars($edit_edu['start_year'] ?? '') ?>" placeholder="2013">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tahun Lulus</label>
                            <input type="number" class="form-control" name="graduation_year" value="<?= htmlspecialchars($edit_edu['graduation_year'] ?? '') ?>" placeholder="2017">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan Tambahan / IPK</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Contoh: Lulus Cumlaude IPK 3.82..."><?= htmlspecialchars($edit_edu['description'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark">
                        <i class="bi bi-save me-1"></i> Simpan Data Pendidikan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Table -->
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-list-stars me-1"></i> Daftar Pendidikan User ID: <?= $selected_user_id ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Institusi & Gelar</th>
                                <th>Tahun Lulus</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($education_list)): ?>
                                <?php foreach ($education_list as $edu): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($edu['degree_obtained']) ?> - <?= htmlspecialchars($edu['major'] ?? '') ?></div>
                                            <small class="text-muted"><i class="bi bi-bank me-1"></i><?= htmlspecialchars($edu['school_name']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($edu['start_year'] ?? '') ?> - <?= htmlspecialchars($edu['graduation_year'] ?? 'Selesai') ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="dashboard.php?page=education&user_id=<?= $selected_user_id ?>&edit_edu_id=<?= $edu['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus data pendidikan ini?');">
                                                <input type="hidden" name="action" value="delete_edu">
                                                <input type="hidden" name="edu_id" value="<?= $edu['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada data pendidikan untuk user ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>