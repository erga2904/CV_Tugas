<?php
/**
 * Experience Management Module (Dynamic per User) - Clean Placeholders
 */

$message = '';
$selected_user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 2;
$edit_exp = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_exp') {
        $exp_id      = (int)($_POST['exp_id'] ?? 0);
        $user_id     = (int)$_POST['user_id'];
        $company     = trim($_POST['company_name'] ?? '');
        $title       = trim($_POST['job_title'] ?? '');
        $start       = $_POST['start_date'] ?? '';
        $end         = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
        $is_current  = isset($_POST['is_current']) ? 1 : 0;
        $description = trim($_POST['description'] ?? '');

        if ($is_current) {
            $end = NULL;
        }

        if ($exp_id > 0) {
            $stmt = $db->prepare("UPDATE experiences SET company_name=?, job_title=?, start_date=?, end_date=?, is_current=?, description=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssssisii", $company, $title, $start, $end, $is_current, $description, $exp_id, $user_id);
            if ($stmt->execute()) {
                $message = "Success: Pengalaman kerja berhasil diperbarui!";
            } else {
                $message = "Error: Gagal menyimpan data - " . $stmt->error;
            }
        } else {
            $stmt = $db->prepare("INSERT INTO experiences (user_id, company_name, job_title, start_date, end_date, is_current, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssis", $user_id, $company, $title, $start, $end, $is_current, $description);
            if ($stmt->execute()) {
                $message = "Success: Pengalaman kerja baru berhasil ditambahkan!";
            } else {
                $message = "Error: Gagal menambah data - " . $stmt->error;
            }
        }
    } elseif ($action === 'delete_exp') {
        $del_id  = (int)$_POST['exp_id'];
        $user_id = (int)$_POST['user_id'];
        $stmt    = $db->prepare("DELETE FROM experiences WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $del_id, $user_id);
        if ($stmt->execute()) {
            $message = "Success: Pengalaman kerja berhasil dihapus.";
        } else {
            $message = "Error: Gagal menghapus data.";
        }
    }
}

// Fetch edit item if requested
if (isset($_GET['edit_exp_id']) && $db) {
    $edit_exp_id = (int)$_GET['edit_exp_id'];
    $res = $db->query("SELECT * FROM experiences WHERE id = {$edit_exp_id}");
    if ($res) {
        $edit_exp = $res->fetch_assoc();
        if ($edit_exp) {
            $selected_user_id = $edit_exp['user_id'];
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

// Fetch experiences for selected user
$experiences = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM experiences WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $experiences[] = $r;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold"><i class="bi bi-briefcase-fill text-success me-2"></i>Manajemen Pengalaman Kerja</h3>
    
    <!-- User Switcher Filter -->
    <form method="GET" action="dashboard.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="page" value="experiences">
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
            <div class="card-header bg-success text-white fw-bold py-3">
                <i class="bi bi-plus-circle me-1"></i> <?= $edit_exp ? 'Edit Pengalaman' : 'Tambah Pengalaman Kerja' ?>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_exp">
                    <input type="hidden" name="exp_id" value="<?= $edit_exp['id'] ?? 0 ?>">
                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Perusahaan / Instansi</label>
                        <input type="text" class="form-control" name="company_name" required value="<?= htmlspecialchars($edit_exp['company_name'] ?? '') ?>" placeholder="Masukkan nama perusahaan / instansi">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jabatan / Job Title</label>
                        <input type="text" class="form-control" name="job_title" required value="<?= htmlspecialchars($edit_exp['job_title'] ?? '') ?>" placeholder="Masukkan jabatan / job title">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" required value="<?= htmlspecialchars($edit_exp['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($edit_exp['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_current" id="is_current_check" <?= ($edit_exp['is_current'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="is_current_check">Masih Bekerja di Sini (Present)</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Deskripsi / Tanggung Jawab</label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Tuliskan deskripsi tugas dan tanggung jawab Anda..."><?= htmlspecialchars($edit_exp['description'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold text-white">
                        <i class="bi bi-save me-1"></i> Simpan Data Pengalaman
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Table -->
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-list-task me-1"></i> Riwayat Pengalaman User ID: <?= $selected_user_id ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Perusahaan & Jabatan</th>
                                <th>Periode</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($experiences)): ?>
                                <?php foreach ($experiences as $exp): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($exp['job_title']) ?></div>
                                            <small class="text-muted"><i class="bi bi-building me-1"></i><?= htmlspecialchars($exp['company_name']) ?></small>
                                        </td>
                                        <td>
                                            <small class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($exp['start_date']) ?> s/d 
                                                <?= $exp['is_current'] ? 'Sekarang' : htmlspecialchars($exp['end_date'] ?? 'N/A') ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <a href="dashboard.php?page=experiences&user_id=<?= $selected_user_id ?>&edit_exp_id=<?= $exp['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus pengalaman ini?');">
                                                <input type="hidden" name="action" value="delete_exp">
                                                <input type="hidden" name="exp_id" value="<?= $exp['id'] ?>">
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
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada riwayat pengalaman untuk user ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>