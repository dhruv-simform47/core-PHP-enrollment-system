<?php
class Auth {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public static function redirectByRole($role) {
        switch ($role) {
            case 'admin': header("Location: ../admin/admin_dashboard.php"); break;
            case 'instructor': header("Location: ../instructor/instructor_dashboard.php"); break;
            default: header("Location: ../student/student_dashboard.php"); break;
        }
        exit();
    }

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

    public function register($username, $email, $password) {
        try {
            $this->db->beginTransaction();
            require_once "../uuid_generator.php";
            $uuid = generateUUIDv4();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO users(uuid, user_name, email, password_hash, role) VALUES (?, ?, ?, ?, 'student')");
            $stmt->execute([$uuid, $username, $email, $hash]);
            
            $_SESSION['user_id'] = $uuid;
            $_SESSION['user_name'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'student';
            
            $this->db->commit();
            return ['status' => 'success'];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Auth::register Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return ['status' => 'error', 'message' => 'Registration failed due to a system error.'];
        }
    }

    public function sendOTP($user_id, $user_name, $email) {
        try {
            $this->db->beginTransaction();
            $otp = rand(100000, 999999);

            $stmt = $this->db->prepare("
                INSERT INTO otp (user_id, otp_code, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))
                ON DUPLICATE KEY UPDATE 
                    otp_code = VALUES(otp_code), 
                    expires_at = VALUES(expires_at)
            ");
            $stmt->execute([$user_id, $otp]);
            $this->db->commit();

            require_once "./mail.php";
            $mailer = new Emailnotification();
            $subject = "Verify your Enrollment Account";
            $message = "Hello $user_name,\n\nYour verification code is: $otp\nIt expires in 5 minutes.";
            
            return $mailer->compose($email, $subject, $message);
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Auth::sendOTP Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function verifyOTP($user_id, $otp_code) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT id FROM otp WHERE user_id = ? AND otp_code = ? AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$user_id, $otp_code]);

            if ($stmt->rowCount() > 0) {
                $update = $this->db->prepare("UPDATE users SET is_verified = true WHERE uuid = ?");
                $update->execute([$user_id]);
                $this->db->commit();
                return true;
            }
            $this->db->rollBack();
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Auth::verifyOTP Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }
}