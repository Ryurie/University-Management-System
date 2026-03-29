<?php
// modules/admin/notifications.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // Kunin ang lahat ng past announcements
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $db_error = "Database Error: " . $e->getMessage();
}

include '../../includes/header.php'; 
?>

<style>
    /* Premium UI Theme */
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    html, body { width: 100%; max-width: 100%; overflow-x: hidden !important; }

    :root {
        --bg-color: #f4f6f9; --card-bg: #ffffff; --text-color: #2c3e50;
        --nav-bg: #ffffff; --primary-color: #e67e22; /* Orange for Alerts/Notifications */
        --secondary-color: #d35400; --danger-color: #e74c3c; --success-color: #27ae60;
        --border-color: rgba(0,0,0,0.08); --shadow-sm: 0 4px 15px rgba(0,0,0,0.05);
        --modal-overlay: rgba(0,0,0,0.5);
    }

    [data-theme="dark"] {
        --bg-color: #0b0e14 !important; --card-bg: #1c1f26 !important; --text-color: #e4e6eb !important;
        --nav-bg: #1c1f26 !important; --primary-color: #f39c12 !important;
        --border-color: rgba(255,255,255,0.08) !important; --modal-overlay: rgba(0,0,0,0.8) !important;
    }

    body { background-color: var(--bg-color) !important; color: var(--text-color) !important; transition: 0.4s ease; }
    .custom-nav, .card, .modal-content, .floating-input, .floating-select, .notif-card { transition: 0.4s ease; }

    /* Navigation */
    .custom-nav { background: var(--nav-bg) !important; border-bottom: 1px solid var(--border-color) !important; padding: 12px 30px; position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm) !important; }
    .logo-badge { background: var(--primary-color); color: white; padding: 6px 15px; border-radius: 6px; font-weight: 900; letter-spacing: 1px; }
    .nav-btn { background: none; border: none; color: var(--text-color) !important; font-weight: bold; padding: 10px 15px; cursor: pointer; border-radius: 6px; text-decoration: none; font-size: 14px; transition: 0.2s; }
    .nav-btn:hover { background: rgba(230, 126, 34, 0.1); color: var(--primary-color) !important; }
    .theme-btn-small { background: rgba(0,0,0,0.02); border: 1px solid var(--border-color) !important; color: var(--text-color) !important; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: 0.3s; }

    .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px;}
    .btn-primary:hover { background: var(--secondary-color); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3); }

    /* Notification Cards */
    .notif-list { display: flex; flex-direction: column; gap: 15px; }
    .notif-card { background: var(--card-bg) !important; border: 1px solid var(--border-color) !important; border-radius: 12px; padding: 25px; box-shadow: var(--shadow-sm) !important; display: flex; flex-direction: column; gap: 10px; border-left: 5px solid var(--primary-color); }
    .notif-header { display: flex; justify-content: space-between; align-items: center; }
    .notif-title { font-size: 1.2rem; font-weight: 800; color: var(--primary-color); }
    .notif-meta { font-size: 12px; color: gray; font-weight: 600; }
    .notif-body { font-size: 14px; line-height: 1.6; }
    
    .audience-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; background: rgba(0,0,0,0.05); color: var(--text-color); }

    /* Modal */
    .custom-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: var(--modal-overlay) !important; backdrop-filter: blur(5px); justify-content: center; align-items: center;}
    .modal-content { background: var(--card-bg) !important; padding: 40px; border-radius: 15px; width: 95%; max-width: 500px; position: relative; border: 1px solid var(--border-color) !important;}
    .floating-group { position: relative; margin-bottom: 20px; }
    .floating-input, .floating-select, .floating-textarea { width: 100%; padding: 14px; border: 2px solid var(--border-color) !important; border-radius: 8px; background: transparent; color: var(--text-color) !important; font-size: 15px; outline: none; transition: 0.3s; }
    .floating-textarea { resize: vertical; min-height: 120px; }
    .floating-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; color: gray; }
    .floating-input:focus, .floating-select:focus, .floating-textarea:focus { border-color: var(--primary-color) !important; }
