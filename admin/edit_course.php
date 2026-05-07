<?php
session_start();
require_once "../db.php";
require_once "../models/Instructor.php";
require_once "../models/Course.php";

$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $course_name = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $instructor_id = $_POST['instructor_id'];
    $duration = (int)$_POST['duration_weeks'];
    $max_seats = (int)$_POST['max_seats'];

    try {
        $course_obj = new Course($pdo);
        $is_edited = $course_obj->edit($course_name, $description, $instructor_id, $duration, $max_seats, $id);
        if (!$is_edited) {
            throw new Exception("Failed to Edit");
        }

        echo json_encode(['status' => 'success']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}


$course_obj = new Course($pdo);
$course = $course_obj->getById($id);



$instructor_obj = new Instructor($pdo);
$instructors = $instructor_obj->getAll();


if (!$course) {
    header("Location: ./courses.php");
    exit();
}

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Course</h1>
            <div class="card mb-4">
                <div class="card-body">
                    <form id="editCourseForm">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Instructor</label>
                            <select class="form-select" name="instructor_id" required>
                                <?php foreach ($instructors as $inst): ?>
                                    <option value="<?php echo $inst['uuid']; ?>" <?php echo ($inst['uuid'] == $course['instructor_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($inst['user_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Duration (Weeks)</label>
                                <input type="number" class="form-control" name="duration_weeks" value="<?php echo $course['duration_weeks']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Seats</label>
                                <input type="number" class="form-control" name="max_seats" value="<?php echo $course['max_seats']; ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                        <a href="./courses.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#editCourseForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'edit_course.php?id=<?php echo $id; ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Course Updated!');
                        window.location.href = 'courses.php';
                    } else {
                        alert('Error: ' + res.message);
                    }
                }
            });
        });
    </script>
    <?php require_once "./includes/footer.php"; ?>