<?php
session_start();
require_once "../db.php";
require_once "../models/Enrollment.php";
require_once "../models/Student.php";
require_once "../models/Course.php";

// --- AJAX HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $enroll_obj = new Enrollment($pdo);
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            $student_id = $_POST['student_id'] ?? '';
            $course_id = $_POST['course_id'] ?? '';

            if (empty($student_id) || empty($course_id)) throw new Exception("Please select both student and course.");
            
            $result = $enroll_obj->enroll($student_id, $course_id);
            if ($result['status'] === 'error') throw new Exception($result['message']);
            
            echo json_encode(['status' => 'success', 'message' => 'Successfully enrolled!']);
        } 
        elseif ($action === 'edit') {
            $enroll_id = $_POST['id'];
            $status = $_POST['status'];

            $result = $enroll_obj->updateStatus($enroll_id, $status);
            if ($result['status'] === 'error') throw new Exception($result['message']);

            echo json_encode(['status' => 'success', 'message' => 'Status updated!']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- PAGE LOAD ---
$enrollments = (new Enrollment($pdo))->getAllEnrollments();
$students = (new Student($pdo))->getAll();
$courses = (new Course($pdo))->getAll();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Enrollments</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i> Enrollment Records
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Enrollment</button>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr><th>Student</th><th>Course</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['enrolled_date'])); ?></td>
                                    <td>
                                        <?php
                                        $badge = 'bg-primary';
                                        if ($row['status'] == 'completed') $badge = 'bg-success';
                                        if ($row['status'] == 'cancelled') $badge = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick='openModal("edit", <?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Update Status</button>
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
        <div class="modal-dialog">
            <form id="mainForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Manage Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">

                    <div class="add-only">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student_id" id="student_id">
                                <option value="" disabled selected>-- Select Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['uuid']); ?>"><?php echo htmlspecialchars($s['user_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select class="form-select" name="course_id" id="course_id">
                                <option value="" disabled selected>-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c['id']); ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="edit-only">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Student:</label>
                            <div id="displayStudent" class="form-control-plaintext border-bottom"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Course:</label>
                            <div id="displayCourse" class="form-control-plaintext border-bottom"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enrollment Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
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
            $('#modalTitle').text('Update Enrollment Status');
            $('.add-only').hide();
            $('.edit-only').show();
            $('#student_id, #course_id').removeAttr('required');
            
            $('#recordId').val(data.id);
            $('#displayStudent').text(data.student);
            $('#displayCourse').text(data.course);
            $('#status').val(data.status);
        } else {
            $('#modalTitle').text('Add New Enrollment');
            $('.add-only').show();
            $('.edit-only').hide();
            $('#student_id, #course_id').attr('required', true);
        }
        $('#mainModal').modal('show');
    }

    $('#mainForm').on('submit', function(e) {
        e.preventDefault();
        $('#submitBtn').prop('disabled', true).text('Saving...');
        $.post('enrollments.php', $(this).serialize(), function(res) {
            if(res.status === 'success') location.reload();
            else {
                $('#alertBox').removeClass('alert-success').addClass('alert-danger').text(res.message).show();
                $('#submitBtn').prop('disabled', false).text('Save changes');
            }
        }, 'json');
    });
</script>
<?php require_once "./includes/footer.php"; ?>