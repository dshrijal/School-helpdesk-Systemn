<?php
// index.php - Login & Registration Page (SHS-4, SHS-3, SHS-18)
require_once 'config/db.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit();
}

$error = '';
$success = '';
$tab = $_GET['tab'] ?? 'login';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'login') {
        $email = sanitize($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email && $password) {
            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
                exit();
            } else {
                $error = 'Invalid email or password.';
                $tab = 'login';
            }
            $stmt->close();
        } else {
            $error = 'Please fill in all fields.';
            $tab = 'login';
        }
    }

    if ($_POST['action'] === 'register') {
        $name = sanitize($conn, $_POST['name'] ?? '');
        $email = sanitize($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $tab = 'register';

        if (!$name || !$email || !$password || !$confirm) {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $ins->bind_param("sss", $name, $email, $hash);
                if ($ins->execute()) {
                    $success = 'Registration successful! Please login.';
                    $tab = 'login';
                } else {
                    $error = 'Registration failed. Try again.';
                }
                $ins->close();
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Helpdesk - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">🏫</div>
            <h1>School Helpdesk</h1>
            <p>Your one-stop support system</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="auth-tabs">
            <button class="auth-tab <?= $tab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Login</button>
            <button class="auth-tab <?= $tab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Register</button>
        </div>

        <!-- Login Form -->
        <div id="loginForm" style="<?= $tab !== 'login' ? 'display:none' : '' ?>">
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login →</button>
            </form>
            <p style="text-align:center;font-size:12px;color:#9CA3AF;margin-top:14px;">
                Admin: admin@school.com / password
            </p>
        </div>

        <!-- Register Form -->
        <div id="registerForm" style="<?= $tab !== 'register' ? 'display:none' : '' ?>">
            <form method="POST" action="">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account →</button>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
    document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
    document.querySelectorAll('.auth-tab').forEach((el, i) => {
        el.classList.toggle('active', (i === 0 && tab === 'login') || (i === 1 && tab === 'register'));
    });
}
</script>
</body>
</html>