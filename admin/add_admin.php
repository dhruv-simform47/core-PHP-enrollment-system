<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once "../db.php";


if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['action']))) {
    header("content-type: application/json");

    $errors = [];

    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $confirm_passoword = trim($_POST["confirm_password"] ?? '');
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if (empty($name)) $errors[] = "name is required.";
    if (empty($email)) $errors[] = "email is required.";
    if (empty($password)) $errors[] = "password is required.";
    if ($password != $confirm_passoword) $errors[] = "password must match";


    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit();
    }
    try {
        include_once "../uuid_generator.php";
        $uuid = generateUUIDv4();

        $stmt = $pdo->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, role, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash,'admin', :is_verified)
            ");

        $stmt->execute([
            ':uuid' => $uuid,
            ':user_name' => $name,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':is_verified' => true
        ]);
        require_once "../auth/mail.php";

        $mailer = new Emailnotification();
        $subject = "Enrollment Account credentials";
        $mail_name = $name ?? 'admin';
        $mail_email = $email ?? '';

        $message = "Hello " . $mail_name . ",\n\nYour $mail_email account password is: \n <h3>$password</h3>";

        $mailer->compose($mail_email, $subject, $message);

        echo json_encode(['status' => 'success', 'message' => 'admin added successfully!']);
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, '../logs/error.log');
        echo json_encode(['status' => 'error', 'errors' => ['Database error occurred. Please try again.']]);
        exit();
    }
}


?>
<?php require_once "./includes/header.php"; ?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Add New admin</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="./admins.php">admins</a></li>
                <li class="breadcrumb-item active">Add New admin</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>
                    Add New admin
                </div>
                <div class="card-body">
                    <div id="alertBox" class="alert" style="display: none;"></div>
                    <form action="./add_admin.php" method="POST" id="addadminForm">

                        <input type="hidden" name="action" value="addadmin">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">confirm password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save admin
                            </button>
                            <a href="./admins.php" class="btn btn-secondary px-4">Cancel</a>
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
            $('#addadminForm').on('submit', function(e) {
                e.preventDefault();
                // Validation
                let name = $('#name').val().trim();
                let email = $('#email').val();
                let password = $('#password').val();
                let confirm_password = $('#confirm_password').val();
                let alertBox = $('#alertBox');

                if (!name || !email || !password) {
                    alertBox.removeClass('alert-success').addClass('alert-danger')
                        .html('Please fill all required fields correctly.').slideDown();
                    return;
                }
                else if (password != confirm_password) {
                    alertBox.removeClass('alert-success').addClass('alert-danger').html('password didnt match!').slideDown();
                }

                let formData = $(this).serialize();
                let submitBtn = $('#submitBtn');


                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
                alertBox.slideUp(); // Hide old alerts

                //Send AJAX Request 
                $.ajax({
                    url: "add_admin.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === 'success') {

                            alertBox.removeClass('alert-danger').addClass('alert-success')
                                .html(response.message).slideDown();


                            $('#addCourseForm')[0].reset();
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
                        submitBtn.prop('disabled', false);
                    }
                });
            });
        });
    </script>