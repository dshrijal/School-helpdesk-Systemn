<?php
// admin/dashboard.php - SHS-12
session_start();

require_once '../config/db.php';
requireAdmin();

$total_q   = $conn->query("SELECT COUNT(*) as c FROM queries")->fetch_assoc()['c'];
$open_q    = $conn->query("SELECT COUNT(*) as c FROM queries WHERE status='open'")->fetch_assoc()['c'];
$resolved  = $conn->query("SELECT COUNT(*) as c FROM queries WHERE status='resolved'")->fetch_assoc()['c'];
$students  = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
$lf_active = $conn->query("SELECT COUNT(*) as c FROM lost_found WHERE status='active'")->fetch_assoc()['c'];
$in_prog   = $conn->query("SELECT COUNT(*) as c FROM queries WHERE status='in_progress'")->fetch_assoc()['c'];

$recent = $conn->query("SELECT q.*, u.name as student_name FROM queries q JOIN users u ON q.student_id=u.id ORDER BY q.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Helpdesk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Admin Dashboard</span>
            </div>
            <div class="topbar-right">
                <span style="font-size:13px;color:#9CA3AF;"><?= date('D, M d Y') ?></span>
            </div>
        </div>

        <div class="page-content">
            <div class="page-header">
                <div>
                    <h2>Admin Dashboard</h2>
                    <p>Overview of the school helpdesk system</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">📬</div>
                    <div class="stat-info"><p><?= $total_q ?></p><span>Total Queries</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">🔴</div>
                    <div class="stat-info"><p><?= $open_q ?></p><span>Open Queries</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">⏳</div>
                    <div class="stat-info"><p><?= $in_prog ?></p><span>In Progress</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">✅</div>
                    <div class="stat-info"><p><?= $resolved ?></p><span>Resolved</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">👥</div>
                    <div class="stat-info"><p><?= $students ?></p><span>Students</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">📌</div>
                    <div class="stat-info"><p><?= $lf_active ?></p><span>L&F Active</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Recent Queries</h3>
                    <a href="all_queries.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <div class="table-wrap">
                    <?php if ($recent->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $recent->fetch_assoc()): ?>
                            <tr>
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
                        <div class="empty-state"><div class="icon">📭</div><p>No queries yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>