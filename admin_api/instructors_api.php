<?php
header('Content-Type: application/json');
session_start();
// Security: Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}
require_once "../db.php";
require_once "../models/Instructor.php";
require_once "../uuid_generator.php";
require_once "../auth/mail.php";

$inst_obj = new Instructor($pdo);

try {
    $action = $_POST['action'] ?? '';

    // ACTION: LIST (DataTables fetch)
    if ($action === 'list') {
        $draw = (int)$_POST['draw'];
        // For full server-side, you'd process start, length, search here
        $data = $inst_obj->getAll(); 
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
    } 
    // ACTION: ADD
    elseif ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) throw new Exception("All fields are required.");

        $uuid = generateUUIDv4();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if (!$inst_obj->add($uuid, $name, $email, $hash)) throw new Exception("Database insertion failed.");

        $mailer = new Emailnotification();
        $mailer->compose($email, "Instructor Credentials", "Hello $name,\n\nYour password is: <h3>$password</h3>");

        echo json_encode(['status' => 'success']);
    } 
    // ACTION: EDIT
    elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = trim($_POST['user_name']);
        $email = trim($_POST['email']);
        $status = isset($_POST['is_verified']) ? 1 : 0;

        if (!$inst_obj->edit($name, $email, $status, $id)) throw new Exception("Update failed.");
        echo json_encode(['status' => 'success']);
    } 
    // ACTION: DELETE
    elseif ($action === 'delete') {
        if (!$inst_obj->delete($_POST['id'])) throw new Exception("Delete failed.");
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}