<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION['role'] !== 'student') {
    header("Location: registration.php");
    exit();
}

require_once "../db.php";
$error_msg = '';
$success_msg = '';

//  RESEND OTP 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend_btn'])) {
    include_once "./generate_otp.php";
    $success_msg = "A new OTP has been sent to your email!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_btn'])) {

    $userotp = trim($_POST['userotp']);
    $user_id = $_SESSION['user_id'];
    // fetch record which have same otp as user submitted; to check valid otp
    $stmt = $pdo->prepare("SELECT id FROM otp WHERE user_id = :user_id AND otp_code = :otp_code AND expires_at > NOW() LIMIT 1");
    $stmt->execute([':user_id' => $user_id, ':otp_code' => $userotp]);
    //if count is > 0 then valid otp
    if ($stmt->rowCount() > 0) {
        $editstmt = $pdo->prepare("update users set is_verified=true where uuid= :user_id");
        $editstmt->execute([':user_id' => $user_id]);
        if ($editstmt->rowCount() > 0) {
            unset($_SESSION['email']); // Clean up

            echo json_encode(['status' => 'success', 'redirect' => '../student/student_dashboard.php']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error']);
        exit();
    }
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

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success text-center"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">OTP Verification</h3>
                <p class="text-muted text-center mb-4">
                    Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></strong>
                </p>

                <form id="verifyForm">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-lg text-center fw-bold"
                            id="userotp" name="userotp" maxlength="6" placeholder="------" autocomplete="off" required>
                        <div id="otpError" class="text-danger mt-2 text-center" style="display: none; font-size: 0.9em;"></div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" id="verifyBtn" class="btn btn-primary btn-lg">Verify OTP</button>
                    </div>
                </form>

                <form method="POST" action="verify_otp.php" class="text-center">
                    <p class="mb-0">Didn't get the code?
                        <button type="submit" name="resend_btn" class="btn btn-link p-0 m-0 align-baseline text-decoration-none">Resend OTP</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
<script>
    $(document).ready(function() {

        // Numbers only
        $('#userotp').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        $('#verifyForm').on("submit", function(e) {
            e.preventDefault();

            let otpValue = $('#userotp').val().trim();
            let errorSpan = $('#otpError');
            let verifyBtn = $('#verifyBtn');

            if (!/^\d{6}$/.test(otpValue)) {
                errorSpan.text("Please enter exactly 6 digits.").slideDown('fast');
                $('#userotp').css('border-color', 'red');
                return false;
            }

            errorSpan.slideUp('fast');
            $('#userotp').css('border-color', '#ced4da');
            verifyBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...');

            let formData = $(this).serialize() + "&verify_btn=1";

            $.ajax({
                url: "verify_otp.php",
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = response.redirect;
                    } else {
                        verifyBtn.prop('disabled', false).text('Verify OTP');
                        $('#otpError').text("Invalid or expired OTP.").slideDown('fast');
                        $('#userotp').css('border-color', 'red').val('');
                    }
                },
                error: function(xhr, status, error) {
                    verifyBtn.prop('disabled', false).text('Verify OTP');
                    console.log('AJAX Error:', xhr.responseText);
                }
            });
        });
    });
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
