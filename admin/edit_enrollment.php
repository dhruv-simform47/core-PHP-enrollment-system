<?php
session_start();
require_once "../db.php";
require_once "../models/Enrollment.php";

$id = $_GET['id'] ?? '';
$enroll_obj = new Enrollment($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $enroll_id = $_POST['enroll_id'];
    $status = $_POST['status'];
    
    // Admin update (no student_id check required)
    $result = $enroll_obj->updateStatus($enroll_id, $status);
    echo json_encode($result);
    exit();
}

$enroll = $enroll_obj->getById($id);
if (!$enroll) { header("Location: enrollments.php"); exit(); }

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Manage Enrollment</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <form id="editEnrollForm">
                        <input type="hidden" name="enroll_id" value="<?php echo htmlspecialchars($enroll['id']); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Student</label>
                            <p class="form-control-plaintext border-bottom"><?php echo htmlspecialchars($enroll['user_name']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Course</label>
                            <p class="form-control-plaintext border-bottom"><?php echo htmlspecialchars($enroll['course_name']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enrollment Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo $enroll['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="cancelled" <?php echo $enroll['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $enroll['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                        <a href="enrollments.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#editEnrollForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'edit_enrollment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') { 
                alert('Status Updated successfully');
                window.location.href='enrollments.php'; 
            } else {
                alert('Error: ' + res.message);
            }
        }
    });
});
</script>
<?php require_once "./includes/footer.php"; ?>