<?php
// includes/sidebar.php
$role = $_SESSION['role'] ?? 'student';
$name = $_SESSION['name'] ?? 'User';
$initials = strtoupper(substr($name, 0, 1));
$base = ($role === 'admin') ? '../admin/' : '../student/';
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🏫</div>
        <div>
            <h2>School Help</h2>
            <span>Helpdesk System</span>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= $initials ?></div>
        <div class="user-info">
            <p><?= htmlspecialchars($name) ?></p>
            <span><?= $role ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'student'): ?>
        <div class="nav-section">
            <div class="nav-section-label">Main</div>
            <a href="../student/dashboard.php" class="nav-item <?= $current === 'dashboard.php' ? 'active' : '' ?>">
                <span class="icon">📊</span> Dashboard
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Help Queries</div>
            <a href="../student/submit_query.php" class="nav-item <?= $current === 'submit_query.php' ? 'active' : '' ?>">
                <span class="icon">✉️</span> Submit Query
            </a>
            <a href="../student/my_queries.php" class="nav-item <?= $current === 'my_queries.php' ? 'active' : '' ?>">
                <span class="icon">📋</span> My Queries
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Lost & Found</div>
            <a href="../student/lost_item.php" class="nav-item <?= $current === 'lost_item.php' ? 'active' : '' ?>">
                <span class="icon">🔍</span> Post Lost Item
            </a>
            <a href="../student/found_item.php" class="nav-item <?= $current === 'found_item.php' ? 'active' : '' ?>">
                <span class="icon">✅</span> Post Found Item
            </a>
            <a href="../student/lost_found_list.php" class="nav-item <?= $current === 'lost_found_list.php' ? 'active' : '' ?>">
                <span class="icon">📌</span> Lost & Found List
            </a>
        </div>
        <?php else: ?>
        <div class="nav-section">
            <div class="nav-section-label">Main</div>
            <a href="../admin/dashboard.php" class="nav-item <?= $current === 'dashboard.php' ? 'active' : '' ?>">
                <span class="icon">📊</span> Dashboard
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Queries</div>
            <a href="../admin/all_queries.php" class="nav-item <?= $current === 'all_queries.php' ? 'active' : '' ?>">
                <span class="icon">📬</span> All Queries
            </a>
            <a href="../admin/calendar.php" class="nav-item <?= $current === 'calendar.php' ? 'active' : '' ?>">
                <span class="icon">📅</span> Calendar View
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Lost & Found</div>
            <a href="../admin/moderate_lf.php" class="nav-item <?= $current === 'moderate_lf.php' ? 'active' : '' ?>">
                <span class="icon">🛡️</span> Moderate Posts
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">
            <span class="icon">🚪</span> Logout
        </a>
    </div>
</div>
<div id="overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:99;" onclick="document.getElementById('sidebar').classList.remove('open');this.style.display='none';"></div>