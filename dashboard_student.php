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
        <!-- Display welcome message -->
        <p>Welcome, <?php echo $_SESSION['user_email']; ?></p>


<!-- Success message after login -->
<?php if(isset($_GET['success'])) : ?>
    <div class="success-message">
        <?php echo $_GET['success']; ?>
    </div>
<?php endif; ?>
       
<?php include 'includes/sidebar.php'; ?>
        
        <section>
            <h2>Your Queries</h2>
            <!-- Display student queries here -->
        </section>
    </div>
    <!-- Auto hide success/error messages after 3 seconds -->
<script>
setTimeout(() => {
    const messages = document.querySelectorAll('.success-message, .error-message');

    messages.forEach(message => {
        message.style.display = 'none';
    });
}, 3000);
</script>
</body>
</html>
