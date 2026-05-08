<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar">
    <ul>
        <li>
            <a href="dashboard_admin.php"
               class="<?php echo ($current_page == 'dashboard_admin.php') ? 'active' : ''; ?>">
               Dashboard
            </a>
        </li>

        <li>
            <a href="admin_queries.php"
               class="<?php echo ($current_page == 'admin_queries.php') ? 'active' : ''; ?>">
               View Queries
            </a>
        </li>

        <li>
            <a href="submit_query.php"
               class="<?php echo ($current_page == 'submit_query.php') ? 'active' : ''; ?>">
               Submit Query
            </a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>
    </ul>
</nav>