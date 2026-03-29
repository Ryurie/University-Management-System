<?php
// modules/teacher/grades.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// I-check kung Teacher nga ang naka-login
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher'){ 
    header("Location: ../auth/login.php");
    exit(); 
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Kunin LAMANG ang mga Classes na naka-assign sa Teacher na ito
    $stmt_classes = $pdo->prepare("
        SELECT c.id, c.section_name, crs.course_code 
        FROM classes c 
        JOIN courses crs ON c.course_id = crs.id 
        WHERE c.teacher_id = ?
        ORDER BY c.section_name ASC
    ");
    $stmt_classes->execute([$teacher_id]);
    $my_classes = $stmt_classes->fetchAll();

    // Kung may piniling klase, kunin ang mga enrolled students
    $students_list = [];
    $selected_class = $_GET['class_id'] ?? null;

    // I-verify kung hawak nga ba talaga ng teacher ang klaseng ito (Security check!)
    $is_my_class = false;
    foreach($my_classes as $mc) {
        if($mc['id'] == $selected_class) { $is_my_class = true; break; }
    }

    if ($selected_class && $is_my_class) {
        $stmt = $pdo->prepare("
            SELECT e.id as enrollment_id, s.student_number, s.first_name, s.last_name, 
                   g.prelim, g.midterm, g.finals, g.final_grade, g.remarks
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            LEFT JOIN grades g ON e.id = g.enrollment_id
            WHERE e.class_id = ?
            ORDER BY s.last_name ASC
        ");
        $stmt->execute([$selected_class]);
        $students_list = $stmt->fetchAll();
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal | Grade Encoding</title>
    <style>
        /* EMERALD GREEN THEME FOR TEACHERS */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0fdf4; color: #1e293b; }
        
        .navbar { background-color: #047857; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .brand { font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; gap: 10px; text-decoration: none; color: white; }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .header-title { font-size: 2rem; font-weight: 900; color: #047857; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .filter-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; gap: 15px; align-items: flex-end; margin-bottom: 30px; border-left: 5px solid #10b981; }
        .input-group { flex: 1; }
        .input-label { font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 8px; display: block; text-transform: uppercase; }
        .custom-select { width: 100%; padding: 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 15px; font-weight: bold; outline: none; cursor: pointer; }
        .custom-select:focus { border-color: #10b981; }
        
        .btn-primary { background: #10b981; color: white; padding: 14px 25px; border: none; font-weight: bold; border-radius: 10px; cursor: pointer; transition: 0.3s; font-size: 15px; }
        .btn-primary:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 10px 15px rgba(16, 185, 129, 0.2); }
        .btn-primary:disabled { background: gray; cursor: not-allowed; transform: none; box-shadow: none; }

        .table-card { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x: auto; padding: 30px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        .data-table th { background-color: #f8fafc; font-size: 13px; text-transform: uppercase; color: #64748b; }
        
        .grade-input { width: 80px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 15px; text-align: center; font-weight: bold; outline: none; transition: 0.3s; }
        .grade-input:focus { border-color: #10b981; background: #f0fdf4; }

        .badge-pass { background: #d1fae5; color: #10b981; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-fail { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="brand">
            <div style="background: white; color: #047857; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">🍎</div>
            FACULTY PORTAL
        </a>
        <div style="font-weight: bold;">Prof. <?= htmlspecialchars($_SESSION['teacher_name']) ?></div>
    </nav>

    <div class="container">
        <h1 class="header-title">📝 Class Grading Sheet</h1>

        <form method="GET" action="grades.php" class="filter-card">
            <div class="input-group">
                <label class="input-label">Select My Class</label>
                <select name="class_id" class="custom-select" required>
                    <option value="" disabled selected hidden>Choose a class you handle...</option>
                    <?php foreach($my_classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selected_class == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['section_name']) ?> - <?= htmlspecialchars($c['course_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Load Masterlist ➔</button>
        </form>

        <?php if ($selected_class && $is_my_class): ?>
            <div class="table-card">
                <form id="saveGradesForm">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th style="text-align:center;">Prelim</th>
                                <th style="text-align:center;">Midterm</th>
                                <th style="text-align:center;">Finals</th>
                                <th style="text-align:center;">Final Grade</th>
                                <th style="text-align:center;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students_list)): ?>
                                <tr><td colspan="6" style="text-align:center; padding: 30px; color: gray;">No students enrolled yet.</td></tr>
                            <?php else: ?>
                                <?php foreach($students_list as $s): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: #047857; font-size: 16px;"><?= htmlspecialchars($s['last_name']) ?>, <?= htmlspecialchars($s['first_name']) ?></strong><br>
                                            <small style="color: gray;">SN: <?= htmlspecialchars($s['student_number']) ?></small>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="number" step="0.01" name="grades[<?= $s['enrollment_id'] ?>][prelim]" class="grade-input" value="<?= $s['prelim'] ?>" placeholder="0.00">
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="number" step="0.01" name="grades[<?= $s['enrollment_id'] ?>][midterm]" class="grade-input" value="<?= $s['midterm'] ?>" placeholder="0.00">
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="number" step="0.01" name="grades[<?= $s['enrollment_id'] ?>][finals]" class="grade-input" value="<?= $s['finals'] ?>" placeholder="0.00">
                                        </td>
                                        <td style="text-align:center; font-weight:900; font-size: 18px; color: #1e293b;">
                                            <?= $s['final_grade'] ? number_format($s['final_grade'], 2) : '--' ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php if($s['remarks'] == 'Passed'): ?>
                                                <span class="badge-pass">PASSED</span>
                                            <?php elseif($s['remarks'] == 'Failed'): ?>
                                                <span class="badge-fail">FAILED</span>
                                            <?php else: ?>
                                                <span style="color:gray; font-size:12px;">--</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if(!empty($students_list)): ?>
                        <div style="display:flex; justify-content:flex-end; margin-top: 20px;">
                            <button type="submit" class="btn-primary" id="saveBtn">💾 Submit Grades to System</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php elseif ($selected_class && !$is_my_class): ?>
            <div style="text-align:center; padding: 40px; color: #ef4444; font-weight: bold;">
                ❌ Access Denied: You are not assigned to this class.
            </div>
        <?php endif; ?>

    </div>

    <script>
        // --- REUSING THE ADMIN'S SAVE GRADES BACKEND! ---
        const gradeForm = document.getElementById('saveGradesForm');
        if(gradeForm) {
            gradeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('saveBtn');
                btn.innerText = "Submitting...";
                btn.disabled = true;

                // Tignan mo, itinuturo natin siya sa ginawa mo sa Admin folder! Code reuse level 999!
                fetch('../academic/process/save_grades.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.text())
                .then(data => {
                    if(data.trim() === "success") {
                        alert("✅ Grades submitted successfully! Students can now view this in their portal.");
                        location.reload();
                    } else {
                        alert("❌ Error: " + data);
                        btn.innerText = "💾 Submit Grades to System";
                        btn.disabled = false;
                    }
                }).catch(err => {
                    alert("❌ Connection Error.");
                    btn.innerText = "💾 Submit Grades to System";
                    btn.disabled = false;
                });
            });
        }
    </script>

</body>
</html>