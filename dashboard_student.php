<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - School Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Student Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['user_email']; ?></p>
        
        <nav>
            <ul>
                <li><a href="submit_query.php">Submit Query</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <section>
            <h2>Your Queries</h2>
            <!-- Display student queries here -->
        </section>
    </div>
</body>
</html>
