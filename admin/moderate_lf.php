<?php
// admin/moderate_lf.php - SHS-17
require_once '../config/db.php';
requireAdmin();

$success = ''; $error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['id'] ?? 0);
    $action = sanitize($conn, $_POST['action'] ?? '');

    if ($id && in_array($action, ['resolved', 'removed', 'active'])) {
        $stmt = $conn->prepare("UPDATE lost_found SET status=? WHERE id=?");
        $stmt->bind_param("si", $action, $id);
        $success = $stmt->execute() ? "Item marked as $action." : "Action failed.";
        $stmt->close();
    }
}

$filter = sanitize($conn, $_GET['status'] ?? 'active');
$sql = "SELECT lf.*, u.name as poster FROM lost_found lf JOIN users u ON lf.user_id=u.id";
if ($filter !== 'all') $sql .= " WHERE lf.status='$filter'";
$sql .= " ORDER BY lf.created_at DESC";
$items = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Lost & Found - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Moderate Lost & Found</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div><h2>Lost & Found Moderation</h2><p>Review and manage all lost/found posts</p></div>
            </div>

            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                <?php foreach(['active', 'resolved', 'removed', 'all'] as $s): ?>
                    <a href="?status=<?= $s ?>"
                       class="btn btn-sm <?= $filter === $s ? 'btn-primary' : 'btn-secondary' ?>"
                       style="width:auto;"><?= ucfirst($s) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <?php if ($items->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Location</th>
                                <th>Posted By</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-<?= $item['type'] ?>"><?= $item['type'] ?></span></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= htmlspecialchars($item['location']) ?></td>
                                <td><?= htmlspecialchars($item['poster']) ?></td>
                                <td><span class="badge badge-<?= $item['status'] ?>"><?= $item['status'] ?></span></td>
                                <td><?= date('M d', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div style="display:flex;gap:4px;">
                                        <?php if ($item['status'] !== 'resolved'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="action" value="resolved">
                                            <button type="submit" class="btn btn-success btn-sm" data-confirm="Mark as resolved?">✓</button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($item['status'] !== 'removed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="action" value="removed">
                                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Remove this post?">✕</button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($item['status'] === 'removed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="action" value="active">
                                            <button type="submit" class="btn btn-warning btn-sm">↺</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state"><div class="icon">📌</div><p>No items found.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>