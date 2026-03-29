<?php
// modules/student/login.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Kung naka-login na bilang student, ibato agad sa portal dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Student') {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = trim($_POST['student_number']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ? AND password = ?");
        $stmt->execute([$student_number, $password]);
        $student = $stmt->fetch();

        if ($student) {
            // Setup Student Session
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_number'] = $student['student_number'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['role'] = 'Student';
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid Student Number or Password.";
        }
    } catch (PDOException $e) {
        $error = "System Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); /* Deep Blue Gradient */
            color: #333;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .logo-circle {
            width: 80px; height: 80px;
            background: #eff6ff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 40px; margin: 0 auto 20px auto;
            color: #3b82f6;
        }
        .title { font-size: 1.8rem; font-weight: 800; color: #1e3a8a; margin-bottom: 5px; }
        .subtitle { font-size: 0.95rem; color: #64748b; margin-bottom: 30px; }
        
        .input-group { position: relative; margin-bottom: 20px; text-align: left; }
        .input-label { display: block; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
        .input-field {
            width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 15px; transition: 0.3s; background: #f8fafc; outline: none;
        }
        .input-field:focus { border-color: #3b82f6; background: #ffffff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        
        .btn-login {
            width: 100%; padding: 16px; background: #3b82f6; color: white; border: none;
            border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
        }
        .btn-login:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 10px 15px rgba(37, 99, 235, 0.2); }
        
        .error-msg {
            background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px;
            font-size: 14px; font-weight: bold; margin-bottom: 20px; display: <?= $error ? 'block' : 'none' ?>;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-circle">🎓</div>
        <h1 class="title">Student Portal</h1>
        <p class="subtitle">Enter your credentials to access your account.</p>

        <div class="error-msg"><?= htmlspecialchars($error) ?></div>

        <form method="POST" action="">
            <div class="input-group">
                <label class="input-label">Student Number</label>
                <input type="text" name="student_number" class="input-field" placeholder="e.g. 2026-0001" required>
            </div>
            
            <div class="input-group">
                <label class="input-label">Password</label>
                <input type="password" name="password" class="input-field" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Sign In ➔</button>
        </form>
        
        <p style="margin-top: 25px; font-size: 13px; color: #94a3b8;">Default testing password is <strong>123456</strong></p>
    </div>

</body>
</html>