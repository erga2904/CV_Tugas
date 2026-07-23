<?php
/**
 * Skill Management Module (Dynamic per User)
 */

$message = '';
$selected_user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : 2;
$edit_skill = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_skill') {
        $skill_id    = (int)($_POST['skill_id'] ?? 0);
        $user_id     = (int)$_POST['user_id'];
        $skill_name  = trim($_POST['skill_name'] ?? '');
        $level       = trim($_POST['proficiency_level'] ?? 'Intermediate');
        $percentage  = (int)($_POST['percentage'] ?? 80);
        $category    = trim($_POST['category'] ?? 'Technical');

        if ($skill_id > 0) {
            $stmt = $db->prepare("UPDATE skills SET skill_name=?, proficiency_level=?, percentage=?, category=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssisii", $skill_name, $level, $percentage, $category, $skill_id, $user_id);
            if ($stmt->execute()) {
                $message = "Success: Skill berhasil diperbarui!";
            } else {
                $message = "Error: Gagal menyimpan skill - " . $stmt->error;
            }
        } else {
            $stmt = $db->prepare("INSERT INTO skills (user_id, skill_name, proficiency_level, percentage, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issis", $user_id, $skill_name, $level, $percentage, $category);
            if ($stmt->execute()) {
                $message = "Success: Skill baru berhasil ditambahkan!";
            } else {
                $message = "Error: Gagal menambah skill - " . $stmt->error;
            }
        }
    } elseif ($action === 'delete_skill') {
        $del_id  = (int)$_POST['skill_id'];
        $user_id = (int)$_POST['user_id'];
        $stmt    = $db->prepare("DELETE FROM skills WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $del_id, $user_id);
        if ($stmt->execute()) {
            $message = "Success: Skill berhasil dihapus.";
        } else {
            $message = "Error: Gagal menghapus skill.";
        }
    }
}

// Fetch edit item if requested
if (isset($_GET['edit_skill_id']) && $db) {
    $edit_skill_id = (int)$_GET['edit_skill_id'];
    $res = $db->query("SELECT * FROM skills WHERE id = {$edit_skill_id}");
    if ($res) {
        $edit_skill = $res->fetch_assoc();
        if ($edit_skill) {
            $selected_user_id = $edit_skill['user_id'];
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

// Fetch skills for selected user
$skills = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $skills[] = $r;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold"><i class="bi bi-tools text-info me-2"></i>Manajemen Keahlian (Skills)</h3>
    
    <!-- User Switcher Filter -->
    <form method="GET" action="dashboard.php" class="d-flex align-items-center gap-2">
        <input type="hidden" name="page" value="skills">
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
            <div class="card-header bg-info text-white fw-bold py-3">
                <i class="bi bi-gear-fill me-1"></i> <?= $edit_skill ? 'Edit Keahlian' : 'Tambah Keahlian Baru' ?>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_skill">
                    <input type="hidden" name="skill_id" value="<?= $edit_skill['id'] ?? 0 ?>">
                    <input type="hidden" name="user_id" value="<?= $selected_user_id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Keahlian / Skill</label>
                        <input type="text" class="form-control" name="skill_name" required value="<?= htmlspecialchars($edit_skill['skill_name'] ?? '') ?>" placeholder="PHP & MySQL">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tingkat Kemahiran</label>
                            <select name="proficiency_level" class="form-select">
                                <option value="Beginner" <?= ($edit_skill['proficiency_level'] ?? '') === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="Intermediate" <?= ($edit_skill['proficiency_level'] ?? '') === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="Advanced" <?= ($edit_skill['proficiency_level'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                                <option value="Expert" <?= ($edit_skill['proficiency_level'] ?? '') === 'Expert' ? 'selected' : '' ?>>Expert</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori</label>
                            <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($edit_skill['category'] ?? 'Backend') ?>" placeholder="Backend / Frontend">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Persentase Penguasaan (1 - 100%)</label>
                        <input type="number" class="form-control" name="percentage" min="10" max="100" value="<?= htmlspecialchars($edit_skill['percentage'] ?? 85) ?>">
                    </div>

                    <button type="submit" class="btn btn-info w-100 py-2 fw-bold text-white">
                        <i class="bi bi-save me-1"></i> Simpan Data Skill
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Table -->
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-cpu me-1"></i> Daftar Keahlian User ID: <?= $selected_user_id ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Skill & Kategori</th>
                                <th>Tingkat</th>
                                <th>Penguasaan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($skills)): ?>
                                <?php foreach ($skills as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['skill_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($s['category']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($s['proficiency_level']) ?></span>
                                        </td>
                                        <td style="width: 25%;">
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= (int)$s['percentage'] ?>%;"></div>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= (int)$s['percentage'] ?>%</small>
                                        </td>
                                        <td class="text-end">
                                            <a href="dashboard.php?page=skills&user_id=<?= $selected_user_id ?>&edit_skill_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Hapus skill ini?');">
                                                <input type="hidden" name="action" value="delete_skill">
                                                <input type="hidden" name="skill_id" value="<?= $s['id'] ?>">
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
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada data keahlian untuk user ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>