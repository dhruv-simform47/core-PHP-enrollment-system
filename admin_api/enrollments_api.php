<?php
header('Content-Type: application/json');
session_start();
// Security: Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}
require_once "../db.php";
require_once "../models/Enrollment.php";
require_once "../models/Student.php";
require_once "../models/Course.php";

$enroll_obj = new Enrollment($pdo);
$student_obj = new Student($pdo);
$course_obj = new Course($pdo);

try {
    $action = $_POST['action'] ?? '';

    // ACTION: LIST (For DataTables)
    if ($action === 'list') {
        $draw = (int)$_POST['draw'];
        // In a full server-side setup, you'd pass $_POST['start'], $_POST['length'], and $_POST['search'] to the model
        $data = $enroll_obj->getAllEnrollments(); 
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    } 
    // ACTION: GET DEPENDENCIES (For Dropdowns)
    elseif ($action === 'get_dependencies') {
        echo json_encode([
            'students' => $student_obj->getAll(),
            'courses' => $course_obj->getAll()
        ]);
    }
    // ACTION: ADD
    elseif ($action === 'add') {
        $result = $enroll_obj->enroll($_POST['student_id'], $_POST['course_id']);
        if ($result['status'] === 'error') throw new Exception($result['message']);
        echo json_encode(['status' => 'success']);
    }
    // ACTION: EDIT (Status Update)
    elseif ($action === 'edit') {
        $result = $enroll_obj->updateStatus($_POST['id'], $_POST['status']);
        if ($result['status'] === 'error') throw new Exception($result['message']);
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}