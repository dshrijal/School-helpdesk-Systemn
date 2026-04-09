<?php
// student/submit_query.php - SHS-6
require_once '../config/db.php';
requireStudent();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = sanitize($conn, $_POST['title'] ?? '');
    $category = sanitize($conn, $_POST['category'] ?? 'General');
    $desc     = sanitize($conn, $_POST['description'] ?? '');
    $uid      = $_SESSION['user_id'];

    if (!$title || !$desc) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO queries (student_id, title, category, description) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $uid, $title, $category, $desc);
        if ($stmt->execute()) {
            $success = 'Query submitted successfully!';
        } else {
            $error = 'Failed to submit query.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Query - School Helpdesk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Submit Query</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h2>Submit a Help Query</h2>
                    <p>Describe your issue and we'll get back to you</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="card" style="max-width:600px;">
                <div class="card-header"><h3>New Query</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Query Title *</label>
                            <input type="text" name="title" placeholder="Brief title of your issue" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="General">General</option>
                                <option value="Academic">Academic</option>
                                <option value="Technical">Technical</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Library">Library</option>
                                <option value="Hostel">Hostel</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" placeholder="Describe your issue in detail..." required></textarea>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:auto;">Submit Query</button>
                            <a href="my_queries.php" class="btn btn-secondary btn-sm">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>