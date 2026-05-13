<?php
header('Content-Type: application/json');
session_start();

// Basic Auth Check
// Security: Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

// Fixed Paths: Only one level up (../)
require_once "../db.php";
require_once "../models/Admin.php";
require_once "../models/Student.php";
require_once "../models/Course.php";
require_once "../models/Instructor.php";

try {
    $response = [
        "status" => "success",
        "data" => [
            "students"    => (new Student($pdo))->getCount(),
            "instructors" => (new Instructor($pdo))->getCount(),
            "courses"     => (new Course($pdo))->getCount(),
            "admins"      => (new Admin($pdo))->getCount()
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Server error: " . $e->getMessage()
    ]);
}