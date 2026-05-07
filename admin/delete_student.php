<?php
session_start();
require_once "../db.php";
require_once "../models/Student.php";


if (isset($_GET['id'])) {
    try {
         $is_deleted=(new Student($pdo))->delete($_GET['id']);
        if(!$is_deleted)
        {
            throw new Exception("Failed to delete");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
header("Location: ./students.php");
exit();