<?php
session_start();
require_once "../db.php";
require_once "../models/Auth.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: registration.php");
    exit();
}

$auth = new Auth($pdo);
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend_btn'])) {
    $auth->sendOTP($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['email']);
    $success_msg = "A new OTP has been sent to your email!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_btn'])) {
    header('Content-Type: application/json');
    if ($auth->verifyOTP($_SESSION['user_id'], trim($_POST['userotp']))) {
        unset($_SESSION['email']);
        echo json_encode(['status' => 'success', 'redirect' => '../student/student_dashboard.php']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary">
    <div class="container mt-5">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <?php if ($success_msg): ?><div class="alert alert-success text-center"><?php echo $success_msg; ?></div><?php endif; ?>
                <div class="card shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h3 class="mb-4">OTP Verification</h3>
                        <p>Sent to: <strong><?php echo $_SESSION['email']; ?></strong></p>
                        <form id="verifyForm">
                            <div class="mb-3">
                                <input type="text" class="form-control form-control-lg text-center" id="userotp" name="userotp" maxlength="6" required>
                                <div id="otpError" class="text-danger mt-2" style="display:none;">Invalid or expired OTP.</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" id="verifyBtn" class="btn btn-primary btn-lg">Verify OTP</button>
                            </div>
                        </form>
                        <form method="POST" class="mt-3">
                            <button type="submit" name="resend_btn" class="btn btn-link">Resend OTP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#verifyForm').on("submit", function(e) {
                e.preventDefault();
                $('#verifyBtn').prop('disabled', true).text('Verifying...');
                $.ajax({
                    url: "verify_otp.php",
                    type: "POST",
                    data: $(this).serialize() + "&verify_btn=1",
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') window.location.href = res.redirect;
                        else {
                            $('#otpError').show();
                            $('#verifyBtn').prop('disabled', false).text('Verify OTP');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>