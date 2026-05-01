<?php
session_start();
if (isset($_GET['id'])) {
    try {
       require_once "../db.php";
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
header("Location: ./courses.php");
exit();
?>