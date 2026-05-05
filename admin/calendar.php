<?php
// SHS-13: Calendar view of queries per day
session_start();
require_once '../db.php';


// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

// ── Determine which month/year to show ───────────────────────────────────────
$today      = new DateTime();
$year       = isset($_GET['year'])  ? (int) $_GET['year']  : (int) $today->format('Y');
$month      = isset($_GET['month']) ? (int) $_GET['month'] : (int) $today->format('m');

// Clamp month
if ($month < 1)  { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$monthStart   = new DateTime("$year-$month-01");
$monthEnd     = (clone $monthStart)->modify('last day of this month');
$prevMonth    = (clone $monthStart)->modify('-1 month');
$nextMonth    = (clone $monthStart)->modify('+1 month');

// ── Fetch query counts per day for this month ─────────────────────────────────
$start_str = $monthStart->format('Y-m-d');
$end_str   = $monthEnd->format('Y-m-d');

$sql = "SELECT DATE(created_at) AS query_date, COUNT(*) AS cnt
        FROM   queries
        WHERE  created_at BETWEEN '$start_str 00:00:00' AND '$end_str 23:59:59'
        GROUP  BY DATE(created_at)";
$res = $conn->query($sql);

$day_counts = []; // day_number => count
while ($row = $res->fetch_assoc()) {
    $day = (int) date('j', strtotime($row['query_date']));
    $day_counts[$day] = (int) $row['cnt'];
}

$max_count = $day_counts ? max($day_counts) : 0;

// ── If a specific day is selected, fetch its queries ─────────────────────────
$selected_day    = isset($_GET['day']) ? (int) $_GET['day'] : null;
$selected_queries = [];

if ($selected_day) {
    $sel_date = sprintf('%04d-%02d-%02d', $year, $month, $selected_day);
    $q_sql = "SELECT q.id, q.title, q.category, q.status, u.name AS student_name, q.created_at
              FROM   queries q
              JOIN   users u ON q.user_id = u.id
              WHERE  DATE(q.created_at) = '$sel_date'
              ORDER  BY q.created_at DESC";
    $q_res = $conn->query($q_sql);
    while ($q = $q_res->fetch_assoc()) {
        $selected_queries[] = $q;
    }
}

// ── Calendar helpers ──────────────────────────────────────────────────────────
$days_in_month  = (int) $monthEnd->format('j');
$first_weekday  = (int) $monthStart->format('w'); // 0=Sun … 6=Sat
$month_name     = $monthStart->format('F');

// ── Monthly summary stats ─────────────────────────────────────────────────────
$total_this_month = array_sum($day_counts);
$busiest_day      = $day_counts ? array_search(max($day_counts), $day_counts) : null;

$status_sql = "SELECT status, COUNT(*) AS cnt FROM queries
               WHERE created_at BETWEEN '$start_str 00:00:00' AND '$end_str 23:59:59'
               GROUP BY status";
$status_res = $conn->query($status_sql);
$status_counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0];
while ($s = $status_res->fetch_assoc()) {
    $status_counts[$s['status']] = (int) $s['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar View – School Helpdesk</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { display: flex; min-height: 100vh; background: #f0f2f5; }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; }

        /* ── Page header ── */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .page-header h1 { font-size: 1.6rem; color: #1a1a2e; margin: 0; border: none; padding: 0; }
        .page-header .subtitle { color: #6c757d; font-size: 0.9rem; margin-top: 4px; }

        /* ── Summary cards ── */
        .summary-cards { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 24px; }
        .sum-card {
            flex: 1; min-width: 130px; background: #fff; border-radius: 12px;
            padding: 16px 18px; box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }
        .sum-card .num { font-size: 1.9rem; font-weight: 700; }
        .sum-card .lbl { font-size: 0.78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; }
        .sum-card.total   .num { color: #007bff; }
        .sum-card.open    .num { color: #fd7e14; }
        .sum-card.progress .num { color: #6f42c1; }
        .sum-card.resolved .num { color: #28a745; }
        .sum-card.busiest  .num { color: #e83e8c; font-size: 1.4rem; }

        /* ── Calendar wrapper ── */
        .calendar-wrap { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.08); overflow: hidden; margin-bottom: 28px; }

        /* Navigation bar */
        .cal-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px; background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
        }
        .cal-nav h2 { font-size: 1.2rem; margin: 0; }
        .nav-btn {
            background: rgba(255,255,255,.2); border: none; color: #fff;
            padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 1rem;
            text-decoration: none; transition: background .2s;
        }
        .nav-btn:hover { background: rgba(255,255,255,.35); text-decoration: none; color: #fff; }

        /* Day-of-week header */
        .cal-header {
            display: grid; grid-template-columns: repeat(7, 1fr);
            background: #f8f9fa; border-bottom: 1px solid #e9ecef;
        }
        .cal-header span {
            text-align: center; padding: 10px 0;
            font-size: 0.75rem; font-weight: 700; color: #6c757d;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .cal-header span:first-child { color: #dc3545; }
        .cal-header span:last-child  { color: #0056b3; }

        /* Grid */
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }

        .cal-cell {
            border: 1px solid #f0f0f0; min-height: 90px; padding: 8px;
            position: relative; cursor: pointer; transition: background .15s;
        }
        .cal-cell:hover { background: #f0f7ff; }
        .cal-cell.empty { background: #fafafa; cursor: default; }
        .cal-cell.today { background: #fff8e1; }
        .cal-cell.selected { background: #e8f4fd; border: 2px solid #007bff; }

        .day-num {
            font-size: 0.85rem; font-weight: 600; color: #333;
            width: 26px; height: 26px; display: flex; align-items: center;
            justify-content: center; border-radius: 50%; margin-bottom: 4px;
        }
        .cal-cell.today .day-num { background: #007bff; color: #fff; }
        .cal-cell.selected .day-num { background: #0056b3; color: #fff; }

        /* Heat bar */
        .heat-bar {
            height: 5px; border-radius: 3px; margin: 4px 0;
            background: #e9ecef; overflow: hidden;
        }
        .heat-fill { height: 100%; border-radius: 3px; transition: width .3s; }

        /* Query count badge */
        .query-badge {
            font-size: 0.72rem; font-weight: 700; color: #fff;
            background: #007bff; border-radius: 10px; padding: 2px 7px;
            display: inline-block; margin-top: 4px;
        }
        .cal-cell.empty .query-badge,
        .no-query { display: none; }

        /* Status mini-dots */
        .status-dots { display: flex; gap: 3px; margin-top: 5px; flex-wrap: wrap; }
        .dot { width: 7px; height: 7px; border-radius: 50%; }
        .dot-open      { background: #fd7e14; }
        .dot-progress  { background: #6f42c1; }
        .dot-resolved  { background: #28a745; }

        /* ── Day detail panel ── */
        .day-detail {
            background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.08);
            padding: 24px; margin-bottom: 20px;
        }
        .day-detail h3 { margin-bottom: 16px; color: #1a1a2e; font-size: 1.1rem; }
        .query-list-item {
            display: flex; align-items: flex-start; gap: 14px; padding: 12px;
            border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 10px;
            background: #fafbfc; transition: box-shadow .15s;
        }
        .query-list-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .query-id { font-size: 0.75rem; color: #aaa; white-space: nowrap; padding-top: 2px; }
        .query-info { flex: 1; }
        .query-info .qtitle { font-weight: 600; color: #1a1a2e; font-size: 0.95rem; }
        .query-info .qmeta  { font-size: 0.8rem; color: #888; margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap; }
        .qstatus {
            font-size: 0.72rem; font-weight: 700; padding: 2px 9px; border-radius: 20px;
            white-space: nowrap; align-self: center;
        }
        .qstatus-open       { background: #fff3cd; color: #856404; }
        .qstatus-in_progress{ background: #e8dffd; color: #4a249e; }
        .qstatus-resolved   { background: #d4edda; color: #155724; }

        .empty-day { text-align: center; color: #aaa; padding: 30px; }

        /* ── Legend ── */
        .legend { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; font-size: 0.8rem; color: #555; margin-top: 10px; }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-box { width: 12px; height: 12px; border-radius: 3px; }
        .lb-heat-low  { background: #cce5ff; }
        .lb-heat-mid  { background: #66b2ff; }
        .lb-heat-high { background: #0056b3; }

        @media (max-width: 650px) {
            .main-content { padding: 14px; }
            .cal-cell { min-height: 56px; padding: 4px; }
            .query-badge { display: none; }
        }
    </style>
</head>
<body>

<?php
$_SESSION['role'] = $_SESSION['user_role'] ?? 'admin';
$_SESSION['name'] = $_SESSION['name'] ?? $_SESSION['user_email'] ?? 'Admin';
include '../includes/sidebar.php';
?>

<div class="main-content">

    <!-- Page header -->
    <div class="page-header">
        <div>
            <h1>📅 Calendar View – Queries per Day</h1>
            <p class="subtitle">Visual overview of query volume across the month.</p>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="summary-cards">
        <div class="sum-card total">
            <div class="num"><?= $total_this_month ?></div>
            <div class="lbl">Total This Month</div>
        </div>
        <div class="sum-card open">
            <div class="num"><?= $status_counts['open'] ?></div>
            <div class="lbl">Open</div>
        </div>
        <div class="sum-card progress">
            <div class="num"><?= $status_counts['in_progress'] ?></div>
            <div class="lbl">In Progress</div>
        </div>
        <div class="sum-card resolved">
            <div class="num"><?= $status_counts['resolved'] ?></div>
            <div class="lbl">Resolved</div>
        </div>
        <div class="sum-card busiest">
            <div class="num"><?= $busiest_day ? $month_name . ' ' . $busiest_day : '–' ?></div>
            <div class="lbl">Busiest Day (<?= $max_count ?> queries)</div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="calendar-wrap">
        <!-- Nav -->
        <div class="cal-nav">
            <a class="nav-btn" href="?year=<?= $prevMonth->format('Y') ?>&month=<?= $prevMonth->format('n') ?>">‹ Prev</a>
            <h2><?= $month_name ?> <?= $year ?></h2>
            <a class="nav-btn" href="?year=<?= $nextMonth->format('Y') ?>&month=<?= $nextMonth->format('n') ?>">Next ›</a>
        </div>

        <!-- Day names -->
        <div class="cal-header">
            <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
                <span><?= $d ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Cells -->
        <div class="cal-grid">
            <!-- Leading empty cells -->
            <?php for ($i = 0; $i < $first_weekday; $i++): ?>
                <div class="cal-cell empty"></div>
            <?php endfor; ?>

            <!-- Day cells -->
            <?php for ($day = 1; $day <= $days_in_month; $day++):
                $count     = $day_counts[$day] ?? 0;
                $is_today  = ($today->format('Y-m-d') === sprintf('%04d-%02d-%02d', $year, $month, $day));
                $is_sel    = ($selected_day === $day);

                // Heat colour based on fraction of max
                if ($max_count > 0 && $count > 0) {
                    $fraction  = $count / $max_count;
                    $intensity = max(40, (int)($fraction * 100));
                    $r = (int)(0   + (0  - 0)   * $fraction);
                    $g = (int)(123 + (86 - 123)  * $fraction);
                    $b = (int)(255 + (179 - 255) * $fraction);
                    $heat_color = "rgb($r,$g,$b)";
                    $heat_width = max(15, (int)($fraction * 100)) . '%';
                } else {
                    $heat_color = '#dee2e6';
                    $heat_width = '0%';
                }

                $cell_url = "?year=$year&month=$month&day=$day";
                if ($is_sel) $cell_url .= ''; // clicking again will deselect — keep same URL

                $classes = 'cal-cell';
                if ($is_today) $classes .= ' today';
                if ($is_sel)   $classes .= ' selected';
            ?>
            <div class="<?= $classes ?>" onclick="window.location='<?= $is_sel ? "?year=$year&month=$month" : $cell_url ?>'">
                <div class="day-num"><?= $day ?></div>

                <?php if ($count > 0): ?>
                <div class="heat-bar">
                    <div class="heat-fill" style="width:<?= $heat_width ?>; background:<?= $heat_color ?>;"></div>
                </div>
                <span class="query-badge"><?= $count ?> <?= $count === 1 ? 'query' : 'queries' ?></span>
                <?php endif; ?>
            </div>
            <?php endfor; ?>

            <!-- Trailing empty cells to complete the last row -->
            <?php
            $total_cells = $first_weekday + $days_in_month;
            $trailing    = (7 - ($total_cells % 7)) % 7;
            for ($i = 0; $i < $trailing; $i++): ?>
                <div class="cal-cell empty"></div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <strong>Heat intensity:</strong>
        <div class="legend-item"><div class="legend-box lb-heat-low"></div> Low</div>
        <div class="legend-item"><div class="legend-box lb-heat-mid"></div> Medium</div>
        <div class="legend-item"><div class="legend-box lb-heat-high"></div> High</div>
        <span style="margin-left:8px">Click any day to see its queries.</span>
    </div>

    <!-- Day detail panel -->
    <?php if ($selected_day): ?>
    <div class="day-detail" style="margin-top:24px">
        <h3>
            📋 Queries on <?= $month_name ?> <?= $selected_day ?>, <?= $year ?>
            <span style="font-size:.85rem; color:#888; font-weight:400; margin-left:8px">
                (<?= count($selected_queries) ?> found)
            </span>
            <a href="?year=<?= $year ?>&month=<?= $month ?>" style="font-size:.8rem; float:right; color:#007bff;">✕ Close</a>
        </h3>

        <?php if (empty($selected_queries)): ?>
            <div class="empty-day">📭 No queries submitted on this day.</div>
        <?php else: ?>
            <?php foreach ($selected_queries as $q): ?>
            <div class="query-list-item">
                <div class="query-id">#<?= $q['id'] ?></div>
                <div class="query-info">
                    <div class="qtitle"><?= htmlspecialchars($q['title']) ?></div>
                    <div class="qmeta">
                        <span>👤 <?= htmlspecialchars($q['student_name']) ?></span>
                        <span>🏷️ <?= ucfirst($q['category']) ?></span>
                        <span>🕒 <?= date('h:i A', strtotime($q['created_at'])) ?></span>
                    </div>
                </div>
                <span class="qstatus qstatus-<?= $q['status'] ?>"><?= ucfirst(str_replace('_', ' ', $q['status'])) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /main-content -->
</body>
</html>
