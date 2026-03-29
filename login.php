<?php
include "db.php";

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

$sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    if ($role == "student") {
        header("Location: dashboard_student.php");
    } else {
        header("Location: dashboard_admin.php");
    }

} else {
    echo "Invalid login!";
}
?>