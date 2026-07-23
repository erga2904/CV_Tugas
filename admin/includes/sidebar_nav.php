<?php
/**
 * Sidebar Navigation for Admin Panel - Clean Inter Font Theme
 */

$current_page = $_GET['page'] ?? 'dashboard';
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;
?>
<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse p-3 text-white min-vh-100">
    <div class="position-sticky pt-2">
        <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-50">
            <i class="bi bi-shield-lock text-primary fs-4 me-2"></i>
            <div>
                <h6 class="mb-0 text-white fw-bold" style="letter-spacing: -0.02em;">Admin Control</h6>
                <small class="text-secondary" style="font-size: 0.75rem;">CV Project</small>
            </div>
        </div>

        <ul class="nav nav-pills flex-column mb-auto" style="font-size: 0.9rem;">
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($current_page === 'dashboard') ? 'active bg-primary' : 'text-white-50'; ?>" href="dashboard.php?page=dashboard">
                    <i class="bi bi-grid me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($current_page === 'users') ? 'active bg-primary' : 'text-white-50'; ?>" href="dashboard.php?page=users">
                    <i class="bi bi-people me-2"></i> User Profiles
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($current_page === 'experiences') ? 'active bg-primary' : 'text-white-50'; ?>" href="dashboard.php?page=experiences">
                    <i class="bi bi-briefcase me-2"></i> Experience
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($current_page === 'education') ? 'active bg-primary' : 'text-white-50'; ?>" href="dashboard.php?page=education">
                    <i class="bi bi-journal-text me-2"></i> Education
                </a>
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link text-white <?= ($current_page === 'skills') ? 'active bg-primary' : 'text-white-50'; ?>" href="dashboard.php?page=skills">
                    <i class="bi bi-tools me-2"></i> Skills
                </a>
            </li>
        </ul>

        <hr class="my-4 border-secondary border-opacity-50">

        <div class="d-flex flex-column gap-2">
            <a href="<?= htmlspecialchars($base_url ?: '/') ?>" target="_blank" class="btn btn-outline-light btn-sm text-start" style="font-size: 0.8rem;">
                <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Public Site
            </a>
            <a href="../logout.php" class="btn btn-danger btn-sm text-start" style="font-size: 0.8rem;">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</div>