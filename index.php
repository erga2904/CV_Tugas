<?php
/**
 * Front Controller & Router for the CV Application.
 * Handles clean URLs for Admin, User Login, User CVs, and Default CV.
 */

require_once __DIR__ . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Extract and normalize requested path
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base directory prefix if present
if (!empty($script_dir) && $script_dir !== '/' && strpos($request_uri, $script_dir) === 0) {
    $request_uri = substr($request_uri, strlen($script_dir));
}

$request_path = trim($request_uri, '/');
$request_path = urldecode($request_path);

/**
 * Route incoming request to appropriate controller/view
 */
function handleRouting($path, $db) {
    $lower_path = strtolower($path);

    // 1. Admin Dashboard Access (http://localhost/project_cv/Admin or http://localhost/project_cv/admin)
    if ($lower_path === 'admin' || $lower_path === 'admin/dashboard.php' || $lower_path === 'admin/index.php') {
        require_once __DIR__ . '/admin/dashboard.php';
        return;
    }

    // 2. Login Page Access (http://localhost/project_cv/login.php or http://localhost/project_cv/login)
    if ($lower_path === 'login.php' || $lower_path === 'login') {
        require_once __DIR__ . '/login.php';
        return;
    }

    // 3. Logout Access
    if ($lower_path === 'logout.php' || $lower_path === 'logout') {
        require_once __DIR__ . '/logout.php';
        return;
    }

    // 4. Register Access
    if ($lower_path === 'register.php' || $lower_path === 'register') {
        require_once __DIR__ . '/register.php';
        return;
    }

    // 5. User Panel Access (for regular logged-in users)
    if ($lower_path === 'user' || $lower_path === 'user/dashboard.php') {
        require_once __DIR__ . '/user/dashboard.php';
        return;
    }

    // 6. Default CV View (http://localhost/project_cv or http://localhost/project_cv/)
    if (empty($path) || $lower_path === 'index.php') {
        $slug = null;
        if ($db) {
            $stmt = $db->prepare("SELECT slug FROM users WHERE is_default = 1 LIMIT 1");
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $slug = $row['slug'];
                }
            }
            if (!$slug) {
                $res_fallback = $db->query("SELECT slug FROM users WHERE role != 'admin' ORDER BY id ASC LIMIT 1");
                if ($res_fallback && $row_fb = $res_fallback->fetch_assoc()) {
                    $slug = $row_fb['slug'];
                }
            }
        }
        
        if (!$slug) {
            $slug = 'erga_refaldy'; // Default user
        }
        
        require_once __DIR__ . '/includes/user_view_template.php';
        return;
    }

    // 7. Specific User CV Display (e.g. http://localhost/project_cv/erga_refaldy or http://localhost/project_cv/cecep_suwanda)
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $path)) {
        $slug = strtolower($path);
        // Alias legacy test slug to erga_refaldy if requested
        if ($slug === 'cecep_suwanda') {
            $slug = 'erga_refaldy';
        }
        require_once __DIR__ . '/includes/user_view_template.php';
        return;
    }

    // 404 Not Found Page
    http_response_code(404);
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>404 Page Not Found</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light d-flex align-items-center justify-content-center vh-100'>
        <div class='text-center p-5 bg-white rounded shadow-sm' style='max-width: 500px;'>
            <h1 class='display-1 fw-bold text-danger'>404</h1>
            <h4 class='mb-3'>Halaman Tidak Ditemukan</h4>
            <p class='text-muted mb-4'>Halaman CV <strong>'" . htmlspecialchars($path) . "'</strong> yang Anda cari tidak tersedia.</p>
            <a href='" . htmlspecialchars($script_dir ?: '/') . "' class='btn btn-primary px-4'>Kembali ke CV Utama</a>
        </div>
    </body>
    </html>";
}

handleRouting($request_path, $db);
?>