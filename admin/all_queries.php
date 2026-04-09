<?php
// admin/all_queries.php - SHS-9
require_once '../config/db.php';
requireAdmin();

$filter = sanitize($conn, $_GET['status'] ?? '');
$sql = "SELECT q.*, u.name as student_name FROM queries q JOIN users u ON q.student_id=u.id";
if ($filter) $sql .= " WHERE q.status='$filter'";
$sql .= " ORDER BY q.created_at DESC";
$queries = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Queries - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">All Queries</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div><h2>All Student Queries</h2><p>Manage and respond to all help requests</p></div>
            </div>

            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                <?php foreach(['', 'open', 'in_progress', 'resolved', 'closed'] as $s): ?>
                    <a href="?status=<?= $s ?>"
                       class="btn btn-sm <?= $filter === $s ? 'btn-primary' : 'btn-secondary' ?>"
                       style="width:auto;">
                        <?= $s ? ucwords(str_replace('_', ' ', $s)) : 'All' ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="table-wrap">
                    <?php if ($queries->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i=1; while ($row = $queries->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><span class="badge badge-<?= $row['status'] ?>"><?= str_replace('_', ' ', $row['status']) ?></span></td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td><a href="reply_query.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Reply</a></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state"><div class="icon">📭</div><p>No queries found.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>