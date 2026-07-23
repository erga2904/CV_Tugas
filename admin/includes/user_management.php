<?php
/**
 * User Profiles Management Module - With Profile Photo Upload Support
 */

$message = '';
$edit_user = null;

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$root_dir = (basename($script_dir) === 'admin') ? dirname($script_dir) : $script_dir;
$base_url = ($root_dir === '/' || $root_dir === '\\') ? '' : $root_dir;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_user') {
        $user_id    = (int)($_POST['user_id'] ?? 0);
        $username   = trim($_POST['username'] ?? '');
        $full_name  = trim($_POST['full_name'] ?? '');
        $title      = trim($_POST['title'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $summary    = trim($_POST['summary'] ?? '');
        $role       = $_POST['role'] ?? 'user';
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $password   = trim($_POST['password'] ?? '');

        // Fetch existing photo path
        $photo_path = NULL;
        if ($user_id > 0) {
            $res_p = $db->query("SELECT photo FROM users WHERE id = {$user_id}");
            if ($res_p && $row_p = $res_p->fetch_assoc()) {
                $photo_path = $row_p['photo'];
            }
        }

        // Process photo upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_name = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($ext, $allowed)) {
                $new_name = 'profile_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $target_dir = __DIR__ . '/../../uploads/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($tmp_name, $target_dir . $new_name)) {
                    $photo_path = 'uploads/' . $new_name;
                }
            }
        }

        // Auto slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '_', $username));

        if ($is_default) {
            $db->query("UPDATE users SET is_default = 0");
        }

        if ($user_id > 0) {
            // Update existing user
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET username=?, slug=?, full_name=?, title=?, email=?, phone=?, address=?, summary=?, photo=?, password=?, role=?, is_default=? WHERE id=?");
                $stmt->bind_param("sssssssssssii", $username, $slug, $full_name, $title, $email, $phone, $address, $summary, $photo_path, $hash, $role, $is_default, $user_id);
            } else {
                $stmt = $db->prepare("UPDATE users SET username=?, slug=?, full_name=?, title=?, email=?, phone=?, address=?, summary=?, photo=?, role=?, is_default=? WHERE id=?");
                $stmt->bind_param("ssssssssssii", $username, $slug, $full_name, $title, $email, $phone, $address, $summary, $photo_path, $role, $is_default, $user_id);
            }

            if ($stmt->execute()) {
                $message = "Success: Profil Pengguna berhasil diperbarui!";
            } else {
                $message = "Error: Gagal memperbarui data - " . $stmt->error;
            }
        } else {
            // Insert new user
            $hash = password_hash($password ?: 'userpass', PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, slug, full_name, title, email, phone, address, summary, photo, password, role, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssi", $username, $slug, $full_name, $title, $email, $phone, $address, $summary, $photo_path, $hash, $role, $is_default);

            if ($stmt->execute()) {
                $message = "Success: Pengguna baru berhasil ditambahkan! Slug CV: " . $slug;
            } else {
                $message = "Error: Gagal menambah pengguna - " . $stmt->error;
            }
        }
    } elseif ($action === 'delete_user') {
        $del_id = (int)$_POST['user_id'];
        if ($del_id === 1) {
            $message = "Error: Administrator utama tidak boleh dihapus.";
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Success: Pengguna berhasil dihapus beserta seluruh data CV terkait.";
            } else {
                $message = "Error: Gagal menghapus pengguna.";
            }
        }
    }
}

// Check edit request
if (isset($_GET['edit_id']) && $db) {
    $edit_id = (int)$_GET['edit_id'];
    $res = $db->query("SELECT * FROM users WHERE id = {$edit_id}");
    if ($res) {
        $edit_user = $res->fetch_assoc();
    }
}

// Fetch all users
$all_users = [];
if ($db) {
    $res = $db->query("SELECT * FROM users ORDER BY id ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $all_users[] = $r;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen User Profiles</h3>
    <?php if ($edit_user): ?>
        <a href="dashboard.php?page=users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah User Baru</a>
    <?php endif; ?>
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
            <div class="card-header bg-primary text-white fw-bold py-3">
                <i class="bi bi-person-gear me-1"></i> <?= $edit_user ? 'Edit Profil User (ID: ' . $edit_user['id'] . ')' : 'Tambah User Baru' ?>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_user">
                    <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?? 0 ?>">

                    <!-- Profile Photo Field -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Foto Profil (Gambar CV)</label>
                        <?php if (!empty($edit_user['photo'])): ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="../<?= htmlspecialchars($edit_user['photo']) ?>" alt="Photo" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                                <small class="text-muted">Foto saat ini</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                        <small class="text-muted" style="font-size: 0.75rem;">Format: JPG, PNG, WEBP. Ukuran disarankan rasio 1:1.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" name="full_name" required value="<?= htmlspecialchars($edit_user['full_name'] ?? '') ?>" placeholder="Masukkan nama lengkap Anda">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" name="username" required value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>" placeholder="Masukkan username Anda">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select">
                                <option value="user" <?= ($edit_user['role'] ?? '') === 'user' ? 'selected' : '' ?>>User Biasa</option>
                                <option value="admin" <?= ($edit_user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Profesi / Job Title</label>
                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($edit_user['title'] ?? '') ?>" placeholder="Masukkan profesi / job title Anda">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" placeholder="Masukkan email Anda">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. HP / WA</label>
                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($edit_user['phone'] ?? '') ?>" placeholder="Masukkan nomor telepon / HP Anda">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($edit_user['address'] ?? '') ?>" placeholder="Masukkan kota / alamat Anda">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ringkasan Profil (Bio / Summary)</label>
                        <textarea class="form-control" name="summary" rows="3" placeholder="Tuliskan deskripsi ringkasan profil Anda..."><?= htmlspecialchars($edit_user['summary'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <?= $edit_user ? '<small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>' : '' ?></label>
                        <input type="password" class="form-control" name="password" <?= $edit_user ? '' : 'required' ?> placeholder="••••••••">
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default_check" <?= ($edit_user['is_default'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="is_default_check">Jadikan Tampilan CV Default System</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> <?= $edit_user ? 'Simpan Perubahan' : 'Tambah User baru' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: List Table -->
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-table me-1"></i> Daftar User Terdaftar
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Foto</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>URL CV Slug</th>
                                <th>Default</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_users)): ?>
                                <?php foreach ($all_users as $u): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($u['photo']) && file_exists(__DIR__ . '/../../' . $u['photo'])): ?>
                                                <img src="../<?= htmlspecialchars($u['photo']) ?>" alt="Avatar" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($u['full_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>"><?= strtoupper($u['role']) ?></span>
                                        </td>
                                        <td>
                                            <a href="../<?= htmlspecialchars($u['slug']) ?>" target="_blank" class="badge bg-info text-dark text-decoration-none">
                                                /<?= htmlspecialchars($u['slug']) ?> <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <?= $u['is_default'] ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>DEFAULT</span>' : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="dashboard.php?page=users&edit_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <?php if ($u['id'] !== 1): ?>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini dan semua data CV-nya?');">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data user.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>