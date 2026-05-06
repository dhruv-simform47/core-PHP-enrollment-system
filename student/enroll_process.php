<?php
session_start();
require_once "../db.php";
require_once "../uuid_generator.php"; // Ensure this has generateUUIDv4()

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$student_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];

try {
    $enroll_obj = new Enrollment($pdo);
    $result = $enroll_obj->enroll($student_id, $course_id);
    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}






