<?php
// student/lost_item.php - SHS-14
require_once '../config/db.php';
requireStudent();

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item     = sanitize($conn, $_POST['item_name'] ?? '');
    $desc     = sanitize($conn, $_POST['description'] ?? '');
    $location = sanitize($conn, $_POST['location'] ?? '');
    $contact  = sanitize($conn, $_POST['contact'] ?? '');
    $uid      = $_SESSION['user_id'];

    if (!$item) {
        $error = 'Item name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO lost_found (user_id, type, item_name, description, location, contact) VALUES (?, 'lost', ?, ?, ?, ?)");
        $stmt->bind_param("issss", $uid, $item, $desc, $location, $contact);
        $success = $stmt->execute() ? 'Lost item posted successfully!' : 'Failed to post.';
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Lost Item - School Helpdesk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Post Lost Item</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div><h2>Post a Lost Item</h2><p>Report something you've lost at school</p></div>
            </div>

            <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

            <div class="card" style="max-width:600px;">
                <div class="card-header"><h3>🔍 Lost Item Report</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Item Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Blue backpack, Calculator" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" placeholder="Describe the item (color, size, brand, etc.)"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Last Seen Location</label>
                            <input type="text" name="location" placeholder="e.g. Library, Canteen, Classroom B2">
                        </div>
                        <div class="form-group">
                            <label>Contact Info</label>
                            <input type="text" name="contact" placeholder="Phone or email to reach you">
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="btn btn-danger btn-sm" style="width:auto;">Post Lost Item</button>
                            <a href="lost_found_list.php" class="btn btn-secondary btn-sm">View List</a>
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