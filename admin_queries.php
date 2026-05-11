<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$sql = "SELECT q.id, q.title, q.description, q.category, q.status, u.name, q.created_at FROM queries q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Queries - School Helpdesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-queries-container">
        <h1>All Student Queries</h1>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Student</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['title'] . "</td>";
                    echo "<td>" . $row['category'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . $row['created_at'] . "</td>";
                    echo "<td><a href='reply_query.php?id=" . $row['id'] . "'>Reply</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No queries found</td></tr>";
            }
            ?>
        </table>
        
        <a href="dashboard_admin.php">Back to Dashboard</a>
    </div>
</body>
</html>