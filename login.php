<?php
session_start();
require_once 'includes/db.php';   // your PDO connection
require_once 'includes/auth.php'; // helper functions

$error   = '';
$success = '';
$role    = isset($_POST['role']) ? $_POST['role'] : 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role']     ?? 'student');
    $admin_id = trim($_POST['admin_id'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';

    } elseif ($role === 'admin' && empty($admin_id)) {
        $error = 'Admin ID is required.';

    } else {
        // Fetch user from DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {

            // Extra admin ID check
            if ($role === 'admin' && $user['admin_id'] !== $admin_id) {
                $error = 'Invalid Admin ID.';
            } else {
                // Start session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'];

                // Redirect based on role
                if ($role === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: student/dashboard.php');
                }
                exit;
            }

        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
