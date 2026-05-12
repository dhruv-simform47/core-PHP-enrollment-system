<?php
header('Content-Type: application/json');
session_start();

// Security: Check if user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

require_once "../db.php";
require_once "../models/Admin.php";
require_once "../uuid_generator.php";
require_once "../auth/mail.php";

$admin_obj = new Admin($pdo);

try {
    $action = $_POST['action'] ?? '';

    // --- ACTION: LIST (For DataTables) ---
    if ($action === 'list') {
        $draw = (int)($_POST['draw'] ?? 1);
        // Fetch all admins
        $all_admins = $admin_obj->getAll();
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => count($all_admins),
            "recordsFiltered" => count($all_admins),
            "data" => $all_admins
        ]);
        exit;
    }

    // --- ACTION: ADD ---
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) throw new Exception("All fields are required.");
        if ($password !== $confirm_password) throw new Exception("Passwords do not match.");

        $uuid = generateUUIDv4();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if (!$admin_obj->add($uuid, $name, $email, $hash)) throw new Exception("Failed to insert admin.");

        // Send Email
        $mailer = new Emailnotification();
        $mailer->compose($email, "Enrollment Account credentials", "Hello $name,\n\nYour account password is: <h3>$password</h3>");

        echo json_encode(['status' => 'success', 'message' => 'Admin added successfully!']);
    }

    // --- ACTION: EDIT ---
    elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = isset($_POST['is_verified']) ? 1 : 0;

        if (empty($id) || empty($name)) throw new Exception("Required data missing.");

        if (!$admin_obj->edit($name, $email, $status, $id)) throw new Exception("Failed to update admin.");
        echo json_encode(['status' => 'success', 'message' => 'Admin updated!']);
    }

    // --- ACTION: DELETE ---
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) throw new Exception("ID missing.");
        
        if (!$admin_obj->delete($id)) throw new Exception("Failed to delete admin.");
        echo json_encode(['status' => 'success', 'message' => 'Admin deleted!']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit();