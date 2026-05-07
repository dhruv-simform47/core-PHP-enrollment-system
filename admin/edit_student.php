<?php
session_start();
require_once "../db.php";
require_once "../models/Student.php";

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $name = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $status = isset($_POST['is_verified']) ? 1 : 0;

    try {
        $stu_obj=new Student($pdo);
        $is_updated=$stu_obj->edit($name, $email, $status, $id);
        if(!$is_updated)
        {
            throw new Exception("Error in update");
        }
        echo json_encode(['status' => 'success']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

$student =(new Student($pdo))->getById($id);

if (!$student) { header("Location: ./students.php"); exit(); }

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Student</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <form id="editStudentForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="user_name" value="<?php echo htmlspecialchars($student['user_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_verified" id="is_verified" <?php echo $student['is_verified'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_verified">Verified Account</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Student</button>
                        <a href="./students.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#editStudentForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'edit_student.php?id=<?php echo $id; ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') { alert('Updated!'); window.location.href='students.php'; }
            else { alert('Error: ' + res.message); }
        }
    });
});
</script>
<?php require_once "./includes/footer.php"; ?>