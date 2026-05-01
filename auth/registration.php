<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'instructor') {
        header("Location: ../admin/instructor_dashboard.php");
    } else {
        header("Location: ../student/student_dashboard.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["send_otp"])) {

    $errors = [];
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    //validation for registration form

    if (empty($username)) {
        $errors[] = "username Required";
    }

    if (empty($email)) {
        $errors[] = "email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "invalid email format";
    }


    if (empty($password)) {
        $errors[] = "Password is required";
    } else if (strlen($password) < 6) {
        $errors[] = "Password should be 6 char long";
    } else if ($password !== $confirm_password) {
        $errors[] = "password does not match";
    }

    // if errors then return to registyration
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit();
    }
    //otherwise add user and send otp
    //this session var is used in verify_otp file to insert into db
    $_SESSION['user_name'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    $_SESSION['role'] = 'student';


require_once "../db.php";

    //NEW USER ===================
    include_once "../uuid_generator.php";
    $uuid = generateUUIDv4();


    //prepare query 

    $stmt = $pdo->prepare("Insert into users(uuid,user_name,email,password_hash) 
    values(:uuid,:user_name,:email,:password_hash)
    ");

    // EXECUTE QUERY 
    $stmt->execute([
        ':uuid' => $uuid,
        ':user_name' => $_SESSION['user_name'],
        ':email' => $_SESSION['email'],
        ':password_hash' => $_SESSION['password_hash']
    ]);

    // Set the session for the next page
    $_SESSION['user_id'] = $uuid;


    // CREATE OTP 
    include_once "./generate_otp.php";


    if (isset($_SESSION['user_id'])) {

        echo json_encode(['status' => 'success', 'redirect' => 'verify_otp.php']);
        exit();

        //old way
        // header("Location: verify_otp.php");
        // exit();
    }



    // header('Location: registration.php');
    // exit();

}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrolment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<body class="bg-secondary">
    <div class="container mt-5">

        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Student Registration</h3>

                        <?php if (!empty($_SESSION['errors'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($_SESSION['errors'] as $err) {
                                    echo $err . "<br>";
                                } ?>
                            <?php endif; ?>
                            </div>
                            <form id="form_register">
                                <div class="m-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"><span id="usernameError" class="error text-danger"></span>
                                </div>

                                <div class="m-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    <span id="emailError" class="error"></span>
                                </div>

                                <div class="m-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="6">
                                    <span id="pswError" class="error"></span>
                                </div>

                                <div class="m-4">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                                    <span id="cpswError" class="error"></span>
                                </div>

                                <div class="d-grid justify-content-center ">
                                    <button type="submit" name="submit" class="btn btn-primary btn-md ">Send OTP</button>
                                </div>
                            </form>

                            <div class="text-center mt-3">
                                <p>Already have an account? <a href="./login.php">Login here</a></p>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" crossorigin="anonymous"></script>

        <script>
            function validateEmail(email) {
                let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(email);
            }




            $(document).ready(function() {
                $('#form_register').on("submit", function(e) {
                    e.preventDefault();

                    let isValid = true;
                    $('.error').text("");
                    let username = $("#username").val().trim();
                    let email = $("#email").val().trim();
                    let password = $("#password").val().trim();
                    let cnf_password = $("#confirm_password").val().trim();
                    if (username == "") {
                        isValid = false;
                        $("#usernameError").text("uname Required");
                    }

                    if (email == "") {
                        isValid = false;
                        $("#emailError").text("email Required");
                    } else if (!validateEmail(email)) {
                        isValid = false;
                        $("#emailError").text("Invalid email");
                    }

                    if (password == "") {
                        isValid = false;
                        $("#pswError").text("Password Required");
                    } else if (password.length < 6) {
                        isValid = false;
                        $("#pswError").text("Password should be 6 char long");
                    } else if (password != cnf_password) {
                        isValid = false;
                        $("#cpswError").text("Password should match");
                    }

                    if (!isValid) return;
                    let formData = $(this).serialize();
                    formData += "&send_otp=1";

                    $.ajax({
                        url: "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>",
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                //  JS handle the redirect
                                window.location.href = response.redirect;
                            } else if (response.status === 'error') {
                                // If  errors that JS show it
                                console.log('PHP Errors:', response.errors);
                                alert("Server Error: " + response.errors.join(", "));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', error);
                            console.log('Response Text:', xhr.responseText);
                        }

                    });



                });
            });
        </script>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>  </body>
</html>