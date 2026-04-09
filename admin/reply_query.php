<?php
// admin/reply_query.php - SHS-10
require_once '../config/db.php';
requireAdmin();

$qid = intval($_GET['id'] ?? 0);
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT q.*, u.name as student_name, u.email as student_email FROM queries q JOIN users u ON q.student_id=u.id WHERE q.id=?");
$stmt->bind_param("i", $qid);
$stmt->execute();
$query = $stmt->get_result()->fetch_assoc();
if (!$query) { header("Location: all_queries.php"); exit(); }
$stmt->close();

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message    = sanitize($conn, $_POST['message'] ?? '');
    $new_status = sanitize($conn, $_POST['status'] ?? '');

    if (!$message) {
        $error = 'Reply message is required.';
    } else {
        // Insert reply
        $rs = $conn->prepare("INSERT INTO query_replies (query_id, user_id, message) VALUES (?,?,?)");
        $rs->bind_param("iis", $qid, $admin_id, $message);
        $rs->execute();
        $rs->close();

        // Update status
        if ($new_status) {
            $us = $conn->prepare("UPDATE queries SET status=? WHERE id=?");
            $us->bind_param("si", $new_status, $qid);
            $us->execute();
            $us->close();
            $query['status'] = $new_status;
        }

        $success = 'Reply sent successfully!';
    }
}

$replies = $conn->query("SELECT r.*, u.name, u.role FROM query_replies r JOIN users u ON r.user_id=u.id WHERE r.query_id=$qid ORDER BY r.created_at ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Query - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Reply to Query</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h2><?= htmlspecialchars($query['title']) ?></h2>
                    <p>From: <?= htmlspecialchars($query['student_name']) ?></p>
                </div>
                <a href="all_queries.php" class="btn btn-secondary btn-sm">← Back</a>
            </div>

            <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">
                <!-- Query + replies -->
                <div class="card">
                    <div class="card-body">
                        <div class="query-meta">
                            <div class="meta-item">
                                <span class="meta-label">Status</span>
                                <span><span class="badge badge-<?= $query['status'] ?>"><?= str_replace('_', ' ', $query['status']) ?></span></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Category</span>
                                <span class="meta-value"><?= htmlspecialchars($query['category']) ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Date</span>
                                <span class="meta-value"><?= date('M d, Y', strtotime($query['created_at'])) ?></span>
                            </div>
                        </div>

                        <p style="font-size:14px;line-height:1.7;color:#374151;margin-bottom:20px;"><?= nl2br(htmlspecialchars($query['description'])) ?></p>

                        <div class="replies-section">
                            <h4>Replies</h4>
                            <?php if ($replies->num_rows > 0): ?>
                                <?php while ($r = $replies->fetch_assoc()): ?>
                                <div class="reply-bubble <?= $r['role'] ?>">
                                    <div class="reply-author"><?= $r['role'] === 'admin' ? '🛡️ Admin' : '👤 ' . htmlspecialchars($r['name']) ?></div>
                                    <p><?= nl2br(htmlspecialchars($r['message'])) ?></p>
                                    <div class="reply-time"><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="color:#9CA3AF;font-size:13px;">No replies yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Reply form -->
                <div class="card">
                    <div class="card-header"><h3>Send Reply</h3></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Update Status</label>
                                <select name="status">
                                    <option value="">-- Keep current --</option>
                                    <option value="open" <?= $query['status']==='open' ? 'selected' : '' ?>>Open</option>
                                    <option value="in_progress" <?= $query['status']==='in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= $query['status']==='resolved' ? 'selected' : '' ?>>Resolved</option>
                                    <option value="closed" <?= $query['status']==='closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Reply Message *</label>
                                <textarea name="message" placeholder="Type your reply..." required style="min-height:140px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">Send Reply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>