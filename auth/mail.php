<?php
// Ensure this points to where your autoloader or bootstrap is located 
require_once __DIR__ . '/../init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

interface Notification
{
    public function compose($recipient, $subject, $body, $cc = "", $bcc = "");
}

class Emailnotification implements Notification
{
    private string $host;
    private string $username;
    private string $password;
    private int $port;

    public function __construct()
    {
        // Pulling values directly from the .env file
        $this->host     = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->username = $_ENV['SMTP_USER'] ?? '';
        $this->password = $_ENV['SMTP_PASS'] ?? '';
        $this->port     = (int)($_ENV['SMTP_PORT'] ?? 587);
    }

    public function compose($recipient, $subject, $body, $cc = "", $bcc = [])
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;

            // Recipients
            $mail->setFrom($this->username, 'Enrollment System');
            $mail->addAddress($recipient);

            if (!empty($cc)) {
                $mail->addCC($cc);
            }
            if (!empty($bcc)) {
                foreach ($bcc as $email) {
                    $mail->addBCC(trim($email));
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
