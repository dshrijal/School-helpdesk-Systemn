<?php
// Start the session to access session variables
session_start();

// Include database connection file
require_once 'db.php';

// Check if user is NOT logged in OR user is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    // Redirect to login page if not authorized
    header("Location: login.html");
    exit(); // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <!-- Make website responsive on all devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page title -->
    <title>Admin Dashboard - School Helpdesk</title>
    
    <!-- Link external CSS file -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="dashboard-container">
        
        <!-- Main heading -->
        <h1>Admin Dashboard</h1>
        
        <!-- Display logged-in admin email -->
        <p>Welcome, Admin <?php echo $_SESSION['user_email']; ?></p>

<!-- Success message after login -->
        <?php if(isset($_GET['success'])) : ?>
    <div class="success-message">
        <?php echo $_GET['success']; ?>
    </div>
<?php endif; ?>
        
      <?php include 'includes/sidebar.php'; ?>
        
        <!-- Section to display recent queries -->
        <section>
            <h2>Recent Queries</h2>
            
            <!-- TODO: Add PHP code here to fetch and display queries from database -->
            <!-- Example: SELECT * FROM queries ORDER BY created_at DESC LIMIT 5 -->
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