<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
$loggedInName = $_SESSION['user_name'] ?? 'student';

?>