<?php
// modules/student/change_password.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check: Student lang ang pwedeng pumasok
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Student Portal only.</h2></div>");
}

$student_id = $_SESSION['student_id'];
$msg = '';
$msgType = '';

// Kapag sinubmit ang form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pw = $_POST['current_password'];
    $new_pw = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    if (empty($current_pw) || empty($new_pw) || empty($confirm_pw)) {
        $msg = "Pakilagyan ang lahat ng fields.";
        $msgType = "error";
    } elseif ($new_pw !== $confirm_pw) {
        $msg = "Hindi nag-match ang iyong New Password at Confirm Password.";
        $msgType = "error";
    } elseif (strlen($new_pw) < 6) {
        $msg = "Ang password ay dapat may hindi bababa sa 6 na characters.";
        $msgType = "error";
    } else {
        try {
            // Kunin ang kasalukuyang password hash sa database
            $stmt = $pdo->prepare("SELECT password FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();

            // I-verify kung tama ang tinype niyang "Current Password"
            if (password_verify($current_pw, $student['password'])) {
                // I-hash ang bagong password
                $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                
                // I-save sa database
                $update = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
                if ($update->execute([$new_hash, $student_id])) {
                    $msg = "Success! Napalitan na ang iyong password.";
                    $msgType = "success";
                } else {
                    $msg = "System error. Hindi ma-update ang password.";
                    $msgType = "error";
                }
            } else {
                $msg = "Mali ang iyong Current Password.";
                $msgType = "error";
            }
        } catch (PDOException $e) {
            $msg = "Database Error: " . $e->getMessage();
            $msgType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | UMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        :root {
            --bg-color: #eef2ff; --card-bg: #ffffff; --text-color: #1e293b;
            --nav-bg: #ffffff; --primary-color: #4f46e5; --secondary-color: #4338ca; 
            --danger-color: #ef4444; --success-color: #10b981; --border-color: rgba(0,0,0,0.08);
            --input-bg: #f8fafc;
        }

        [data-theme="dark"] {
            --bg-color: #0f172a; --card-bg: #1e293b; --text-color: #f1f5f9;
            --nav-bg: #1e293b; --primary-color: #6366f1; --border-color: rgba(255,255,255,0.08);
            --input-bg: #0f172a;
        }

        body { background-color: var(--bg-color); color: var(--text-color); transition: 0.3s; }
        
        /* Navigation */
        .custom-nav { background: var(--nav-bg); border-bottom: 1px solid var(--border-color); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;}
        .logo-badge { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 8px 18px; border-radius: 8px; font-weight: 900; letter-spacing: 1px;}
        .nav-btn { color: var(--text-color); font-weight: bold; text-decoration: none; font-size: 14px; margin-left: 20px; transition: 0.2s;}
        .nav-btn:hover { color: var(--primary-color); }

        .container { max-width: 500px; margin: 60px auto; padding: 0 20px; }
        
        .card { background: var(--card-bg); border-radius: 15px; padding: 40px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 10px; color: var(--text-color); text-align: center; }
        .card-desc { text-align: center; color: gray; font-size: 14px; margin-bottom: 30px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 8px; color: var(--text-color); }
        .custom-input { width: 100%; padding: 12px 15px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--input-bg); color: var(--text-color); font-size: 15px; outline: none; transition: 0.3s; }
        .custom-input:focus { border-color: var(--primary-color); }

        .submit-btn { background: var(--primary-color); color: white; border: none; width: 100%; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .submit-btn:hover { background: var(--secondary-color); transform: translateY(-2px); }

        .alert { padding: 15px; border-radius: 8px; font-weight: bold; text-align: center; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2); }
        .alert-success { background: rgba(16, 185, 129, 0.1); color: var(--success-color); border: 1px solid rgba(16, 185, 129, 0.2); }
    </style>
</head>
<body>

    <nav class="custom-nav">
        <div>
            <span class="logo-badge">SETTINGS</span>
        </div>
        <div>
            <a href="index.php" class="nav-btn">⬅️ Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div style="text-align: center; font-size: 40px; margin-bottom: 10px;">🔐</div>
            <h2 class="card-title">Change Password</h2>
            <p class="card-desc">Gawing secure ang iyong account. Huwag i-share ang password sa iba.</p>

            <?php if(!empty($msg)): ?>
                <div class="alert alert-<?= $msgType ?>">
                    <?= $msgType === 'success' ? '✅' : '❌' ?> <?= $msg ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="custom-input" required placeholder="I-type ang kasalukuyang password">
                </div>
                
                <div style="height: 1px; background: var(--border-color); margin: 25px 0;"></div>

                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="custom-input" required placeholder="Gumawa ng bagong password">
                </div>
                <div class="input-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="custom-input" required placeholder="I-type ulit ang bagong password">
                </div>

                <button type="submit" class="submit-btn">Update Password</button>
            </form>
        </div>
    </div>

    <script>
        // Kopyahin ang theme (Dark/Light) mula sa Dashboard
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>