<?php
// index.php (Main Landing Page - Ultimate Responsive Edition)
session_start();
require_once 'config/constants.php';

$isLoggedIn = isset($_SESSION['role']);
$dashboardLink = 'modules/auth/login.php';

if($isLoggedIn) {
    if($_SESSION['role'] === 'Admin') $dashboardLink = 'modules/admin/index.php';
    if($_SESSION['role'] === 'Registrar') $dashboardLink = 'modules/registrar/index.php';
    if($_SESSION['role'] === 'Student') $dashboardLink = 'modules/student/index.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>UMS | The Future of Education</title>
    <style>
        /* =========================================================
           1. PURE CSS RESET & MODERN VARIABLES
           ========================================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; }
        
        :root {
            --bg-color: #f8fafc; --text-color: #0f172a; 
            --nav-bg: rgba(255, 255, 255, 0.7);
            --primary: #2980b9; --secondary: #8e44ad; --accent: #00d2ff;
            --card-bg: rgba(255, 255, 255, 0.6); --border-color: rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-color: #020617; --text-color: #f8fafc; 
            --nav-bg: rgba(2, 6, 23, 0.7);
            --primary: #3b82f6; --secondary: #a855f7; --accent: #38bdf8;
            --card-bg: rgba(15, 23, 42, 0.6); --border-color: rgba(255,255,255,0.05);
        }

        body { 
            background-color: var(--bg-color); color: var(--text-color); 
            transition: background-color 0.5s ease, color 0.5s ease;
            overflow-x: hidden; position: relative;
        }

        /* =========================================================
           2. AMBIENT GLOWING ORBS (The Secret to Mobile Beauty)
           ========================================================= */
        .ambient-orbs {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            overflow: hidden; z-index: -1; pointer-events: none;
        }
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            animation: floatOrb 15s infinite alternate ease-in-out;
        }
        .orb-1 { width: 50vw; height: 50vw; background: var(--primary); top: -10%; left: -10%; }
        .orb-2 { width: 40vw; height: 40vw; background: var(--secondary); bottom: -10%; right: -10%; animation-delay: -5s; }
        .orb-3 { width: 30vw; height: 30vw; background: var(--accent); top: 40%; left: 50%; animation-delay: -10s; opacity: 0.3; }

        @keyframes floatOrb {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(10vw, 10vh) scale(1.2); }
        }

        /* =========================================================
           3. FROSTED GLASS NAVBAR
           ========================================================= */
        .navbar {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 5%; background: var(--nav-bg); 
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color); transition: 0.3s;
        }
        
        .logo-group { display: flex; align-items: center; gap: 12px; font-weight: 900; font-size: clamp(1.2rem, 3vw, 1.5rem); letter-spacing: -0.5px; }
        .logo-icon { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            color: white; width: 38px; height: 38px; border-radius: 10px; 
            display: flex; justify-content: center; align-items: center; font-size: 18px; 
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.4); 
        }

        .nav-actions { display: flex; gap: clamp(10px, 2vw, 20px); align-items: center; }
        
        .theme-btn {
            background: transparent; border: 1px solid var(--border-color); color: var(--text-color);
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px;
            transition: 0.3s; display: flex; justify-content: center; align-items: center;
        }
        .theme-btn:hover { background: var(--card-bg); transform: rotate(15deg) scale(1.1); }
        .theme-spin { animation: spin 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes spin { 0% { transform: scale(0.5) rotate(0deg); opacity: 0; } 100% { transform: scale(1) rotate(360deg); opacity: 1; } }

        .login-btn {
            background: var(--text-color); color: var(--bg-color); text-decoration: none; 
            padding: 10px clamp(15px, 3vw, 25px); border-radius: 30px;
            font-weight: 800; font-size: clamp(12px, 2vw, 14px); transition: 0.3s; 
        }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); opacity: 0.9; }

        /* =========================================================
           4. FLUID HERO SECTION (Perfect on all screens)
           ========================================================= */
        .hero {
            min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 120px 5% 50px 5%; position: relative; z-index: 10;
        }
        
        .hero-badge {
            display: inline-block; padding: 8px 18px; background: rgba(142, 68, 173, 0.1);
            color: var(--secondary); border-radius: 30px; font-size: clamp(11px, 2vw, 13px); font-weight: 800;
            margin-bottom: 25px; border: 1px solid rgba(142, 68, 173, 0.2); backdrop-filter: blur(10px);
            animation: slideDown 0.8s ease-out;
        }

        /* FLUID TYPOGRAPHY: Kusang aayon sa screen size! */
        .hero-title {
            font-size: clamp(2.5rem, 8vw, 6rem); 
            font-weight: 900; line-height: 1.05; margin-bottom: 25px;
            background: linear-gradient(to right, var(--text-color), var(--primary), var(--secondary), var(--text-color));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-size: 300% auto; animation: textShine 6s linear infinite, slideUp 1s ease-out;
            max-width: 1000px;
        }
        @keyframes textShine { to { background-position: 300% center; } }

        .hero-subtitle { 
            font-size: clamp(1rem, 2.5vw, 1.25rem); line-height: 1.6; opacity: 0.8; 
            max-width: 600px; margin-bottom: 40px; animation: slideUp 1.2s ease-out;
        }

        .cta-group { display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; animation: slideUp 1.4s ease-out; width: 100%; }
        
        .cta-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; text-decoration: none; padding: 18px clamp(20px, 5vw, 40px);
            border-radius: 30px; font-weight: 800; font-size: clamp(14px, 2.5vw, 16px);
            transition: 0.3s; box-shadow: 0 10px 30px rgba(41, 128, 185, 0.3); 
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            width: fit-content;
        }
        .cta-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 40px rgba(41, 128, 185, 0.5); }

        /* =========================================================
           5. RESPONSIVE GLASS CARDS GRID
           ========================================================= */
        .features { padding: 80px 5%; width: 100%; max-width: 1200px; margin: 0 auto; position: relative; z-index: 10; }
        
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); 
            gap: clamp(15px, 3vw, 30px); 
        }
        
        .glass-card {
            padding: clamp(25px, 5vw, 40px) clamp(20px, 4vw, 30px); 
            border-radius: 24px; border: 1px solid var(--border-color);
            background: var(--card-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1); text-align: left;
        }
        .glass-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); border-color: var(--primary); }
        
        .f-icon { font-size: clamp(30px, 6vw, 40px); margin-bottom: 20px; display: inline-block; padding: 15px; background: rgba(41, 128, 185, 0.1); border-radius: 16px; }
        .glass-card h3 { font-size: clamp(1.2rem, 3vw, 1.5rem); font-weight: 800; margin-bottom: 10px; }
        .glass-card p { opacity: 0.7; line-height: 1.6; font-size: clamp(0.9rem, 2vw, 1rem); }

        /* Animations */
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }

        /* =========================================================
           6. EXTREME MOBILE OPTIMIZATIONS
           ========================================================= */
        @media (max-width: 480px) {
            .navbar { padding: 15px; }
            .theme-btn { width: 35px; height: 35px; font-size: 16px; }
            .hero { padding: 100px 20px 40px 20px; }
            .cta-primary { width: 100%; } /* Gawing full width ang button sa maliliit na phone */
            .orb-1 { filter: blur(50px); } /* Bawasan ang blur sa mobile para mas makulay */
        }
    </style>
