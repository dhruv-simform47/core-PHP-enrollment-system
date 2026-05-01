<?php

//when we use it for regenerte otp check we need session variable here or not ?

$otp = rand(100000, 999999);


// Ensure $pdo is available (in case it wasn't created in the parent file)
if (!isset($pdo)) {
   require_once "../db.php";
}

//insert new otp

$stmtOtp = $pdo->prepare("
    INSERT INTO otp (user_id, otp_code, expires_at) 
    VALUES (:user_id, :otp_code, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
    ON DUPLICATE KEY UPDATE 
        otp_code = VALUES(otp_code), 
        expires_at = VALUES(expires_at)
");
$stmtOtp->execute([
    ':user_id' => $_SESSION['user_id'],
    ':otp_code' => $otp
]);




include_once "./mail.php";

$mailer = new Emailnotification();
$subject = "Verify your Enrollment Account";
$mail_name = $_SESSION['user_name'] ?? 'Student';
$mail_email = $_SESSION['email'] ?? '';

$message = "Hello " . $mail_name . ",\n\nYour verification code is: $otp\nIt expires in 5 minutes.";

$mailer->compose($mail_email, $subject, $message);
?>




