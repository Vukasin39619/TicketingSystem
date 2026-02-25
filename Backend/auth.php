<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function requireRole(array $allowedRoles) {
    if (!isset($_SESSION['userid']) || !isset($_SESSION['userrole'])) {
        header("Location: ../login.php");
        exit;
    }
    if (!in_array($_SESSION['userrole'], $allowedRoles, true)) {
        // 403 Forbidden ili redirect
        http_response_code(403);
        echo "Access denied.";
        exit;
    }
}
