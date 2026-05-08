<?php
session_start();
require_once "../db.php";
require_once "../models/Auth.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    Auth::redirectByRole($_SESSION['role']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["send_otp"])) {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    $auth = new Auth($pdo);
    $result = $auth->register($username, $email, $password);

    if ($result['status'] === 'success') {
        $auth->sendOTP($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['email']);
        echo json_encode(['status' => 'success', 'redirect' => 'verify_otp.php']);
    } else {
        echo json_encode(['status' => 'error', 'errors' => [$result['message']]]);
    }
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary">
    <div class="container mt-5">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Student Registration</h3>
                        <form id="form_register">
                            <div class="m-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="username" name="username">
                                <span id="usernameError" class="text-danger small"></span>
                            </div>
                            <div class="m-3">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <span id="emailError" class="text-danger small"></span>
                            </div>
                            <div class="m-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <span id="pswError" class="text-danger small"></span>
                            </div>
                            <div class="m-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <span id="cpswError" class="text-danger small"></span>
                            </div>
                            <div class="d-grid justify-content-center">
                                <button type="submit" class="btn btn-primary">Send OTP</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="./login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#form_register').on("submit", function(e) {
                e.preventDefault();
                let isValid = true;
                $('.text-danger').text("");
                
                if ($("#username").val().trim() == "") { $("#usernameError").text("Name required"); isValid = false; }
                if ($("#email").val().trim() == "") { $("#emailError").text("Email required"); isValid = false; }
                if ($("#password").val().length < 6) { $("#pswError").text("Min 6 chars"); isValid = false; }
                if ($("#password").val() != $("#confirm_password").val()) { $("#cpswError").text("Passwords don't match"); isValid = false; }

                if (!isValid) return;

                $.ajax({
                    url: "registration.php",
                    type: "POST",
                    data: $(this).serialize() + "&send_otp=1",
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') window.location.href = res.redirect;
                        else alert("Error: " + res.errors.join(", "));
                    }
                });
            });
        });
    </script>
</body>
</html>