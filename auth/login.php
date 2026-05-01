<?php
session_start();
require_once "../db.php";


// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'instructor') {
        header("Location: ../instructor/instructor_dashboard.php");
    } else {
        header("Location: ../student/student_dashboard.php");
    }
    exit();
}

$error = '';

// 2. Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $user_captcha = trim($_POST['captcha']);

    // Check if the user answered the math question correctly
    if (empty($_SESSION['captcha_answer']) || $user_captcha != $_SESSION['captcha_answer']) {
        $error = "Invalid Security Answer! Please try again.";
    } else {
      

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['uuid'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'] ?? 'student';


            // echo $_SESSION['role']; 
            // exit();

            // Clean up the captcha from the session
            unset($_SESSION['captcha_question'], $_SESSION['captcha_answer']);

            if ($_SESSION['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } elseif ($_SESSION['role'] === 'instructor') {
                header("Location: ../instructor/instructor_dashboard.php");
            } else {
                header("Location: ../student/student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Email or Password!";
        }
    }
}

$num1 = rand(1, 10);
$num2 = rand(1, 10);
$_SESSION['captcha_question'] = "$num1 + $num2";
$_SESSION['captcha_answer'] = $num1 + $num2;

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
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Student Login</h3>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="form-label">Email address</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Security Check</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-primary" style="font-size: 1.1em; width: 140px; justify-content: center;">
                                        <?php echo $_SESSION['captcha_question']; ?> = ?
                                    </span>
                                    <input type="number" class="form-control" name="captcha" placeholder="Enter answer" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>
                        <div class="card-footer text-center py-3">
                            <div class="small"><a href="./registration.php">Need an account? Sign up!</a></div>
                            <a class="small" href="password.html">Forgot Password?</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>  </body>
</html>