<?php
session_start();
require_once "../db.php";

// Security: Ensure user is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? '';
$student_id = $_SESSION['user_id'];

// Handle the AJAX POST Request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $new_status = $_POST['status'];

    // Validation: Students should only be allowed to set 'cancelled'
    // or keep it 'active'. They cannot mark it 'completed'.
    if (!in_array($new_status, ['active', 'cancelled'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status selection.']);
        exit();
    }

    try {
        // Ensure the enrollment belongs to the logged-in student for security
        $enroll_obj=new Enrollment($pdo);
        $result=$enroll_obj->updateStatus($id,$student_id,$new_status);
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        exit();
    }
}

// Fetch existing enrollment details for the form
$enroll_obj=new Enrollment($pdo);

$enroll =$enroll_obj->getCourseDetail($id,$student_id);

if (!$enroll) {
    header("Location: student_dashboard.php");
    exit();
}

require_once "./layout/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Update Enrollment Status</h1>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Course:</label>
                        <p class="form-control-plaintext border-bottom"><?php echo htmlspecialchars($enroll['course_name']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instructor:</label>
                        <p class="form-control-plaintext border-bottom"><?php echo htmlspecialchars($enroll['instructor_name']); ?></p>
                    </div>

                    <form id="updateStatusForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo $enroll['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="cancelled" <?php echo $enroll['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled / Drop Course</option>
                                <?php if($enroll['status'] == 'completed'): ?>
                                    <option value="completed" selected disabled>Completed</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text text-muted">Note: Marking a course as cancelled will remove you from the active roster.</div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'edit_enrollment.php?id=<?php echo $id; ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    alert(res.message);
                    window.location.href = 'student_dashboard.php';
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function() {
                alert('An error occurred on the server.');
            }
        });
    });
});
</script>

<?php require_once "./layout/footer.php"; ?>