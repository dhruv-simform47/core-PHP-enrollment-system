<?php
session_start();
require_once "../db.php";
require_once "../models/Course.php";
require_once "../models/Instructor.php";

// --- AJAX HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $course_obj = new Course($pdo);
    $action = $_POST['action'];

    try {
        if ($action === 'add' || $action === 'edit') {
            $course_name = trim($_POST['course_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $instructor_id = trim($_POST['instructor_id'] ?? '');
            $duration = filter_var($_POST['duration_weeks'] ?? 0, FILTER_VALIDATE_INT);
            $max_seats = filter_var($_POST['max_seats'] ?? 0, FILTER_VALIDATE_INT);

            if (empty($course_name) || empty($instructor_id)) throw new Exception("Course Name and Instructor are required.");
            if (!$duration || $duration <= 0) throw new Exception("Duration must be a valid positive number.");
            if (!$max_seats || $max_seats <= 0) throw new Exception("Max seats must be a valid positive number.");

            if ($action === 'add') {
                require_once "../uuid_generator.php";
                $course_id = generateUUIDv4();
                if (!$course_obj->add($course_id, $course_name, $description, $instructor_id, $duration, $max_seats)) {
                    throw new Exception("Failed to insert Course.");
                }
            } else {
                if (!$course_obj->edit($course_name, $description, $instructor_id, $duration, $max_seats, $_POST['id'])) {
                    throw new Exception("Failed to update Course.");
                }
            }
            echo json_encode(['status' => 'success']);
        } 
        elseif ($action === 'delete') {
            if (!$course_obj->delete($_POST['id'])) throw new Exception("Failed to delete Course.");
            echo json_encode(['status' => 'success']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- PAGE LOAD ---
$courses = (new Course($pdo))->getFullDetails();
$instructors = (new Instructor($pdo))->getAll();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Courses</h1>
            <div class="card mb-4">
                <div class="card-header">                   
                    <i class="fas fa-book me-1"></i> Courses List
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Course</button>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr><th>Name</th><th>Instructor</th><th>Duration</th><th>Vacant Seats</th><th>Max Seats</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['instructor_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['duration_weeks']); ?> wks</td>
                                <td><?php echo htmlspecialchars($row['vacant_seats']); ?></td>
                                <td><?php echo htmlspecialchars($row['max_seats']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", <?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRecord('<?php echo $row['id']; ?>')">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="mainModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="course_name" id="course_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instructor <span class="text-danger">*</span></label>
                            <select class="form-select" name="instructor_id" id="instructor_id" required>
                                <option value="" disabled selected>-- Select --</option>
                                <?php foreach ($instructors as $inst): ?>
                                    <option value="<?php echo htmlspecialchars($inst['uuid']); ?>"><?php echo htmlspecialchars($inst['user_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (Weeks) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration_weeks" id="duration_weeks" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Seats <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_seats" id="max_seats" min="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submitBtn" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function openModal(action, data = null) {
        $('#mainForm')[0].reset();
        $('#alertBox').hide();
        $('#formAction').val(action);
        
        if(action === 'edit') {
            $('#modalTitle').text('Edit Course');
            $('#recordId').val(data.id);
            $('#course_name').val(data.course_name);
            $('#description').val(data.description);
            $('#instructor_id').val(data.instructor_id);
            $('#duration_weeks').val(data.duration_weeks);
            $('#max_seats').val(data.max_seats);
        } else {
            $('#modalTitle').text('Add New Course');
        }
        $('#mainModal').modal('show');
    }

    $('#mainForm').on('submit', function(e) {
        e.preventDefault();
        $('#submitBtn').prop('disabled', true).text('Saving...');
        $.post('courses.php', $(this).serialize(), function(res) {
            if(res.status === 'success') location.reload();
            else {
                $('#alertBox').removeClass('alert-success').addClass('alert-danger').text(res.message).show();
                $('#submitBtn').prop('disabled', false).text('Save changes');
            }
        }, 'json');
    });

    function deleteRecord(id) {
        if(confirm('Are you sure you want to delete this course?')) {
            $.post('courses.php', { action: 'delete', id: id }, function(res) {
                if(res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        }
    }
</script>
<?php require_once "./includes/footer.php"; ?>