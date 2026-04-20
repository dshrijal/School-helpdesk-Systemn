<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, Admin <?php echo $_SESSION['user_email']; ?></p>
        
        <nav>
            <ul>
                <li><a href="admin_queries.php">View All Queries</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <section>
            <h2>Recent Queries</h2>
            <!-- Display all queries here -->
        </section>
    </div>
</body>
</html>