<?php
// modules/academic/courses.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Administrator Portal only.</h2></div>");
}

$msg = '';
$msgType = '';
$courses_list = [];

// --- 1. ADD COURSE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);

    try {
        $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
        if ($stmt->execute([$course_code, $course_name])) {
            $msg = "Success! Naidagdag na ang programang " . htmlspecialchars($course_code) . ".";
            $msgType = "success";
        }
    } catch (PDOException $e) {
        $msg = "Error: Baka existing na ang Course Code na ito. System Info: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 2. FETCH COURSES DATA ---
try {
    $courses_list = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $msg = "Database Error: " . $e->getMessage();
    $msgType = "error";
}

// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER HEADER DITO
// ==========================================
include '../../includes/header.php'; 
?>

<?php if(!empty($msg)): ?>
    <div style="max-width: 1200px; margin: 20px auto 0 auto; padding: 0 20px;">
        <div style="padding: 15px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; <?= $msgType === 'success' ? 'background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);' : 'background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);' ?>">
            <?= $msgType === 'success' ? '✅' : '❌' ?> <?= $msg ?>
        </div>
    </div>
<?php endif; ?>

<div class="container split-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    
    <div class="card" style="height: fit-content;">
        <h3 class="card-title">➕ Add New Program</h3>
        <form method="POST" action="">
            <div class="input-group">
                <label>Course Code (e.g. BSCS)</label>
                <input type="text" name="course_code" class="custom-input" required placeholder="Enter code...">
            </div>
            <div class="input-group">
                <label>Descriptive Title</label>
                <input type="text" name="course_name" class="custom-input" required placeholder="e.g. Bachelor of Science in...">
            </div>
            <button type="submit" name="add_course" class="btn-primary">Save Course</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">📚 Official Course Directory</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Code</th>
                        <th>Descriptive Title</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($courses_list) > 0): ?>
                        <?php foreach($courses_list as $co): ?>
                            <tr>
                                <td style="color: gray; font-size: 12px;">#<?= htmlspecialchars($co['id']) ?></td>
                                <td style="font-weight: bold; color: var(--primary-color);">
                                    <?= htmlspecialchars($co['course_code']) ?>
                                </td>
                                <td><?= htmlspecialchars($co['course_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; padding: 40px; color: gray;">Walang naka-register na course. Mag-add na!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER FOOTER DITO
// ==========================================
include '../../includes/footer.php'; 
?>