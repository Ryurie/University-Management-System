<?php
// includes/header.php
$user_role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMS | BENRU's NETWORK</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { width: 100%; max-width: 100%; overflow-x: hidden !important; }
        ::-webkit-scrollbar { display: none; }
        html, body { scrollbar-width: none; -ms-overflow-style: none; }

        :root {
            --bg-color: #f8fafc; --card-bg: #ffffff; --text-color: #1e293b;
            --nav-bg: #ffffff; --primary-color: #3b82f6; --danger-color: #ef4444;
            --success-color: #10b981; --border-color: rgba(0,0,0,0.06);
            --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.05); --shadow-md: 0 10px 25px rgba(0,0,0,0.08);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a !important; --card-bg: #1e293b !important; --text-color: #f1f5f9 !important;
            --nav-bg: #1e293b !important; --border-color: rgba(255,255,255,0.08) !important;
            --shadow-sm: 0 4px 15px rgba(0,0,0,0.2) !important; --shadow-md: 0 10px 25px rgba(0,0,0,0.4) !important;
        }

        body { background-color: var(--bg-color) !important; color: var(--text-color) !important; transition: 0.3s ease; }
        .main-vanilla-wrapper { display: flex; flex-direction: column; width: 100%; min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; width: 100%; }

        /* Navigation */
        .custom-nav { background: var(--nav-bg) !important; border-bottom: 1px solid var(--border-color) !important; padding: 15px 40px; position: sticky; top: 0; z-index: 1000; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm) !important;}
        .logo-badge { background: linear-gradient(135deg, var(--primary-color), #8b5cf6); color: white; padding: 8px 18px; border-radius: 8px; font-weight: 800; letter-spacing: 1px; font-size: 14px;}
        
        .desktop-menu { display: flex; align-items: center; gap: 10px; }
        .nav-btn { background: transparent; border: none; color: var(--text-color) !important; font-weight: 600; padding: 10px 15px; cursor: pointer; border-radius: 8px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
        .nav-btn:hover { background: rgba(59, 130, 246, 0.08); color: var(--primary-color) !important; }
        
        .dropdown { position: relative; }
        .dropdown-content { display: none; position: absolute; top: 100%; right: 0; background: var(--card-bg) !important; min-width: 200px; box-shadow: var(--shadow-md) !important; border-radius: 12px; z-index: 2001; overflow: hidden; border: 1px solid var(--border-color) !important; margin-top: 5px; }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown-item { color: var(--text-color) !important; padding: 12px 20px; text-decoration: none; display: block; font-weight: 500; font-size: 14px; transition: 0.2s;}
        .dropdown-item:hover { background: rgba(59, 130, 246, 0.08); color: var(--primary-color) !important; padding-left: 25px; }

        .theme-btn-small { background: rgba(0,0,0,0.04); border: 1px solid var(--border-color) !important; color: var(--text-color) !important; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: 0.3s; margin-left: 10px;}
        .theme-btn-small:hover { transform: translateY(-2px); border-color: var(--primary-color) !important; }
        
        .hamburger-btn { display: none; background: none; border: none; font-size: 24px; color: var(--text-color); cursor: pointer; }
        .sidebar-overlay, .sidebar { display: none; }

        /* UI Components */
        .card { background: var(--card-bg) !important; border-radius: 15px; padding: 25px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; margin-bottom: 20px;}
        .card-title { font-size: 1.2rem; font-weight: 800; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;}
        .profile-banner { background: var(--card-bg); border-radius: 15px; padding: 30px 40px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);}
        .page-title { font-size: 2rem; font-weight: 900; margin-bottom: 5px; }
        
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 8px; color: gray; }
        .custom-input, .custom-select { width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 8px; background: var(--bg-color); color: var(--text-color); font-size: 14px; outline: none; transition: 0.3s;}
        .custom-input:focus, .custom-select:focus { border-color: var(--primary-color); }
        .btn-primary { background: var(--primary-color); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.3s; width: 100%; margin-top: 10px;}
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        .data-table th { background: rgba(0,0,0,0.02); font-size: 12px; text-transform: uppercase; color: gray; }
        [data-theme="dark"] .data-table th { background: rgba(255,255,255,0.02); }

        @media (max-width: 768px) {
            .desktop-menu { display: none; }
            .hamburger-btn { display: block; }
            
            .custom-nav { display: flex !important; flex-direction: row !important; justify-content: space-between !important; align-items: center !important; padding: 15px 20px !important; }
            .custom-nav > div:first-child { width: auto !important; border-bottom: none !important; padding-bottom: 0 !important; margin-bottom: 0 !important; }
            .theme-btn-small { margin-left: auto !important; margin-top: 0 !important; }

            .sidebar-overlay { display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; opacity: 0; visibility: hidden; transition: 0.3s ease; backdrop-filter: blur(2px); }
            .sidebar { display: block; position: fixed; top: 0; left: -300px; width: 280px; height: 100%; background: var(--card-bg); z-index: 2005; box-shadow: 5px 0 25px rgba(0,0,0,0.2); transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; padding-bottom: 20px; }
            .sidebar.active { left: 0; }
            .sidebar-overlay.active { opacity: 1; visibility: visible; }

            .sidebar-header { padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;}
            .close-btn { background: rgba(239, 68, 68, 0.1); border: none; font-size: 18px; color: var(--danger-color); cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;}
            
            .sidebar .nav-btn { width: calc(100% - 30px); margin: 5px 15px; padding: 12px 15px; border-radius: 8px; font-size: 14px; justify-content: flex-start;}
            .sidebar .dropdown { width: 100%; }
            .sidebar .dropdown-content { position: static; display: block; width: calc(100% - 30px); margin: 0 15px; box-shadow: none; border: none; border-left: 3px solid var(--primary-color); border-radius: 0 8px 8px 0; background: rgba(0,0,0,0.02); }
            [data-theme="dark"] .sidebar .dropdown-content { background: rgba(255,255,255,0.02); }
            .sidebar .dropdown-item { padding: 10px 15px; font-size: 13px; opacity: 0.8; }
            
            .split-grid { grid-template-columns: 1fr !important; }
            .profile-banner { flex-direction: column; text-align: center; gap: 15px; padding: 25px 15px; }
            .page-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="main-vanilla-wrapper">
    <nav class="custom-nav">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="hamburger-btn" onclick="toggleSidebar()">☰</button>
            <span class="logo-badge">UMS <?= strtoupper($user_role) ?></span>
        </div>

        <div class="desktop-menu">
            <?php if($user_role === 'Admin'): ?>
                <a href="../admin/index.php" class="nav-btn">🏠 Dashboard</a>
                <div class="dropdown">
                    <button class="nav-btn">📖 Academic ▾</button>
                    <div class="dropdown-content">
                        <a href="../academic/courses.php" class="dropdown-item">📚 Courses</a>
                        <a href="../academic/classes.php" class="dropdown-item">📅 Classes</a>
                        <a href="../academic/grades.php" class="dropdown-item">💯 Enrollment</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="nav-btn">⚙️ Management ▾</button>
                    <div class="dropdown-content">
                        <a href="../admin/students.php" class="dropdown-item">👥 Students</a>
                        <a href="../admin/teachers.php" class="dropdown-item">👨‍🏫 Teachers</a>
                        <a href="../admin/finance.php" class="dropdown-item">💰 Finance</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php" class="nav-btn">🏠 My Dashboard</a>
            <?php endif; ?>
            <a href="../auth/logout.php" class="nav-btn" style="color: var(--danger-color) !important;">🚪 Sign Out</a>
        </div>

        <button class="theme-btn-small" id="adminThemeBtn" onclick="toggleAdminTheme()">🌙</button>
    </nav>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <div class="sidebar" id="mobileSidebar">
        <div class="sidebar-header">
            <span class="logo-badge">MENU</span>
            <button class="close-btn" onclick="toggleSidebar()">✖</button>
        </div>
        
        <?php if($user_role === 'Admin'): ?>
            <a href="../admin/index.php" class="nav-btn" style="color: var(--primary-color) !important; background: rgba(59, 130, 246, 0.1);">🏠 Dashboard</a>
            <div style="padding: 15px 15px 5px 15px; font-size: 11px; font-weight: bold; color: gray; text-transform: uppercase;">Academic Records</div>
            <div class="dropdown">
                <div class="dropdown-content">
                    <a href="../academic/courses.php" class="dropdown-item">📚 Courses & Programs</a>
                    <a href="../academic/classes.php" class="dropdown-item">📅 Classes & Schedule</a>
                    <a href="../academic/grades.php" class="dropdown-item">💯 Grades & Enrollment</a>
                </div>
            </div>
            <div style="padding: 15px 15px 5px 15px; font-size: 11px; font-weight: bold; color: gray; text-transform: uppercase;">System Management</div>
            <div class="dropdown">
                <div class="dropdown-content">
                    <a href="../admin/students.php" class="dropdown-item">👥 Students Directory</a>
                    <a href="../admin/teachers.php" class="dropdown-item">👨‍🏫 Faculty Roster</a>
                    <a href="../admin/finance.php" class="dropdown-item">💰 Finance / Cashier</a>
                </div>
            </div>
        <?php else: ?>
            <a href="index.php" class="nav-btn" style="color: var(--primary-color) !important; background: rgba(59, 130, 246, 0.1);">🏠 My Dashboard</a>
        <?php endif; ?>

        <div style="margin-top: 20px; border-top: 1px solid var(--border-color); padding-top: 10px;">
            <a href="../auth/logout.php" class="nav-btn" style="color: var(--danger-color) !important;">🚪 Sign Out</a>
        </div>
    </div>