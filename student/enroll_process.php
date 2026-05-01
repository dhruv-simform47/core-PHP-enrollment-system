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
    // 1. Check if already enrolled
    $checkQuery = "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status != 'cancelled'";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$student_id, $course_id]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'You are already enrolled in this course.']);
        exit;
    }

    // 2. Check seat availability
    $seatQuery = "SELECT max_seats, 
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status != 'cancelled') as current_enrolls 
                  FROM courses WHERE id = ?";
    $seatStmt = $pdo->prepare($seatQuery);
    $seatStmt->execute([$course_id, $course_id]);
    $courseData = $seatStmt->fetch(PDO::FETCH_ASSOC);

    if ($courseData['current_enrolls'] >= $courseData['max_seats']) {
        echo json_encode(['status' => 'error', 'message' => 'Sorry, this course is now full.']);
        exit;
    }

    // 3. Perform Enrollment
    $enrollId = generateUUIDv4();
    $insertQuery = "INSERT INTO enrollments (id, student_id, course_id, status) VALUES (?, ?, ?, 'active')";
    $insertStmt = $pdo->prepare($insertQuery);
    
    if ($insertStmt->execute([$enrollId, $student_id, $course_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Successfully enrolled!']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}