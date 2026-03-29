<?php
$conn = new mysqli("localhost", "root", "", "helpdesk");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>