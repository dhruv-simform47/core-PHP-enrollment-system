<?php
session_start();
require_once "../db.php";
require_once "../models/Instructor.php";

// --- AJAX HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $inst_obj = new Instructor($pdo);
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) throw new Exception("All fields are required.");
            if ($password !== $confirm_password) throw new Exception("Passwords do not match.");

            require_once "../uuid_generator.php";
            $uuid = generateUUIDv4();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            if (!$inst_obj->add($uuid, $name, $email, $hash)) throw new Exception("Failed to insert instructor.");

            require_once "../auth/mail.php";
            $mailer = new Emailnotification();
            $mailer->compose($email, "Enrollment Account credentials", "Hello $name,\n\nYour account password is: <h3>$password</h3>");

            echo json_encode(['status' => 'success']);
        } 
        elseif ($action === 'edit') {
            $id = $_POST['id'];
            $name = trim($_POST['user_name']);
            $email = trim($_POST['email']);
            $status = isset($_POST['is_verified']) ? 1 : 0;

            if (!$inst_obj->edit($name, $email, $status, $id)) throw new Exception("Failed to update instructor.");
            echo json_encode(['status' => 'success']);
        } 
        elseif ($action === 'delete') {
            if (!$inst_obj->delete($_POST['id'])) throw new Exception("Failed to delete instructor.");
            echo json_encode(['status' => 'success']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- PAGE LOAD ---
$instructors = (new Instructor($pdo))->getAll();
require_once "./includes/header.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Instructors</h1>
            <div class="card mb-4">
                <div class="card-header">                   
                    <i class="fas fa-chalkboard-teacher me-1"></i> Instructors List
                    <button class="btn btn-primary btn-sm float-end" onclick="openModal('add')">Add New Instructor</button>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Status</th><th>Joined Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instructors as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['is_verified'] ? '<span class="badge bg-success">Verified</span>' : '<span class="badge bg-warning">Pending</span>'; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='openModal("edit", <?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRecord('<?php echo $row['uuid']; ?>')">Delete</button>
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
                    <h5 class="modal-title" id="modalTitle">Manage Instructor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <input type="hidden" name="action" id="formAction">
                    <input type="hidden" name="id" id="recordId">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="user_name" id="user_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    
                    <div class="password-group">
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password">
                        </div>
                    </div>

                    <div class="mb-3 form-check edit-only">
                        <input type="checkbox" class="form-check-input" name="is_verified" id="is_verified" value="1">
                        <label class="form-check-label">Verified Account</label>
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
            $('#modalTitle').text('Edit Instructor');
            $('.password-group').hide();
            $('.edit-only').show();
            $('#password, #confirm_password').removeAttr('required').attr('name', 'ignore_pass'); 
            $('#user_name').attr('name', 'user_name'); 
            
            $('#recordId').val(data.uuid);
            $('#user_name').val(data.user_name);
            $('#email').val(data.email);
            $('#is_verified').prop('checked', data.is_verified == 1);
        } else {
            $('#modalTitle').text('Add New Instructor');
            $('.password-group').show();
            $('.edit-only').hide();
            $('#password, #confirm_password').attr('required', true).attr('name', function() { return this.id; }); 
            $('#user_name').attr('name', 'name'); 
        }
        $('#mainModal').modal('show');
    }

    $('#mainForm').on('submit', function(e) {
        e.preventDefault();
        $('#submitBtn').prop('disabled', true).text('Saving...');
        $.post('instructors.php', $(this).serialize(), function(res) {
            if(res.status === 'success') location.reload();
            else {
                $('#alertBox').removeClass('alert-success').addClass('alert-danger').text(res.message).show();
                $('#submitBtn').prop('disabled', false).text('Save changes');
            }
        }, 'json');
    });

    function deleteRecord(id) {
        if(confirm('Are you sure you want to delete this instructor?')) {
            $.post('instructors.php', { action: 'delete', id: id }, function(res) {
                if(res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        }
    }
</script>
<?php require_once "./includes/footer.php"; ?>