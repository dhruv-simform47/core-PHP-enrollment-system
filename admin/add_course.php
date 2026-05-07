<?php
session_start();

require_once "../db.php";
require_once "../models/Course.php";
require_once "../models/Instructor.php";





if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    // expect the json data
    header('Content-Type: application/json');

    $errors = [];
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $instructor_id = trim($_POST['instructor_id'] ?? '');
    $duration = filter_var($_POST['duration_weeks'] ?? 0, FILTER_VALIDATE_INT);
    $max_seats = filter_var($_POST['max_seats'] ?? 0, FILTER_VALIDATE_INT);

    // Backend Validation
    if (empty($course_name)) $errors[] = "Course name is required.";
    if (empty($instructor_id)) $errors[] = "Please select an instructor.";
    if ($duration === false || $duration <= 0) $errors[] = "Duration must be a valid number of weeks.";
    if ($max_seats === false || $max_seats <= 0) $errors[] = "Maximum seats must be a valid number.";

    if (empty($errors)) {
        try {

            include_once "../uuid_generator.php";
            $course_id = generateUUIDv4();

            $course_obj = new Course($pdo);
            $is_inserted = $course_obj->add($course_id, $course_name, $description, $instructor_id, $duration, $max_seats);
            if (!$is_inserted) {
                throw new Exception("Failed to Insert Course");
            }


            echo json_encode(['status' => 'success', 'message' => 'Course added successfully!']);
            exit();
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, '../logs/error.log');
            echo json_encode(['status' => 'error', 'errors' => ['Database error occurred. Please try again.']]);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit();
    }
}

//fetch instructor for form data
$instructor_obj=new Instructor($pdo);
$instructors= $instructor_obj->getAll();

?>

<?php require_once "./includes/header.php"; ?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Add New Course</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="./courses.php">Courses</a></li>
                <li class="breadcrumb-item active">Add New Course</li>
            </ol>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <i class="fas fa-book-open me-1"></i>
                    Course Details
                </div>
                <div class="card-body">

                    <div id="alertBox" class="alert" style="display: none;"></div>

                    <form id="addCourseForm">
                        <input type="hidden" name="action" value="add_course">

                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="instructor_id" class="form-label">Assign Instructor <span class="text-danger">*</span></label>
                                <select class="form-select" id="instructor_id" name="instructor_id" required>
                                    <option value="" selected disabled>-- Select Instructor --</option>
                                    <?php foreach ($instructors as $inst): ?>
                                        <option value="<?php echo htmlspecialchars($inst['uuid']); ?>">
                                            <?php echo htmlspecialchars($inst['user_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="duration_weeks" class="form-label">Duration (Weeks) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" min="1" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="max_seats" class="form-label">Maximum Seats <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="max_seats" name="max_seats" min="1" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Course
                            </button>
                            <a href="./courses.php" class="btn btn-secondary px-4">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>

    <?php require_once "./includes/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#addCourseForm').on('submit', function(e) {
                e.preventDefault();

                // Validation
                let courseName = $('#course_name').val().trim();
                let instructor = $('#instructor_id').val();
                let duration = $('#duration_weeks').val();
                let seats = $('#max_seats').val();
                let alertBox = $('#alertBox');

                if (!courseName || !instructor || duration <= 0 || seats <= 0) {
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('Please fill all required fields correctly.').slideDown();
                    return;
                }


                let formData = $(this).serialize();
                let submitBtn = $('#submitBtn');


                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
                alertBox.slideUp(); // Hide old alerts

                //Send AJAX Request 
                $.ajax({
                    url: "add_course.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === 'success') {

                            alertBox.removeClass('alert-danger').addClass('alert-success')
                                .html(response.message).slideDown();
                            $('#addCourseForm')[0].reset();
                            window.location.href = 'courses.php';

                        } else {
                            // Show validation/backend errors
                            let errorHtml = response.errors.join("<br>");
                            alertBox.removeClass('alert-success').addClass('alert-danger')
                                .html(errorHtml).slideDown();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alertBox.removeClass('alert-success').addClass('alert-danger')
                            .html('A server error occurred. Please check the console.').slideDown();
                    },
                    complete: function() {
                        // Restore button state
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Course');
                    }
                });
            });
        });
    </script>