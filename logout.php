<?php
// logout.php - SHS-5
require_once 'config/db.php';
session_destroy();
header("Location: index.php?msg=logged_out");
exit();
?>