</head>
<body>

    <div class="ambient-orbs">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <nav class="navbar">
        <div class="logo-group">
            <div class="logo-icon">U</div>
            UMS
        </div>
        <div class="nav-actions">
            <button class="theme-btn" id="themeBtn" title="Toggle Theme">🌙</button>
            <a href="<?= $dashboardLink ?>" class="login-btn">
                <?= $isLoggedIn ? 'Dashboard' : 'Login ➔' ?>
            </a>
        </div>
    </nav>

    <section class="hero">
        <span class="hero-badge">✨ HAWAK MO ANG BEAT ANO TARA?</span>
        <h1 class="hero-title">The Future of Campus Management.</h1>
        <p class="hero-subtitle">Experience a lightning-fast, highly secure, and beautifully designed unified platform for students, faculty, and administrators.</p>
        
        <div class="cta-group">
            <a href="<?= $dashboardLink ?>" class="cta-primary">
                <?= $isLoggedIn ? 'Access My Dashboard' : 'Enter Secure Portal' ?> <span>➔</span>
            </a>
        </div>
    </section>

    <section class="features">
        <div class="grid">
            <div class="glass-card">
                <div class="f-icon">👨‍🎓</div>
                <h3>Admissions</h3>
                <p>Seamlessly enroll students, manage digital identification, and maintain a highly organized masterlist instantly.</p>
            </div>

            <div class="glass-card">
                <div class="f-icon">💳</div>
                <h3>Finance</h3>
                <p>Generate tuition invoices instantly, process secure payments, and track the university's overall revenue.</p>
            </div>

            <div class="glass-card">
                <div class="f-icon">📊</div>
                <h3>Analytics</h3>
                <p>Export elegant, print-ready reports for enrollment statistics and collections visualized perfectly.</p>
            </div>
        </div>
    </section>

    <script>
        // Theme Switcher Logic
        const html = document.documentElement;
        const themeBtn = document.getElementById('themeBtn');
        let currentTheme = localStorage.getItem('theme') || 'light';
        
        html.setAttribute('data-theme', currentTheme);
        themeBtn.innerHTML = currentTheme === 'dark' ? '☀️' : '🌙';

        themeBtn.addEventListener('click', () => {
            currentTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', currentTheme);
            localStorage.setItem('theme', currentTheme);
            
            themeBtn.classList.remove('theme-spin');
            void themeBtn.offsetWidth; 
            themeBtn.classList.add('theme-spin');
            
            themeBtn.innerHTML = currentTheme === 'dark' ? '☀️' : '🌙';
        });
    </script>

</body>
</html>