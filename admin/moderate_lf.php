<?php
// SHS-17: Admin moderates lost and found posts
session_start();
require_once '../db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

// ── Handle POST actions (approve / reject / delete) ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['post_id'])) {
    $post_id = (int) $_POST['post_id'];
    $action  = $_POST['action'];

    if ($action === 'delete') {
        $del = $conn->prepare("DELETE FROM lost_found WHERE id = ?");
        $del->bind_param("i", $post_id);
        $del->execute();
        header("Location: moderate_lf.php?msg=deleted");
        exit();
    }

    if (in_array($action, ['approve', 'reject'])) {
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE lost_found SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $post_id);
        $stmt->execute();
        header("Location: moderate_lf.php?msg=" . $action . "d");
        exit();
    }
}

// ── Fetch filter ─────────────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'all';
$allowed = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($filter, $allowed)) $filter = 'all';

$where_clause = ($filter !== 'all') ? "WHERE lf.status = '$filter'" : '';

$sql = "SELECT lf.*, u.name AS student_name
        FROM   lost_found lf
        JOIN   users u ON lf.user_id = u.id
        $where_clause
        ORDER  BY lf.created_at DESC";
$result = $conn->query($sql);

// ── Stat counts ───────────────────────────────────────────────────────────────
$counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$total_res = $conn->query("SELECT status, COUNT(*) AS cnt FROM lost_found GROUP BY status");
while ($s = $total_res->fetch_assoc()) {
    $counts[$s['status']] = (int) $s['cnt'];
    $counts['all'] += (int) $s['cnt'];
}

