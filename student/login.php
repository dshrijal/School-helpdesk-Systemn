<?php
// login.php - Student/Admin Login Page (SHS-4)
require_once 'config/db.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit();
}

$error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        }
        $stmt->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Helpdesk System</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="card">
        <div class="logo">
            <div class="icon">🎓</div>
            <h1>Helpdesk</h1>
            <p>Login to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message" style="color: #e53e3e; background: #fed7d7; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="you@example.com" 
                    required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required
                >
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p style="text-align: center; margin-top: 16px; font-size: 0.9rem; color: #718096;">
            Don't have an account? <a href="index.php?tab=register" style="color: #3182ce; text-decoration: none;">Register here</a>
        </p>
    </div>
</body>
</html>
