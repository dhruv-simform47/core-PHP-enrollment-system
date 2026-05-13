<?php
header('Content-Type: application/json');
session_start();
// Security: Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}
require_once "../db.php";
require_once "../models/Course.php";
require_once "../models/Instructor.php";
require_once "../uuid_generator.php";

$course_obj = new Course($pdo);
$inst_obj = new Instructor($pdo);

try {
    $action = $_POST['action'] ?? '';

    // ACTION: LIST (For DataTables)
    if ($action === 'list') {
        $draw = (int)$_POST['draw'];
        $start = (int)$_POST['start'];
        $length = (int)$_POST['length'];
        $search = $_POST['search']['value'] ?? '';

        // You would ideally update your model to handle LIMIT and LIKE
        $data = $course_obj->getFullDetails(); 
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    } 
    // ACTION: GET INSTRUCTORS (For the dropdown)
    elseif ($action === 'get_instructors') {
        echo json_encode($inst_obj->getAll());
    }
    // ACTION: ADD / EDIT
    elseif ($action === 'add' || $action === 'edit') {
        $course_name = trim($_POST['course_name']);
        $instructor_id = $_POST['instructor_id'];
        $duration = (int)$_POST['duration_weeks'];
        $max_seats = (int)$_POST['max_seats'];
        $description = $_POST['description'] ?? '';

        if ($action === 'add') {
            $id = generateUUIDv4();
            $course_obj->add($id, $course_name, $description, $instructor_id, $duration, $max_seats);
        } else {
            $course_obj->edit($course_name, $description, $instructor_id, $duration, $max_seats, $_POST['id']);
        }
        echo json_encode(['status' => 'success']);
    }
    // ACTION: DELETE
    elseif ($action === 'delete') {
        $course_obj->delete($_POST['id']);
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}