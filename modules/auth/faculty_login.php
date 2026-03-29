<?php
// modules/auth/faculty_login.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Kung naka-login na siya, idiretso na sa portal
if(isset($_SESSION['role']) && $_SESSION['role'] === 'Faculty'){
    header("Location: ../faculty/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_no = trim($_POST['employee_no'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    if(empty($employee_no) || empty($last_name)) {
        $error = "Please enter your Employee Number and Last Name.";
    } else {
        try {
            // Hanapin ang teacher sa database
            $stmt = $pdo->prepare("SELECT id, first_name, department, status FROM teachers WHERE employee_no = ? AND last_name = ?");
            $stmt->execute([$employee_no, $last_name]);
            $faculty = $stmt->fetch();

            if($faculty) {
                if($faculty['status'] !== 'Active') {
                    $error = "Your account is currently " . $faculty['status'] . ". Please contact HR/Admin.";
                } else {
                    // Success!
                    $_SESSION['role'] = 'Faculty';
                    $_SESSION['faculty_id'] = $faculty['id'];
                    $_SESSION['first_name'] = $faculty['first_name'];
                    $_SESSION['department'] = $faculty['department'];
                    
                    header("Location: ../faculty/index.php");
                    exit();
                }
            } else {
                $error = "Invalid Employee Number or Last Name. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Login | UMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body { 
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #1e293b;
        }

        .login-wrapper { background: #ffffff; width: 100%; max-width: 400px; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(20, 184, 166, 0.15); border: 1px solid rgba(20, 184, 166, 0.1); }
        .logo-area { text-align: center; margin-bottom: 30px; }
        .logo-icon { font-size: 50px; margin-bottom: 10px; }
        .logo-text { font-size: 1.5rem; font-weight: 900; color: #0d9488; letter-spacing: 1px; }
        .logo-sub { font-size: 0.9rem; color: gray; }

        .form-group { position: relative; margin-bottom: 25px; }
        .form-input { width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-weight: 600; outline: none; transition: 0.3s; background: #f8fafc; }
        .form-label { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: gray; font-size: 14px; transition: 0.2s ease; pointer-events: none; background: transparent; padding: 0 5px; }

        .form-input:focus, .form-input:not(:placeholder-shown) { border-color: #0d9488; background: #ffffff; }
        .form-input:focus ~ .form-label, .form-input:not(:placeholder-shown) ~ .form-label { top: 0; font-size: 12px; font-weight: bold; color: #0d9488; background: #ffffff; }

        .login-btn { background: #0d9488; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(13, 148, 136, 0.3); }
        .login-btn:hover { background: #0f766e; transform: translateY(-2px); }

        .error-msg { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2); }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 13px; color: gray; text-decoration: none; font-weight: 600; }
        .back-link:hover { color: #0d9488; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="logo-area">
            <div class="logo-icon">👨‍🏫</div>
            <div class="logo-text">FACULTY PORTAL</div>
            <div class="logo-sub">University Management System</div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="employee_no" id="employee_no" class="form-input" placeholder=" " required autocomplete="off">
                <label for="employee_no" class="form-label">Employee Number</label>
            </div>
            <div class="form-group">
                <input type="password" name="last_name" id="last_name" class="form-input" placeholder=" " required autocomplete="off">
                <label for="last_name" class="form-label">Last Name (Password)</label>
            </div>
            <button type="submit" class="login-btn">Sign In ➔</button>
        </form>

        <a href="../admin/index.php" class="back-link">Admin Login? Click here.</a>
    </div>
</body>
</html>