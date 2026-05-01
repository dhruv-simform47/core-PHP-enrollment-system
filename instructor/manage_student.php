<?php
session_start();
require_once "../db.php";

$instructor_id = $_SESSION['user_id'];
$enroll_id = $_GET['enroll_id'] ?? '';


$query = "SELECT e.*, u.user_name, c.course_name 
          FROM enrollments e
          JOIN users u ON e.student_id = u.uuid
          JOIN courses c ON e.course_id = c.id
          WHERE e.id = ? AND c.instructor_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$enroll_id, $instructor_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) { header("Location: instructor_dashboard.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    $update = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
    if($update->execute([$new_status, $enroll_id])) {
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