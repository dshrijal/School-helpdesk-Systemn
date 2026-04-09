<?php
// config/db.php - Database Connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_helpdesk');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../student/dashboard.php");
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}
?>