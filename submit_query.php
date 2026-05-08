<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
$sql = "INSERT INTO queries (student_id, title, description, category, status, created_at) 
VALUES (?, ?, ?, ?, 'open', NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param("isss", $user_id, $title, $description, $category);
    if ($stmt->execute()) {
    $success = "Query submitted successfully!";
} else {
    $error = "Failed to submit query!";
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Query - School Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="submit-query-container">
        <h1>Submit Query</h1>
        <form action="submit_query.php" method="POST">
            <input type="text" name="title" placeholder="Query Title" required>
            <textarea name="description" placeholder="Describe your query" required></textarea>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="academic">Academic</option>
                <option value="technical">Technical</option>
                <option value="administrative">Administrative</option>
            </select>
            <button type="submit">Submit Query</button>
            
            <?php if(isset($success)) : ?>
    <div class="success-message">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if(isset($error)) : ?>
    <div class="error-message">
        <?php echo $error; ?>
    </div>
<?php endif; ?>
        </form>
        <a href="dashboard_student.php">Back to Dashboard</a>
    </div>
</body>
</html>
