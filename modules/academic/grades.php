<?php
// modules/academic/grades.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Administrator Portal only.</h2></div>");
}

$msg = '';
$msgType = '';

// --- 1. ENROLL STUDENT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];

    try {
        // I-check muna kung enrolled na siya sa klaseng ito para iwas duplicate
        $check = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?");
        $check->execute([$student_id, $class_id]);
        
        if ($check->rowCount() > 0) {
            $msg = "Error: Enrolled na ang estudyante sa klaseng ito!";
            $msgType = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, class_id, grade, status) VALUES (?, ?, 'Pending', 'Enrolled')");
            $stmt->execute([$student_id, $class_id]);
            $msg = "Success! Na-enroll na ang estudyante sa klase.";
            $msgType = "success";
        }
    } catch (PDOException $e) {
        $msg = "Database Error: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 2. ENCODE / UPDATE GRADE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_grade'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $grade = trim($_POST['grade']);
    
    try {
        $pdo->prepare("UPDATE enrollments SET grade = ? WHERE id = ?")->execute([$grade, $enrollment_id]);
        $msg = "Success! Grade updated to " . htmlspecialchars($grade) . ".";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error updating grade: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 3. FETCH DATA PARA SA UI ---
$students_list = [];
$classes_list = [];
$enrollments_list = [];

try {
    // Dropdown Data
    $students_list = $pdo->query("SELECT id, student_no, first_name, last_name FROM students WHERE status = 'Active' ORDER BY last_name ASC")->fetchAll();
    
    // Kunin lang yung mga active classes
    $classes_list = $pdo->query("
        SELECT cl.id, co.course_code, cl.schedule 
        FROM classes cl 
        JOIN courses co ON cl.course_id = co.id 
        WHERE cl.status = 'Active'
    ")->fetchAll();
    
    // Masterlist ng Enrollments & Grades
    $enrollments_list = $pdo->query("
        SELECT e.*, s.first_name, s.last_name, s.student_no, co.course_code, c.schedule 
        FROM enrollments e 
        JOIN students s ON e.student_id = s.id 
        JOIN classes c ON e.class_id = c.id 
        JOIN courses co ON c.course_id = co.id 
        ORDER BY e.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msg = "Database Error: " . $e->getMessage();
    $msgType = "error";
}

// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER HEADER
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
        <h3 class="card-title">📝 Enroll Student</h3>
        <form method="POST">
            <div class="input-group">
                <label>Select Student</label>
                <select name="student_id" class="custom-select" required>
                    <option value="" disabled selected>-- Search Student --</option>
                    <?php foreach($students_list as $st): ?>
                        <option value="<?= $st['id'] ?>">
                            <?= htmlspecialchars($st['last_name'] . ', ' . $st['first_name'] . ' (' . $st['student_no'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="input-group">
                <label>Assign to Class</label>
                <select name="class_id" class="custom-select" required>
                    <option value="" disabled selected>-- Select Active Class --</option>
                    <?php foreach($classes_list as $cl): ?>
                        <option value="<?= $cl['id'] ?>">
                            <?= htmlspecialchars($cl['course_code'] . ' | ' . $cl['schedule']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="enroll_student" class="btn-primary">Enroll Now</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">💯 Grades & Enrollment Records</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Details</th>
                        <th>Subject & Schedule</th>
                        <th>Status</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($enrollments_list) > 0): ?>
                        <?php foreach($enrollments_list as $en): 
                            // Kulay ng Grade (Green pag passed, Red pag failed, Gray pag pending)
                            $grade_color = 'gray';
                            $grade_val = $en['grade'];
                            if (is_numeric($grade_val)) {
                                if ((float)$grade_val <= 3.0) $grade_color = '#10b981'; // Passed (1.0 - 3.0)
                                if ((float)$grade_val > 3.0) $grade_color = '#ef4444';  // Failed (5.0)
                            }
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: bold;"><?= htmlspecialchars($en['first_name'] . ' ' . $en['last_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--primary-color); font-weight: bold;"><?= htmlspecialchars($en['student_no']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: bold; font-size: 13px;"><?= htmlspecialchars($en['course_code']) ?></div>
                                    <div style="font-size: 12px; color: gray;"><?= htmlspecialchars($en['schedule']) ?></div>
                                </td>
                                <td>
                                    <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 5px; font-size: 12px; font-weight: bold;">
                                        <?= htmlspecialchars($en['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                        <input type="hidden" name="enrollment_id" value="<?= $en['id'] ?>">
                                        <input type="text" name="grade" value="<?= htmlspecialchars($grade_val) ?>" style="width: 60px; padding: 5px; text-align: center; border: 1px solid var(--border-color); border-radius: 4px; font-weight: bold; color: <?= $grade_color ?>;" required>
                                        <button type="submit" name="update_grade" style="background: var(--primary-color); color: white; border: none; padding: 6px; border-radius: 4px; cursor: pointer; font-size: 11px;" title="Save Grade">💾</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 40px; color: gray;">Wala pang naka-enroll na estudyante.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER FOOTER
// ==========================================
include '../../includes/footer.php'; 
?>