<?php
session_start();
require_once "../db.php";
require_once "../models/Enrollment.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? '';
$student_id = $_SESSION['user_id'];
$enroll_obj = new Enrollment($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $status = $_POST['status'];
    $enroll_id = $_POST['enroll_id'];

    if (!in_array($status, ['active', 'cancelled'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status selection.']);
        exit();
    }

    // Student update (Pass student_id to verify ownership)
    $result = $enroll_obj->updateStatus($enroll_id, $status, $student_id);
    echo json_encode($result);
    exit();
}

$enroll = $enroll_obj->getById($id);
// Verify ownership before showing form
if (!$enroll || $enroll['student_id'] !== $student_id) {
    header("Location: student_dashboard.php");
    exit();
}

require_once "./layout/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Update My Enrollment</h1>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form id="updateStatusForm">
                        <input type="hidden" name="enroll_id" value="<?php echo $id; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Course:</label>
                            <p class="form-control-plaintext border-bottom"><?php echo htmlspecialchars($enroll['course_name']); ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo $enroll['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="cancelled" <?php echo $enroll['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled / Drop Course</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="student_dashboard.php" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#updateStatusForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'edit_enrollment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                alert('Updated successfully');
                window.location.href = 'student_dashboard.php';
            } else {
                alert(res.message);
            }
        }
    });
});
</script>
<?php require_once "./layout/footer.php"; ?>