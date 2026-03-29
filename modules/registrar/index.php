<?php
// modules/registrar/index.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check: Registrar lang ang pwedeng pumasok
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Registrar only.</h2></div>");
}

try {
    // Kunin ang Masterlist ng mga Estudyante
    $stmt_students = $pdo->query("
        SELECT s.*, c.course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        ORDER BY s.last_name ASC
    ");
    $students = $stmt_students->fetchAll();

    // Stats
    $total_students = count($students);
    $active_students = 0;
    foreach($students as $s) { if($s['status'] === 'Active') $active_students++; }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Portal | UMS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Premium Registrar Theme (Crimson/Maroon) */
        :root {
            --bg-color: #fff1f2; --card-bg: #ffffff; --text-color: #1e293b;
            --nav-bg: #ffffff; --primary-color: #e11d48; /* Crimson */
            --secondary-color: #be123c; --danger-color: #ef4444; 
            --border-color: rgba(0,0,0,0.08); --shadow-sm: 0 4px 15px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a; --card-bg: #1e293b; --text-color: #f1f5f9;
            --nav-bg: #1e293b; --primary-color: #fb7185; --border-color: rgba(255,255,255,0.08);
        }

        body { background-color: var(--bg-color); color: var(--text-color); transition: 0.3s; }
        
        /* Navigation */
        .custom-nav { background: var(--nav-bg); border-bottom: 1px solid var(--border-color); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); position: sticky; top: 0; z-index: 100;}
        .logo-badge { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 8px 18px; border-radius: 8px; font-weight: 900; letter-spacing: 1px;}
        .nav-btn { color: var(--text-color); font-weight: bold; text-decoration: none; font-size: 14px; margin-left: 20px; transition: 0.2s;}
        .nav-btn:hover { color: var(--primary-color); }
        .theme-btn-small { background: rgba(0,0,0,0.04); border: 1px solid var(--border-color); color: var(--text-color); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: 0.3s; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Header Banner */
        .welcome-banner { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 15px; padding: 35px 40px; color: white; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(225, 29, 72, 0.3); }
        .welcome-text h1 { font-size: 2.2rem; font-weight: 900; margin-bottom: 5px; }
        .stats-box { text-align: right; }
        .stats-num { font-size: 2rem; font-weight: 900; }

        /* Masterlist Table */
        .portal-card { background: var(--card-bg); border-radius: 15px; padding: 30px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
        .card-title { font-size: 1.3rem; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 15px;}
        
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background: rgba(0,0,0,0.02); font-size: 12px; text-transform: uppercase; color: gray; }
        [data-theme="dark"] .data-table th { background: rgba(255,255,255,0.02); }

        .status-badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-inactive { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }

        .btn-action { background: var(--bg-color); color: var(--primary-color); border: 1px solid var(--primary-color); padding: 8px 12px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 12px; text-decoration: none; display: inline-block; margin-right: 5px;}
        .btn-action:hover { background: var(--primary-color); color: white; }
    </style>
</head>
<body>

    <nav class="custom-nav">
        <div style="display: flex; gap: 15px; align-items: center;">
            <span class="logo-badge">REGISTRAR</span>
            <button class="theme-btn-small" id="regThemeBtn" onclick="toggleRegTheme()">🌙</button>
        </div>
        <div>
            <a href="#" class="nav-btn" style="color: var(--primary-color);">📁 Student Records</a>
            <a href="../../modules/auth/logout.php" class="nav-btn" style="color: var(--danger-color);">🚪 Sign Out</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>! 📂</h1>
                <p>Manage student records, COR, and TOR documents.</p>
            </div>
            <div class="stats-box">
                <div style="font-size: 13px; font-weight: bold; opacity: 0.9;">TOTAL ENROLLEES</div>
                <div class="stats-num"><?= $total_students ?></div>
            </div>
        </div>

        <div class="portal-card">
            <h3 class="card-title">📋 Student Masterlist</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Program/Course</th>
                            <th>Status</th>
                            <th>Documents</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($students) > 0): ?>
                            <?php foreach($students as $s): ?>
                                <tr>
                                    <td style="font-weight: bold; color: gray;"><?= htmlspecialchars($s['student_no']) ?></td>
                                    <td style="font-weight: 800; font-size: 15px;"><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></td>
                                    <td style="font-weight: 600; color: var(--primary-color);"><?= htmlspecialchars($s['course_code'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="status-badge <?= $s['status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
                                            <?= htmlspecialchars($s['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="print_cor.php?id=<?= $s['id'] ?>" class="btn-action">📄 Print COR</a>
                                        <a href="#" onclick="alert('Printing TOR for <?= htmlspecialchars($s['first_name']) ?>. Module next!')" class="btn-action" style="border-color: gray; color: gray;">📜 Print TOR</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: gray; padding: 30px;">No students registered in the system yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleRegTheme() {
            const html = document.documentElement;
            const btn = document.getElementById('regThemeBtn');
            let isDark = html.getAttribute('data-theme') === 'dark';
            let newTheme = isDark ? 'light' : 'dark';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            if (btn) btn.innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
        }

        document.addEventListener('DOMContentLoaded', () => {
            let savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const btn = document.getElementById('regThemeBtn');
            if (btn) btn.innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
        });
    </script>
</body>
</html>