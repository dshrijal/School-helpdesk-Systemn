<?php
// student/query_detail.php - SHS-8
require_once '../config/db.php';
requireStudent();
$uid = $_SESSION['user_id'];
$qid = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT q.*, u.name as student_name FROM queries q JOIN users u ON q.student_id=u.id WHERE q.id=? AND q.student_id=?");
$stmt->bind_param("ii", $qid, $uid);
$stmt->execute();
$query = $stmt->get_result()->fetch_assoc();
if (!$query) { header("Location: my_queries.php"); exit(); }
$stmt->close();

$replies = $conn->query("SELECT r.*, u.name, u.role FROM query_replies r JOIN users u ON r.user_id=u.id WHERE r.query_id=$qid ORDER BY r.created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Detail - School Helpdesk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Query Details</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h2><?= htmlspecialchars($query['title']) ?></h2>
                    <p>Query #<?= $query['id'] ?></p>
                </div>
                <a href="my_queries.php" class="btn btn-secondary btn-sm">← Back</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="query-meta">
                        <div class="meta-item">
                            <span class="meta-label">Status</span>
                            <span class="meta-value"><span class="badge badge-<?= $query['status'] ?>"><?= str_replace('_', ' ', $query['status']) ?></span></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Category</span>
                            <span class="meta-value"><?= htmlspecialchars($query['category']) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Submitted</span>
                            <span class="meta-value"><?= date('M d, Y H:i', strtotime($query['created_at'])) ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Last Updated</span>
                            <span class="meta-value"><?= date('M d, Y H:i', strtotime($query['updated_at'])) ?></span>
                        </div>
                    </div>

                    <p style="font-size:14px;line-height:1.7;color:#374151;"><?= nl2br(htmlspecialchars($query['description'])) ?></p>

                    <div class="replies-section">
                        <h4>Replies (<?= $replies->num_rows ?>)</h4>
                        <?php if ($replies->num_rows > 0): ?>
                            <?php while ($r = $replies->fetch_assoc()): ?>
                            <div class="reply-bubble <?= $r['role'] ?>">
                                <div class="reply-author">
                                    <?= $r['role'] === 'admin' ? '🛡️ Admin' : '👤 ' . htmlspecialchars($r['name']) ?>
                                </div>
                                <p><?= nl2br(htmlspecialchars($r['message'])) ?></p>
                                <div class="reply-time"><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color:#9CA3AF;font-size:13px;">No replies yet. Admin will respond soon.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>