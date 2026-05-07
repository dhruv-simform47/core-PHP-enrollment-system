<?php
session_start();
require_once "../db.php";
require_once "../models/Enrollment.php";

$instructor_id = $_SESSION['user_id'];
$enroll_id = $_GET['enroll_id'] ?? '';
$enroll_model = new Enrollment($pdo);

// Fetch details securely
$data = $enroll_model->getEnrollmentForInstructor($enroll_id, $instructor_id);

if (!$data) { 
    header("Location: instructor_dashboard.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    // Use the flexible updateStatus method from our Enrollment model
    $result = $enroll_model->updateStatus($enroll_id, $new_status);
    
    if($result['status'] === 'success') {
        header("Location: view_students.php?course_id=" . $data['course_id']);
        exit();
    }
}

require_once "./layout/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Update Student Progress</h1>
            <div class="card col-md-6 shadow-sm">
                <div class="card-body">
                    <h5>Student: <?php echo htmlspecialchars($data['user_name']); ?></h5>
                    <p class="text-muted">Course: <?php echo htmlspecialchars($data['course_name']); ?></p>
                    <hr>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enrollment Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo $data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo $data['status'] == 'completed' ? 'selected' : ''; ?>>Completed / Passed</option>
                                <option value="cancelled" <?php echo $data['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled / Failed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Save Status</button>
                        <a href="view_students.php?course_id=<?php echo $data['course_id']; ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
<?php require_once "./layout/footer.php"; ?>