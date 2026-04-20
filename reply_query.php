<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$query_id = $_GET['id'];

$sql = "SELECT * FROM queries WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $query_id);
$stmt->execute();
$result = $stmt->get_result();
$query = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reply = $_POST['reply'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE queries SET reply = ?, status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $reply, $status, $query_id);
    
    if ($update_stmt->execute()) {
        echo "Reply sent successfully!";
        header("Location: admin_queries.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply Query - School Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="reply-query-container">
        <h1>Reply to Query</h1>
        
        <section class="query-details">
            <h3><?php echo $query['title']; ?></h3>
            <p><strong>Category:</strong> <?php echo $query['category']; ?></p>
            <p><strong>Description:</strong> <?php echo $query['description']; ?></p>
            <p><strong>Status:</strong> <?php echo $query['status']; ?></p>
        </section>
        
        <form action="reply_query.php?id=<?php echo $query_id; ?>" method="POST">
            <textarea name="reply" placeholder="Write your reply" required></textarea>
            <select name="status" required>
                <option value="open" <?php if ($query['status'] == 'open') echo 'selected'; ?>>Open</option>
                <option value="in_progress" <?php if ($query['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
                <option value="resolved" <?php if ($query['status'] == 'resolved') echo 'selected'; ?>>Resolved</option>
            </select>
            <button type="submit">Send Reply</button>
        </form>
        
        <a href="admin_queries.php">Back to Queries</a>
    </div>
</body>
</html>