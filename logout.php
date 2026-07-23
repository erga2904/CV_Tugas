<?php
/**
 * Logout Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = ($script_dir === '/' || $script_dir === '\\') ? '' : $script_dir;

header("Location: " . $base_url . "/login.php?msg=" . urlencode("Anda telah berhasil keluar dari akun."));
exit();
?>
