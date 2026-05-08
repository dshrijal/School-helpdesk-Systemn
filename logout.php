<?php
session_start();
session_destroy();

header("Location: login.html?message=Logged out successfully");
exit();
?>