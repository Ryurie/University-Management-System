<?php
// modules/academic/enrollment.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // Kunin ang lahat ng active students
    $students = $pdo->query("SELECT id, first_name, last_name, student_number FROM students ORDER BY last_name ASC")->fetchAll();
    
    // Kunin ang lahat ng open classes/sections
    $classes = $pdo->query("
        SELECT cls.id, cls.section_name, c.course_code, c.course_name, f.last_name as prof 
        FROM classes cls 
        JOIN courses c ON cls.course_id = c.id 
        JOIN faculties f ON cls.faculty_id = f.id
    ")->fetchAll();

    $total_enrolled = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include '../../includes/header.php'; 
?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    :root {
        --bg-color: #f4f6f9; --card-bg: #ffffff; --text-color: #2c3e50;
        --primary-color: #8e44ad; --border-color: rgba(0,0,0,0.08);
        --shadow-sm: 0 4px 15px rgba(0,0,0,0.05);
    }
    [data-theme="dark"] {
        --bg-color: #0b0e14 !important; --card-bg: #1c1f26 !important; --text-color: #e4e6eb !important;
        --primary-color: #9b59b6 !important; --border-color: rgba(255,255,255,0.08) !important;
    }
    body { background-color: var(--bg-color) !important; color: var(--text-color) !important; transition: 0.4s; }
    
    /* Navigation */
    .custom-nav { background: var(--nav-bg) !important; border-bottom: 1px solid var(--border-color) !important; padding: 12px 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1000; box-shadow: var(--shadow-sm) !important;}
    .nav-menu { display: flex; align-items: center; gap: 10px; }
    .nav-btn { background: none; border: none; color: var(--text-color) !important; font-weight: bold; padding: 10px 15px; cursor: pointer; border-radius: 6px; text-decoration: none; font-size: 14px; transition: 0.2s; }
    .nav-btn:hover { background: rgba(142, 68, 173, 0.1); color: var(--primary-color) !important; }
    
    /* Layout */
    .page-header { padding: 40px; background: var(--card-bg) !important; border-bottom: 1px solid var(--border-color) !important; display: flex; justify-content: space-between; align-items: center; }
    .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; padding: 30px 40px; }
    .enroll-card { background: var(--card-bg) !important; padding: 30px; border-radius: 20px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; height: fit-content; }
    
    /* Form Styles */
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 13px; color: gray; text-transform: uppercase; }
    select, input { width: 100%; padding: 15px; border-radius: 10px; border: 2px solid var(--border-color) !important; background: var(--bg-color) !important; color: var(--text-color) !important; outline: none; font-weight: 600; }
    .enroll-btn { width: 100%; padding: 18px; background: var(--primary-color); color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
    .enroll-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(142, 68, 173, 0.3); }

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { text-align: left; padding: 15px; background: rgba(0,0,0,0.02); color: gray; font-size: 12px; text-transform: uppercase; }
    .data-table td { padding: 15px; border-bottom: 1px solid var(--border-color) !important; }

    @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }
</style>

<div class="main-vanilla-wrapper">
    <nav class="custom-nav">
        <div class="flex-row gap-15">
            <span style="background: var(--primary-color); color: white; padding: 6px 15px; border-radius: 6px; font-weight: 900;">ACADEMIC</span>
        </div>
        <div class="nav-menu">
            <a href="courses.php" class="nav-btn">📚 Courses</a>
            <a href="classes.php" class="nav-btn">📅 Classes</a>
            <a href="enrollment.php" class="nav-btn" style="color: var(--primary-color) !important;">✍️ Enrollment</a>
            <a href="../admin/index.php" class="nav-btn" style="color: #e74c3c !important;">⬅️ Admin</a>
        </div>
    </nav>

    <div class="page-header">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 800;">Student Enrollment</h1>
            <p style="color: gray;">Register students to their respective class sections.</p>
        </div>
        <div style="font-size: 45px;">✍️</div>
    </div>

    <div class="dashboard-grid">
        <div class="enroll-card">
            <h3 style="margin-bottom: 20px;">New Registration</h3>
            <form id="enrollForm">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" required>
                        <option value="" disabled selected>Search Student...</option>
                        <?php foreach($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['last_name'] ?>, <?= $s['first_name'] ?> (<?= $s['student_number'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Class / Section</label>
                    <select name="class_id" required>
                        <option value="" disabled selected>Search Class Section...</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['section_name'] ?> - <?= $c['course_code'] ?> (Prof. <?= $c['prof'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="enroll-btn" id="enrollBtn">Enroll Student ➔</button>
                <div id="msgBox" style="margin-top: 15px; text-align: center; font-weight: bold; font-size: 14px;"></div>
            </form>
        </div>

        <div class="enroll-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 30px 30px 10px;">
                <h3>Recent Enrollments</h3>
                <p style="color: gray; font-size: 14px; margin-bottom: 20px;">Total: <?= $total_enrolled ?> records</p>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr><th>Student</th><th>Class Section</th><th>Date</th></tr>
                    </thead>
                    <tbody id="enrollment-list">
                        <tr><td colspan="3" style="text-align: center; padding: 40px; color: gray;">Loading records...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. AJAX FETCH ENROLLMENTS
    function fetchEnrollments() {
        fetch('process/fetch_enrollments.php')
        .then(res => res.text())
        .then(data => { document.getElementById('enrollment-list').innerHTML = data; });
    }

    // 2. AJAX SUBMIT ENROLLMENT
    document.getElementById('enrollForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('enrollBtn');
        const msg = document.getElementById('msgBox');
        btn.disabled = true; btn.innerText = "Processing...";

        fetch('process/add_enrollment.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "success") {
                msg.style.color = "#27ae60";
                msg.innerHTML = "✅ SUCCESS: Student Enrolled!";
                setTimeout(() => location.reload(), 1500);
            } else {
                msg.style.color = "#e74c3c";
                msg.innerHTML = "❌ " + data;
                btn.disabled = false; btn.innerText = "Enroll Student ➔";
            }
        });
    });

    document.addEventListener('DOMContentLoaded', fetchEnrollments);
</script>

<?php include '../../includes/footer.php'; ?>