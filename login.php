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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>School Helpdesk — Login</title>
  <link rel="stylesheet" href="css/login.css" />
</head>
<body>

<div class="card">

  <div class="logo">
    <div class="icon">🏫</div>
    <h1>School Helpdesk</h1>
    <p>Sign in to continue</p>
  </div>

  <!-- Role tabs (plain HTML form buttons) -->
  <form method="POST" action="login.php" class="tab-form">
    <input type="hidden" name="email"    value="<?= htmlspecialchars($_POST['email']    ?? '') ?>">
    <input type="hidden" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>">
    <input type="hidden" name="admin_id" value="<?= htmlspecialchars($_POST['admin_id'] ?? '') ?>">
    <div class="tabs">
      <button type="submit" name="role" value="student" class="tab <?= $role === 'student' ? 'active' : '' ?>">🎓 Student</button>
      <button type="submit" name="role" value="admin"   class="tab <?= $role === 'admin'   ? 'active' : '' ?>">🛡 Admin</button>
    </div>
  </form>

  <!-- Error / success messages -->
  <?php if ($error):   ?><div class="msg error">  <?= htmlspecialchars($error)   ?></div><?php endif; ?>
  <?php if ($success): ?><div class="msg success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <!-- Login form -->
  <form method="POST" action="login.php">
    <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

    <?php if ($role === 'admin'): ?>
    <div class="field">
      <label for="admin_id">Admin ID</label>
      <input type="text" id="admin_id" name="admin_id"
             value="<?= htmlspecialchars($_POST['admin_id'] ?? '') ?>"
             placeholder="ADM-0042" />
    </div>
    <?php endif; ?>

    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="you@school.edu" />
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password"
             placeholder="••••••••" />
    </div>

    <div class="extras">
      <label class="remember">
        <input type="checkbox" name="remember"
               <?= isset($_POST['remember']) ? 'checked' : '' ?> />
        Remember me
      </label>
      <a href="forgot-password.php" class="forgot">Forgot password?</a>
    </div>

    <button type="submit" class="btn">Sign In</button>
  </form>

  <p class="footer-note">
    New student? <a href="register.php">Register here</a>
  </p>

</div>

</body>
</html>