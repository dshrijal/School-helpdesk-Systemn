<?php
// student/lost_found_list.php - SHS-16
require_once '../config/db.php';
requireStudent();

$filter = sanitize($conn, $_GET['type'] ?? '');
$sql = "SELECT lf.*, u.name as poster FROM lost_found lf JOIN users u ON lf.user_id=u.id WHERE lf.status='active'";
if ($filter) $sql .= " AND lf.type='$filter'";
$sql .= " ORDER BY lf.created_at DESC";
$items = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found - School Helpdesk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Lost & Found</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div><h2>Lost & Found Board</h2><p>Browse reported lost and found items</p></div>
                <div style="display:flex;gap:8px;">
                    <a href="lost_item.php" class="btn btn-danger btn-sm">+ Lost</a>
                    <a href="found_item.php" class="btn btn-success btn-sm">+ Found</a>
                </div>
            </div>

            <!-- Filter -->
            <div style="display:flex;gap:8px;margin-bottom:20px;">
                <a href="?" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-secondary' ?>" style="width:auto;">All</a>
                <a href="?type=lost" class="btn btn-sm <?= $filter==='lost' ? 'btn-primary' : 'btn-secondary' ?>" style="width:auto;">🔍 Lost Items</a>
                <a href="?type=found" class="btn btn-sm <?= $filter==='found' ? 'btn-primary' : 'btn-secondary' ?>" style="width:auto;">✅ Found Items</a>
            </div>

            <?php if ($items->num_rows > 0): ?>
            <div class="lf-grid">
                <?php while ($item = $items->fetch_assoc()): ?>
                <div class="lf-card">
                    <div class="lf-type <?= $item['type'] ?>">
                        <?= $item['type'] === 'lost' ? '🔍 LOST' : '✅ FOUND' ?>
                    </div>
                    <h4><?= htmlspecialchars($item['item_name']) ?></h4>
                    <?php if ($item['description']): ?>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['location']): ?>
                        <p>📍 <?= htmlspecialchars($item['location']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['contact']): ?>
                        <p>📞 <?= htmlspecialchars($item['contact']) ?></p>
                    <?php endif; ?>
                    <div class="lf-footer">
                        <span>By <?= htmlspecialchars($item['poster']) ?></span>
                        <span><?= date('M d', strtotime($item['created_at'])) ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="icon">📌</div>
                        <p>No items posted yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>