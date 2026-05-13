<?php
session_start();
require_once "../db.php";
require_once "../models/Auth.php";

if (isset($_SESSION['user_id'])) {
    Auth::redirectByRole($_SESSION['role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $user_password = $_POST['password'];
    $user_captcha = trim($_POST['captcha']);

    if (empty($_SESSION['captcha_answer']) || $user_captcha != $_SESSION['captcha_answer']) {
        $error = "Invalid Security Answer!";
    } else {
        $auth = new Auth($pdo);
        $result = $auth->login($email, $user_password);

        if ($result['status'] === 'success') {
            unset($_SESSION['captcha_question'], $_SESSION['captcha_answer']);
            Auth::redirectByRole($result['role']);
        } else {
            $error = $result['message'];
        }
    }
}

$num1 = rand(1, 10); $num2 = rand(1, 10);
$_SESSION['captcha_question'] = "$num1 + $num2";
$_SESSION['captcha_answer'] = $num1 + $num2;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary">
    <div class="container mt-5">
        <div class="row justify-content-center mt-5">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Student Login</h3>
                        <?php if ($error): ?><div class="alert alert-danger text-center"><?php echo $error; ?></div><?php endif; ?>
                        <form method="POST">
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
                                    <span class="input-group-text bg-light fw-bold text-primary">
                                        <?php echo $_SESSION['captcha_question']; ?> = ?
                                    </span>
                                    <input type="number" class="form-control" name="captcha" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>
                        <div class="card-footer text-center py-3">
                            <div class="small"><a href="./registration.php">Need an account? Sign up!</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>