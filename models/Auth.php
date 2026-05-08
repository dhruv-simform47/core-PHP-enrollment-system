<?php

class Auth {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * Handles  redirection based on user role
     */
    public static function redirectByRole($role) {
        switch ($role) {
            case 'admin':
                header("Location: ../admin/admin_dashboard.php");
                break;
            case 'instructor':
                header("Location: ../instructor/instructor_dashboard.php");
                break;
            default:
                header("Location: ../student/student_dashboard.php");
                break;
        }
        exit();
    }

    /**
     * Login Logic
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['uuid'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'] ?? 'student';
            return ['status' => 'success', 'role' => $_SESSION['role']];
        }
        return ['status' => 'error', 'message' => 'Invalid Email or Password!'];
    }

    /**
     * Registration Logic
     */
    public function register($username, $email, $password) {
        require_once "../uuid_generator.php";
        $uuid = generateUUIDv4();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users(uuid, user_name, email, password_hash, role) VALUES (?, ?, ?, ?, 'student')");
        
        if ($stmt->execute([$uuid, $username, $email, $hash])) {
            $_SESSION['user_id'] = $uuid;
            $_SESSION['user_name'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'student';
            return ['status' => 'success'];
        }
        return ['status' => 'error', 'message' => 'Registration failed.'];
    }

    /**
     * Integrated OTP Generation and Mailing
     */
    public function sendOTP($user_id, $user_name, $email) {
        $otp = rand(100000, 999999);

        $stmt = $this->db->prepare("
            INSERT INTO otp (user_id, otp_code, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
            ON DUPLICATE KEY UPDATE 
                otp_code = VALUES(otp_code), 
                expires_at = VALUES(expires_at)
        ");
        $stmt->execute([$user_id, $otp]);

        require_once "./mail.php";
        $mailer = new Emailnotification();
        $subject = "Verify your Enrollment Account";
        $message = "Hello $user_name,\n\nYour verification code is: $otp\nIt expires in 5 minutes.";
        
        return $mailer->compose($email, $subject, $message);
    }

    /**
     * OTP Verification
     */
    public function verifyOTP($user_id, $otp_code) {
        $stmt = $this->db->prepare("SELECT id FROM otp WHERE user_id = ? AND otp_code = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$user_id, $otp_code]);

        if ($stmt->rowCount() > 0) {
            $update = $this->db->prepare("UPDATE users SET is_verified = true WHERE uuid = ?");
            $update->execute([$user_id]);
            return true;
        }
        return false;
    }
}