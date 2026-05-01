<?php
session_start();
require_once "../db.php";
$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['status' => 'success']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

$stmt = $pdo->prepare("SELECT e.*, s.user_name, c.course_name 
                       FROM enrollments e 
                       JOIN users s ON e.student_id = s.uuid 
                       JOIN courses c ON e.course_id = c.id 
                       WHERE e.id = ?");
$stmt->execute([$id]);
$enroll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enroll) { header("Location: enrollments.php"); exit(); }

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Manage Enrollment</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student</label>
                        <input type="text" class="form-control-plaintext border-bottom" value="<?php echo htmlspecialchars($enroll['user_name']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Course</label>
                        <input type="text" class="form-control-plaintext border-bottom" value="<?php echo htmlspecialchars($enroll['course_name']); ?>" readonly>
                    </div>
                    <form id="editEnrollForm">
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
        url: 'edit_enrollment.php?id=<?php echo $id; ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') { 
                alert('Status Updated successfully');
                window.location.href='enrollments.php'; 
            }
        }
    });
});
</script>
<?php require_once "./includes/footer.php"; ?>