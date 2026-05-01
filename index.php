<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ./admin/admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'instructor') {
        header("Location: ./instructor/instructor_dashboard.php");
    } else {
        header("Location: ./student/student_dashboard.php");
    }
    exit();
}

header("Location: ./auth/login.php");
exit();
?>