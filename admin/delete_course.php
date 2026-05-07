<?php
session_start();
require_once "../db.php";
require_once "../models/Course.php";
if (isset($_GET['id'])) {
    try {
        $course_obj = new Course($pdo);
        $is_deleted=$course_obj->delete($_GET['id']);
        if(!$is_deleted)
        {
            throw new Exception("Failed to delete");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
header("Location: ./courses.php");
exit();
