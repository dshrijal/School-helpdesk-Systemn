<?php
// admin/calendar.php - SHS-13
require_once '../config/db.php';
requireAdmin();

$month = intval($_GET['month'] ?? date('m'));
$year  = intval($_GET['year']  ?? date('Y'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$first_day  = mktime(0, 0, 0, $month, 1, $year);
$days_month = date('t', $first_day);
$start_dow  = date('w', $first_day); // 0=Sun

// Get queries per day
$sql = "SELECT DATE(created_at) as d, COUNT(*) as c FROM queries WHERE MONTH(created_at)=$month AND YEAR(created_at)=$year GROUP BY DATE(created_at)";
$qres = $conn->query($sql);
$query_days = [];
while ($row = $qres->fetch_assoc()) {
    $query_days[intval(date('j', strtotime($row['d'])))] = $row['c'];
}

$prev_m = $month - 1; $prev_y = $year;
if ($prev_m < 1) { $prev_m = 12; $prev_y--; }
$next_m = $month + 1; $next_y = $year;
if ($next_m > 12) { $next_m = 1; $next_y++; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-menu-btn" id="menuBtn">☰</button>
                <span class="topbar-title">Calendar View</span>
            </div>
        </div>
        <div class="page-content">
            <div class="page-header">
                <div><h2>Calendar View</h2><p>Queries submitted per day</p></div>
            </div>

            <div class="card" style="max-width:700px;">
                <div class="card-header">
                    <a href="?month=<?= $prev_m ?>&year=<?= $prev_y ?>" class="btn btn-secondary btn-sm">← Prev</a>
                    <h3><?= date('F Y', $first_day) ?></h3>
                    <a href="?month=<?= $next_m ?>&year=<?= $next_y ?>" class="btn btn-secondary btn-sm">Next →</a>
                </div>
                <div class="card-body">
                    <div class="cal-header">
                        <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                            <div class="cal-day-name"><?= $d ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="calendar-grid">
                        <?php
                        // Empty cells
                        for ($i = 0; $i < $start_dow; $i++) {
                            echo '<div class="cal-day empty"></div>';
                        }
                        $today = date('j'); $today_m = date('m'); $today_y = date('Y');
                        for ($d = 1; $d <= $days_month; $d++) {
                            $is_today = ($d == $today && $month == $today_m && $year == $today_y);
                            $cnt = $query_days[$d] ?? 0;
                            $cls = 'cal-day';
                            if ($is_today) $cls .= ' today';
                            if ($cnt > 0)  $cls .= ' has-queries';
                            echo "<div class='$cls' title='$cnt queries'>";
                            echo "<span>$d</span>";
                            if ($cnt > 0) echo "<span class='dot' title='$cnt queries'></span>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <div style="margin-top:16px;display:flex;gap:16px;font-size:12px;color:#6B7280;">
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;border-radius:2px;background:var(--primary);display:inline-block;"></span>
                            Today
                        </span>
                        <span style="display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:var(--warning);display:inline-block;"></span>
                            Has Queries
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary table -->
            <div class="card" style="max-width:700px;margin-top:20px;">
                <div class="card-header"><h3>Daily Summary - <?= date('F Y', $first_day) ?></h3></div>
                <div class="table-wrap">
                    <?php if (!empty($query_days)): ?>
                    <table>
                        <thead><tr><th>Date</th><th>Queries Submitted</th></tr></thead>
                        <tbody>
                        <?php foreach ($query_days as $day => $count): ?>
                            <tr>
                                <td><?= date('M d, Y', mktime(0,0,0,$month,$day,$year)) ?></td>
                                <td><span class="badge badge-open"><?= $count ?> quer<?= $count > 1 ? 'ies' : 'y' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state"><div class="icon">📅</div><p>No queries this month.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>