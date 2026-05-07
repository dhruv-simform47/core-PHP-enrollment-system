<?php
session_start();
require_once "../db.php";
require_once "../models/Enrollment.php";
require_once "../models/Student.php";
require_once "../models/Course.php";



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    if (empty($student_id) || empty($course_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Please select both student and course.']);
        exit();
    }

    try {

        $enroll_obj=new Enrollment($pdo);
        $is_inserted=$enroll_obj->enroll($student_id,$course_id);
        if(!$is_inserted)
        {
            throw new Exception("Failed to Insert");
        }
        echo json_encode(['status' => 'success', 'message' => 'Successfully enrolled!']);
        exit();
    } catch (Exception $e) {    
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}

$students = (new Student($pdo))->getAll();
$courses = (new Course($pdo))->getAll();

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Add New Enrollment</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <form id="addEnrollForm">
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select class="form-select" name="student_id" required>
                                <option value="">-- Select Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?php echo $s['uuid']; ?>"><?php echo htmlspecialchars($s['user_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select class="form-select" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Enrollment</button>
                        <a href="enrollments.php" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#addEnrollForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'add_enrollment.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') { 
                alert('Enrollment successful!');
                window.location.href='enrollments.php'; 
            } else { 
                alert('Error: ' + res.message); 
            }
        }
    });
});
</script>
<?php require_once "./includes/footer.php"; ?>