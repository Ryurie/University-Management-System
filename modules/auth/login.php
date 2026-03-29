<?php
// modules/auth/login.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Kapag nakapag-login na, bawal na bumalik sa login page
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') header("Location: ../admin/index.php");
    elseif ($_SESSION['role'] === 'Teacher') header("Location: ../faculty/index.php");
    elseif ($_SESSION['role'] === 'Student') header("Location: ../student/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 1. ADMIN CHECK (Hardcoded para sa UAT)
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['role'] = 'Admin';
        $_SESSION['first_name'] = 'System';
        $_SESSION['last_name'] = 'Administrator';
        header("Location: ../admin/index.php");
        exit();
    }

    // 2. TEACHER CHECK (Email/Employee No at Password)
    $stmt_t = $pdo->prepare("SELECT * FROM teachers WHERE (email = ? OR employee_no = ?) AND status = 'Active'");
    $stmt_t->execute([$username, $username]);
    $teacher = $stmt_t->fetch();

    if ($teacher && password_verify($password, $teacher['password'])) {
        $_SESSION['role'] = 'Teacher';
        $_SESSION['user_id'] = $teacher['id'];
        $_SESSION['first_name'] = $teacher['first_name'];
        $_SESSION['last_name'] = $teacher['last_name'];
        header("Location: ../faculty/index.php");
        exit();
    }

    // 3. STUDENT CHECK (Email/Student No at Password)
    $stmt_s = $pdo->prepare("SELECT * FROM students WHERE (email = ? OR student_no = ?) AND status = 'Active'");
    $stmt_s->execute([$username, $username]);
    $student = $stmt_s->fetch();

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['role'] = 'Student';
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['first_name'] = $student['first_name'];
        $_SESSION['last_name'] = $student['last_name'];
        header("Location: ../student/index.php");
        exit();
    }

    // Kapag walang tumama
    $error = "Mali ang Username o Password, o kaya inactive ang account mo.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BENRU's NETWORK</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        body {
            background-color: #0f172a; 
            color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden; /* Para hindi lumagpas yung glow */
        }

        /* ✨ BACKGROUND GLOW EFFECTS (Premium Feel) */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.5;
        }
        .glow-orb-1 {
            width: 300px; height: 300px;
            background: #3b82f6;
            top: -50px; left: -50px;
        }
        .glow-orb-2 {
            width: 400px; height: 400px;
            background: #8b5cf6;
            bottom: -100px; right: -50px;
        }

        /* ✨ ENTRANCE ANIMATION */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(15px); /* Glassmorphism effect */
            width: 100%;
            max-width: 420px;
            padding: 45px 35px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.08);
            text-align: center;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            z-index: 10;
        }

        .logo-badge {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 900;
            letter-spacing: 2px;
            font-size: 18px;
            display: inline-block;
            margin-bottom: 10px;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .subtitle {
            color: #94a3b8;
            font-size: 15px;
            margin-bottom: 35px;
            font-weight: 500;
        }

        .input-group {
            text-align: left;
            margin-bottom: 22px;
            position: relative; /* Para sa icons */
        }

        .input-group label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #94a3b8;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* 👤 INPUT ICONS FIX */
        .input-icon {
            position: absolute;
            left: 15px;
            top: 42px; /* Adjusted para sa label */
            color: #64748b;
            font-size: 16px;
        }

        .custom-input {
            width: 100%;
            padding: 14px 14px 14px 45px; /* Dinagdagan ang left padding para sa icon */
            border: 2px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.6);
            color: white;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .custom-input:focus {
            border-color: #3b82f6;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        /* 👁️ TOGGLE PASSWORD BUTTON */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #64748b;
            font-size: 16px;
            background: none;
            border: none;
            outline: none;
            transition: 0.2s;
        }
        .toggle-password:hover { color: #f1f5f9; }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: left;
            animation: slideUp 0.3s ease forwards;
        }

        /* MOBILE RESPONSIVE */
        @media (max-width: 480px) {
            .login-card { padding: 35px 25px; border-radius: 20px;}
            .logo-badge { font-size: 16px; }
            .glow-orb { display: none; } /* I-hide sa mobile para tipid battery at lag */
        }
    </style>
</head>
<body>

    <div class="glow-orb glow-orb-1"></div>
    <div class="glow-orb glow-orb-2"></div>

    <div class="login-card">
        <div class="logo-badge">BENRU'S NETWORK</div>
        <div class="subtitle">Secure University Portal</div>

        <?php if(!empty($error)): ?>
            <div class="alert-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>ID Number or Email</label>
                <span class="input-icon">👤</span>
                <input type="text" name="username" class="custom-input" required placeholder="Enter your credentials">
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <span class="input-icon">🔒</span>
                <input type="password" name="password" id="passwordInput" class="custom-input" required placeholder="••••••••">
                <button type="button" class="toggle-password" id="toggleBtn" onclick="togglePassword()">👁️</button>
            </div>

            <button type="submit" name="login" class="btn-login">Sign In Securely</button>
        </form>

        <div style="margin-top: 30px; font-size: 12px; color: #64748b; font-weight: 500;">
            &copy; <?= date('Y') ?> System Administration. All rights reserved.
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwdInput = document.getElementById("passwordInput");
            const btn = document.getElementById("toggleBtn");
            if (pwdInput.type === "password") {
                pwdInput.type = "text";
                btn.innerHTML = "🙈"; // Change icon to hide
                btn.style.color = "#3b82f6"; // Highlight color
            } else {
                pwdInput.type = "password";
                btn.innerHTML = "👁️"; // Change back to show
                btn.style.color = "#64748b";
            }
        }
    </script>

</body>
</html>