// ── Flash message ─────────────────────────────────────────────────────────────
$msg_map = [
    'approved' => ['✅ Post approved successfully.', 'success'],
    'rejected' => ['❌ Post rejected.', 'warning'],
    'deleted'  => ['🗑️ Post deleted.', 'danger'],
];
$flash = isset($_GET['msg'], $msg_map[$_GET['msg']]) ? $msg_map[$_GET['msg']] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderate Lost &amp; Found – School Helpdesk</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* ── Layout ── */
        body { display: flex; min-height: 100vh; background: #f0f2f5; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }

        /* ── Page header ── */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }
        .page-header h1 { font-size: 1.6rem; color: #1a1a2e; margin: 0; border: none; padding: 0; }
        .page-header .subtitle { color: #6c757d; font-size: 0.9rem; margin-top: 4px; }

        /* ── Flash ── */
        .flash { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .flash.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .flash.warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .flash.danger  { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        /* ── Stat cards ── */
        .stat-cards { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
        .stat-card {
            flex: 1; min-width: 130px; background: #fff; border-radius: 12px;
            padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,.07);
            display: flex; flex-direction: column; gap: 6px;
            cursor: pointer; transition: box-shadow .2s;
            border-bottom: 4px solid transparent;
            text-decoration: none; color: inherit;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); text-decoration: none; }
        .stat-card.active { border-bottom-color: #007bff; background: #f0f7ff; }
        .stat-card .count { font-size: 2rem; font-weight: 700; }
        .stat-card .label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; }
        .stat-card.all     .count { color: #007bff; }
        .stat-card.pending .count { color: #fd7e14; }
        .stat-card.approved .count { color: #28a745; }
        .stat-card.rejected .count { color: #dc3545; }

        /* ── Filter bar ── */
        .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .filter-btn {
            padding: 6px 16px; border-radius: 20px; border: 1.5px solid #dee2e6;
            background: #fff; color: #495057; font-size: 0.85rem; cursor: pointer;
            transition: all .2s; text-decoration: none;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #007bff; color: #fff; border-color: #007bff; text-decoration: none;
        }

        /* ── Posts grid ── */
        .posts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
        .post-card {
            background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.07);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .post-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px 10px; border-bottom: 1px solid #f0f0f0;
        }
        .post-type-badge {
            font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .badge-lost  { background: #fff0e6; color: #e04d00; }
        .badge-found { background: #e6f9ee; color: #1a7a3c; }

        .post-status-badge {
            font-size: 0.72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px;
            text-transform: capitalize;
        }
        .status-pending  { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .post-card-body { padding: 14px 18px; flex: 1; }
        .post-card-body h3 { margin: 0 0 8px; font-size: 1rem; color: #1a1a2e; }
        .post-card-body p  { font-size: 0.875rem; color: #555; margin-bottom: 8px; line-height: 1.5; }
        .post-meta { font-size: 0.78rem; color: #999; display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .post-meta span { display: flex; align-items: center; gap: 4px; }

        .post-card-actions {
            padding: 12px 18px; border-top: 1px solid #f0f0f0;
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .btn { padding: 6px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.82rem; font-weight: 600; transition: opacity .2s; }
        .btn:hover { opacity: .85; }
        .btn-approve { background: #28a745; color: #fff; }
        .btn-reject  { background: #ffc107; color: #333; }
        .btn-delete  { background: #dc3545; color: #fff; }
        .btn-disabled { background: #e9ecef; color: #aaa; cursor: default; }

        .empty-state { text-align: center; padding: 60px 20px; color: #999; }
        .empty-state .icon { font-size: 3rem; margin-bottom: 10px; }

        /* ── Confirm dialog ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45);
            z-index: 999; align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff; border-radius: 14px; padding: 28px 32px;
            max-width: 380px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,.18);
            text-align: center;
        }
        .modal-box h3 { margin-bottom: 10px; font-size: 1.1rem; }
        .modal-box p  { font-size: 0.9rem; color: #666; margin-bottom: 22px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }

        @media (max-width: 600px) {
            .main-content { padding: 16px; }
            .posts-grid   { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php
// Inject sidebar (sets $role from session key 'role'; bridge for 'user_role' key)
$_SESSION['role'] = $_SESSION['user_role'] ?? 'admin';
$_SESSION['name'] = $_SESSION['name'] ?? $_SESSION['user_email'] ?? 'Admin';
include '../includes/sidebar.php';
?>

<div class="main-content">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <h1>🛡️ Moderate Lost &amp; Found Posts</h1>
            <p class="subtitle">Review, approve, or reject posts submitted by students.</p>
        </div>
    </div>

    <!-- Flash message -->
    <?php if ($flash): ?>
        <div class="flash <?= $flash[1] ?>"><?= $flash[0] ?></div>
    <?php endif; ?>

    <!-- Stat cards (clickable filters) -->
    <div class="stat-cards">
        <?php foreach (['all' => '📋 All', 'pending' => '⏳ Pending', 'approved' => '✅ Approved', 'rejected' => '❌ Rejected'] as $key => $label): ?>
        <a href="?filter=<?= $key ?>" class="stat-card <?= $key ?> <?= $filter === $key ? 'active' : '' ?>">
            <span class="count"><?= $counts[$key] ?></span>
            <span class="label"><?= $label ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Posts grid -->
    <?php if ($result && $result->num_rows > 0): ?>
    <div class="posts-grid">
        <?php while ($post = $result->fetch_assoc()): ?>
        <div class="post-card">
            <div class="post-card-header">
                <span class="post-type-badge badge-<?= $post['type'] ?>">
                    <?= $post['type'] === 'lost' ? '🔍 Lost' : '✅ Found' ?>
                </span>
                <span class="post-status-badge status-<?= $post['status'] ?>">
                    <?= ucfirst($post['status']) ?>
                </span>
            </div>

            <div class="post-card-body">
                <h3><?= htmlspecialchars($post['item_name']) ?></h3>
                <p><?= nl2br(htmlspecialchars(substr($post['description'], 0, 180))) ?>
                   <?= strlen($post['description']) > 180 ? '…' : '' ?>
                </p>
                <?php if (!empty($post['location'])): ?>
                <p>📍 <strong>Location:</strong> <?= htmlspecialchars($post['location']) ?></p>
                <?php endif; ?>
                <div class="post-meta">
                    <span>👤 <?= htmlspecialchars($post['student_name']) ?></span>
                    <span>🕒 <?= date('d M Y, h:i A', strtotime($post['created_at'])) ?></span>
                </div>
            </div>

            <div class="post-card-actions">
                <?php if ($post['status'] === 'pending'): ?>
                    <!-- Approve -->
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve">✅ Approve</button>
                    </form>
                    <!-- Reject -->
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-reject">❌ Reject</button>
                    </form>
                <?php elseif ($post['status'] === 'approved'): ?>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-reject">❌ Revoke Approval</button>
                    </form>
                <?php elseif ($post['status'] === 'rejected'): ?>
                    <form method="POST" style="margin:0">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve">✅ Re-approve</button>
                    </form>
                <?php endif; ?>
                <!-- Delete (any status) -->
                <button class="btn btn-delete" onclick="confirmDelete(<?= $post['id'] ?>)">🗑️ Delete</button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="empty-state">
        <div class="icon">📭</div>
        <p>No posts found<?= $filter !== 'all' ? " with status <strong>$filter</strong>" : '' ?>.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Delete confirmation modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3>🗑️ Delete Post?</h3>
        <p>This action is permanent and cannot be undone.</p>
        <div class="modal-actions">
            <form method="POST" id="deleteForm" style="margin:0">
                <input type="hidden" name="post_id" id="deletePostId">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-delete">Yes, Delete</button>
            </form>
            <button class="btn btn-disabled" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deletePostId').value = id;
    document.getElementById('deleteModal').classList.add('open');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
