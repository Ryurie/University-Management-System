<?php
// modules/admin/students.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Administrator Portal only.</h2></div>");
}

$msg = '';
$msgType = '';
$students_list = [];
$courses = [];

// --- 1. ADD STUDENT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    try {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $course_id = $_POST['course_id'];

        $year = date('Y');
        $count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() + 1;
        $student_no = $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $hashed_password = password_hash($last_name, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_no, first_name, last_name, email, course_id, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->execute([$student_no, $first_name, $last_name, $email, $course_id, $hashed_password]);

            $msg = "Success! Na-register na si {$first_name} {$last_name}. (ID: {$student_no})";
            $msgType = "success";

        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'student_number') !== false) {
                $stmt2 = $pdo->prepare("INSERT INTO students (student_no, student_number, first_name, last_name, email, course_id, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
                $stmt2->execute([$student_no, $student_no, $first_name, $last_name, $email, $course_id, $hashed_password]);

                $msg = "Success! Na-register na si {$first_name} {$last_name}. (ID: {$student_no})";
                $msgType = "success";
            } else {
                throw $e;
            }
        }
    } catch (PDOException $e) {
        $msg = "Database Insert Error: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 2. UPDATE STATUS LOGIC (DROP / RE-ACTIVATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $target_id = $_POST['student_id'];
    $new_status = $_POST['new_status'];

    try {
        $stmt = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $target_id]);
        $msg = "Student status updated to " . $new_status . "!";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error updating status: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 3. KUNIN ANG DATA PARA SA UI ---
try {
    $courses = $pdo->query("SELECT id, course_code, course_name FROM courses")->fetchAll();

    $students_list = $pdo->query("
        SELECT s.*, c.course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        ORDER BY s.id DESC
    ")->fetchAll();
} catch (PDOException $e) {
    if (empty($msg)) {
        $msg = "Error loading data: " . $e->getMessage();
        $msgType = "error";
    }
}
include '../../includes/header.php';
?>

    <?php if (!empty($msg)): ?>
        <div style="max-width: 1200px; margin: 20px auto 0 auto; padding: 0 20px;">
            <div class="alert alert-<?= $msgType ?>">
                <?= $msgType === 'success' ? '✅' : '❌' ?>     <?= $msg ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="card" style="height: fit-content;">
            <h3 class="card-title">➕ Register New Student</h3>
            <form method="POST" action="">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="custom-input" required placeholder="e.g. Juan">
                </div>
                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="custom-input" required placeholder="e.g. Dela Cruz">
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="custom-input" required placeholder="juan@university.edu">
                </div>
                <div class="input-group">
                    <label>Program / Course</label>
                    <select name="course_id" class="custom-select" required>
                        <option value="" disabled selected>-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="font-size: 12px; color: gray; margin-bottom: 15px; text-align: center;">
                    ℹ️ Default password will be the student's Last Name.
                </div>
                <button type="submit" name="add_student" class="btn-primary">Register Student</button>
            </form>
        </div>

        <div class="card">
            <h3 class="card-title">👥 Enrolled Students Directory</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students_list) > 0): ?>
                            <?php foreach ($students_list as $s):
                                $badge_class = ($s['status'] === 'Active') ? 'badge' : 'badge badge-dropped';
                                ?>
                                <tr>
                                    <td style="font-weight: bold; color: var(--primary-color);">
                                        <?= htmlspecialchars($s['student_no']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: bold;">
                                            <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                        <div style="font-size: 12px; color: gray;">
                                            <?= htmlspecialchars($s['course_code'] ?? 'None') ?></div>
                                    </td>
                                    <td>
                                        <span class="<?= $badge_class ?>"><?= htmlspecialchars($s['status']) ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="student_id" value="<?= $s['id'] ?>">

                                            <?php if ($s['status'] === 'Active'): ?>
                                                <input type="hidden" name="new_status" value="Dropped">
                                                <button type="submit" name="update_status" class="btn-action-danger"
                                                    onclick="return confirm('Sigurado ka bang i-do-drop ang estudyanteng ito? Hindi siya makakapag-login kapag dropped na siya.');">Drop</button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_status" value="Active">
                                                <button type="submit" name="update_status"
                                                    class="btn-action-success">Re-activate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: gray;">No students
                                    registered yet.</td>
                            </tr>
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