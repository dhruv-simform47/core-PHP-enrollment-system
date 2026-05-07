<?php
session_start();
require_once "../db.php";
require_once "../models/Admin.php";

if (isset($_GET['id'])) {
    try {
        $admin_obj=new Admin($pdo);
        $is_deleted=$admin_obj->delete($_GET['id']);
        if(!$is_deleted)
        {
             throw new Exception("Failed to delete admin.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
header("Location: ./admins.php");
exit();