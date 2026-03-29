<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php"); exit(); 
}

try {
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY id DESC");
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
include '../../includes/header.php'; 
?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    html, body { width: 100%; max-width: 100%; overflow-x: hidden !important; background-color: var(--bg-color); color: var(--text-color); }

    :root {
        --bg-color: #f4f6f9; --card-bg: #ffffff; --text-color: #2c3e50;
        --nav-bg: #ffffff; --primary-color: #2980b9; --danger-color: #e74c3c;
        --success-color: #27ae60; --shadow-sm: 0 2px 8px rgba(0,0,0,0.05); --border-color: rgba(0,0,0,0.05);
    }
    [data-theme="dark"] {
        --bg-color: #0f172a; --card-bg: #1e293b; --text-color: #f8fafc;
        --nav-bg: #1e293b; --shadow-sm: 0 2px 8px rgba(0,0,0,0.3); --border-color: rgba(255,255,255,0.05);
    }

    /* SMOOTH ANIMATIONS */
    body, .custom-nav, .data-card, .custom-input, .page-header, .modal-box, th, td {
        transition: background-color 0.4s ease, color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
    }

    .main-vanilla-wrapper { display: flex; flex-direction: column; width: 100%; min-height: 100vh; }
    .flex-row { display: flex; align-items: center; } .space-between { justify-content: space-between; } .gap-10 { gap: 10px; } .gap-15 { gap: 15px; }

    .custom-nav { background: var(--nav-bg); border-bottom: 1px solid var(--border-color); padding: 12px 30px; position: sticky; top: 0; z-index: 1000; width: 100%; box-shadow: var(--shadow-sm); }
    .logo-badge { background: var(--primary-color); color: white; padding: 6px 15px; border-radius: 6px; font-weight: 900; letter-spacing: 1px; }

    .nav-btn { background: none; border: none; color: var(--text-color); font-weight: bold; padding: 10px 15px; cursor: pointer; border-radius: 6px; text-decoration: none; font-size: 14px; transition: 0.2s; }
    .nav-btn:hover { background: rgba(41, 128, 185, 0.1); color: var(--primary-color); transform: translateY(-2px); }
    .btn-danger { color: var(--danger-color) !important; }

    /* THEME BUTTON */
    .theme-btn-small { background: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; box-shadow: var(--shadow-sm); transition: all 0.3s ease; }
    .theme-btn-small:hover { transform: translateY(-2px) rotate(15deg) scale(1.1); border-color: var(--primary-color); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .theme-spin { animation: spinIcon 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    @keyframes spinIcon { 0% { transform: rotate(0deg) scale(0.5); opacity: 0; } 100% { transform: rotate(360deg) scale(1); opacity: 1; } }

    .dropdown { position: relative; display: inline-block; }
    .dropdown-content { display: none; position: absolute; top: 100%; left: 0; background-color: var(--card-bg); min-width: 200px; box-shadow: var(--shadow-sm); border-radius: 8px; z-index: 1001; overflow: hidden; border: 1px solid var(--border-color); }
    .dropdown:hover .dropdown-content { display: block; }
    .dropdown-item { color: var(--text-color); padding: 12px 20px; text-decoration: none; display: block; font-weight: 600; font-size: 14px; border-bottom: 1px solid var(--border-color); transition: 0.2s; }
    .dropdown-item:hover { background-color: rgba(41, 128, 185, 0.05); color: var(--primary-color); padding-left: 25px; }

    .page-header { padding: 40px; background: var(--card-bg); border-bottom: 1px solid var(--border-color); }
    .page-title { font-size: 2rem; font-weight: 800; color: var(--text-color); margin-bottom: 5px; }
    .page-subtitle { color: gray; font-size: 1rem; }
    
    .content-area { padding: 30px 40px; width: 100%; flex-grow: 1; }
    .data-card { background: var(--card-bg); border-radius: 12px; padding: 25px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }

    .action-button-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 14px; }
    .action-button-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(41, 128, 185, 0.3); }

    .table-responsive { overflow-x: auto; margin-top: 20px; border-radius: 8px; border: 1px solid var(--border-color); }
    .data-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .data-table th { background: var(--bg-color); font-size: 12px; text-transform: uppercase; color: gray; }
    .data-table tbody tr:hover { background: rgba(0,0,0,0.01); }
    .course-badge { background: rgba(41, 128, 185, 0.1); color: var(--primary-color); padding: 5px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; }

    .custom-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
    .modal-box { background: var(--card-bg); margin: 5% auto; padding: 30px; border-radius: 15px; width: 90%; max-width: 500px; position: relative; animation: slideDown 0.3s; }
    @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .close-btn { position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer; color: gray; transition: 0.2s; }
    .close-btn:hover { color: var(--danger-color); transform: rotate(90deg); }

    .custom-input { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); margin-top: 5px; margin-bottom: 15px; }
    .submit-btn { background: var(--success-color); color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.2s; }
    .submit-btn:hover { background: #219653; transform: translateY(-2px); }
    .submit-btn:disabled { background: gray; cursor: not-allowed; transform: none; }
</style>

<div class="main-vanilla-wrapper">
    <nav class="flex-row space-between custom-nav">
        <div class="flex-row gap-15">
            <span class="logo-badge">UMS ADMIN</span>
            <button class="theme-btn-small" id="themeBtn" title="Toggle Light/Dark Mode">🌙</button>
        </div>
        <div class="flex-row gap-10">
            <a href="index.php" class="nav-btn">🏠 Dashboard</a>
            <div class="dropdown">
                <button class="nav-btn" style="color: var(--primary-color);">⚙️ Management ▾</button>
                <div class="dropdown-content">
                    <a href="students.php" class="dropdown-item">👥 Students</a>
                    <a href="courses.php" class="dropdown-item" style="background: rgba(41, 128, 185, 0.05); color: var(--primary-color);">📚 Courses</a>
                    <a href="finance.php" class="dropdown-item">💰 Finance</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="nav-btn">📊 Reports ▾</button>
                <div class="dropdown-content">
                    <a href="enrollment_report.php" class="dropdown-item">📄 Enrollment</a>
                    <a href="collections_report.php" class="dropdown-item">💵 Collections</a>
                </div>
            </div>
            <a href="../auth/logout.php" class="nav-btn btn-danger">🚪 Sign Out</a>
        </div>
    </nav>

    <div class="page-header flex-row space-between">
        <div>
            <h1 class="page-title">University Courses</h1>
            <p class="page-subtitle">Manage academic programs and courses offered.</p>
        </div>
        <button class="action-button-primary" onclick="openModal('addCourseModal')">➕ Add New Course</button>
    </div>

    <main class="content-area">
        <div class="data-card">
            <h3 style="font-weight: 800; margin-bottom: 15px;">Active Programs</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Program Name</th><th>Status</th><th style="text-align: right;">Action</th></tr></thead>
                    <tbody>
                        <?php if(count($programs) > 0): ?>
                            <?php foreach($programs as $prog): ?>
                                <tr>
                                    <td style="font-weight: bold; color: gray;">#<?= $prog['id'] ?></td>
                                    <td style="font-weight: 600;"><span class="course-badge">🎓 <?= htmlspecialchars($prog['program_name']) ?></span></td>
                                    <td><span style="color: var(--success-color); font-weight: bold; font-size: 12px;">● ACTIVE</span></td>
                                    <td style="text-align: right;">
                                        <button class="nav-btn" style="color: var(--danger-color); padding: 5px 10px;" onclick="alert('Delete course coming soon!')">🗑️ Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px; color: gray;">No courses found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="addCourseModal" class="custom-modal">
    <div class="modal-box">
        <span class="close-btn" onclick="closeModal('addCourseModal')">&times;</span>
        <h2 style="margin-bottom: 20px;">Add New Program</h2>
        <form id="addCourseForm">
            <label style="font-weight: bold; font-size: 13px; color: gray;">Program Name</label>
            <input type="text" name="program_name" id="programNameInput" class="custom-input" required placeholder="e.g. BS Information Technology">
            <button type="submit" class="submit-btn" id="saveCourseBtn">Save Program</button>
            <div id="msgBox" style="margin-top: 15px; text-align: center; font-weight: bold; font-size: 14px;"></div>
        </form>
    </div>
</div>

<script>
    // ADVANCED THEME SWITCHER
    const html = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    let currentTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', currentTheme);
    themeBtn.innerHTML = currentTheme === 'dark' ? '☀️' : '🌙';

    themeBtn.addEventListener('click', () => {
        currentTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', currentTheme); localStorage.setItem('theme', currentTheme);
        themeBtn.classList.remove('theme-spin'); void themeBtn.offsetWidth; themeBtn.classList.add('theme-spin');
        themeBtn.innerHTML = currentTheme === 'dark' ? '☀️' : '🌙';
    });

    // MODAL & AJAX
    function openModal(id) { document.getElementById(id).style.display = "block"; document.body.style.overflow = "hidden"; setTimeout(() => document.getElementById('programNameInput').focus(), 100); }
    function closeModal(id) { document.getElementById(id).style.display = "none"; document.body.style.overflow = "auto"; document.getElementById('addCourseForm').reset(); document.getElementById('msgBox').innerHTML = ""; }

    document.getElementById('addCourseForm').addEventListener('submit', function(e) {
        e.preventDefault(); const btn = document.getElementById('saveCourseBtn'); const msg = document.getElementById('msgBox');
        btn.disabled = true; btn.innerText = "Saving to Database...";
        fetch('process/add_course.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.text()).then(data => {
            if(data.trim() === "success") { msg.style.color = "var(--success-color)"; msg.innerHTML = "✅ SUCCESS!"; setTimeout(() => location.reload(), 1200); } 
            else { msg.style.color = "var(--danger-color)"; msg.innerHTML = "❌ " + data; btn.disabled = false; btn.innerText = "Save Program"; }
        });
    });

    window.onclick = function(e) { if(e.target.className === 'custom-modal') closeModal(e.target.id); }
                            
    

</script>
<?php include '../../includes/footer.php'; ?>