</style>

<nav class="custom-nav">
    <div style="display: flex; gap: 10px; align-items: center;">
        <span class="logo-badge">ALERTS</span>
        <button class="theme-btn-small" id="themeBtn" onclick="toggleTheme()">🌙</button>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php" class="nav-btn" style="color: var(--danger-color) !important;">⬅️ Admin Dashboard</a>
    </div>
</nav>

<div class="container">
    <?php if(isset($db_error)): ?>
        <div style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $db_error ?>
        </div>
    <?php endif; ?>

    <div class="header-flex">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 800;">System Announcements</h1>
            <p style="color: gray;">Broadcast messages to students and faculty.</p>
        </div>
        <button class="btn-primary" onclick="openModal('notifModal')">📢 New Announcement</button>
    </div>

    <div class="notif-list">
        <?php if(isset($notifications) && count($notifications) > 0): ?>
            <?php foreach($notifications as $n): ?>
                <div class="notif-card">
                    <div class="notif-header">
                        <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
                        <div class="notif-meta"><?= date('M d, Y • h:i A', strtotime($n['created_at'])) ?></div>
                    </div>
                    <div><span class="audience-badge">To: <?= htmlspecialchars($n['target_audience']) ?></span></div>
                    <div class="notif-body">
                        <?= nl2br(htmlspecialchars($n['message'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; color: gray; padding: 40px; border: 1px dashed var(--border-color); border-radius: 12px;">
                No announcements broadcasted yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="notifModal" class="custom-modal">
    <div class="modal-content">
        <button onclick="closeModal('notifModal')" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; background: none; border: none; color: gray;">&times;</button>
        <h2 style="margin-bottom: 25px;">Create Announcement</h2>

        <form id="notifForm">
            <div class="floating-group">
                <label class="floating-label">Target Audience</label>
                <select name="target_audience" class="floating-select" required>
                    <option value="All">Everyone (Students & Faculty)</option>
                    <option value="Students">Students Only</option>
                    <option value="Faculty">Faculty Only</option>
                </select>
            </div>

            <div class="floating-group">
                <label class="floating-label">Subject / Title</label>
                <input type="text" name="title" class="floating-input" placeholder="e.g. Midterm Examination Schedule" required>
            </div>

            <div class="floating-group">
                <label class="floating-label">Message Details</label>
                <textarea name="message" class="floating-textarea" placeholder="Type your full announcement here..." required></textarea>
            </div>
            
            <button type="submit" class="btn-primary" id="saveNotifBtn" style="width: 100%; padding: 15px;">Broadcast Message ➔</button>
            <div id="msgBox" style="margin-top: 15px; text-align: center; font-weight: bold; font-size: 14px;"></div>
        </form>
    </div>
</div>

<script>
    function toggleTheme() {
        const html = document.documentElement;
        let isDark = html.getAttribute('data-theme') === 'dark';
        let newTheme = isDark ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        document.getElementById('themeBtn').innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
    }

    document.addEventListener('DOMContentLoaded', () => {
        let savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('themeBtn').innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
    });

    function openModal(id) { document.getElementById(id).style.display = "flex"; }
    function closeModal(id) { document.getElementById(id).style.display = "none"; }

    // AJAX Submission
    document.getElementById('notifForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveNotifBtn');
        const msg = document.getElementById('msgBox');
        
        btn.disabled = true; btn.innerText = "Sending Broadcast...";

        fetch('process/send_notification.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.text())
        .then(data => { 
            if(data.trim() === "success") {
                msg.style.color = "var(--success-color)"; msg.innerHTML = "✅ Announcement Sent!";
                setTimeout(() => location.reload(), 1500);
            } else {
                msg.style.color = "var(--danger-color)"; msg.innerHTML = "❌ " + data;
                btn.disabled = false; btn.innerText = "Broadcast Message ➔";
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>send_notification.php