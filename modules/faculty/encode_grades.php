<?php
// modules/faculty/encode_grades.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// 1. Security Check: Faculty lang dapat ang nandito
if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Faculty' && $_SESSION['role'] !== 'Teacher')){ 
    die("Access Denied.");
}

$faculty_id = $_SESSION['faculty_id'];
$class_id = $_GET['class_id'] ?? 0;
$msg = '';

try {
    // 2. I-check kung sa kanya ba talaga itong klase na ito (Security!)
    $stmt_class = $pdo->prepare("
        SELECT c.*, co.course_code, co.course_name 
        FROM classes c 
        JOIN courses co ON c.course_id = co.id 
        WHERE c.id = ? AND c.teacher_id = ?
    ");
    $stmt_class->execute([$class_id, $faculty_id]);
    $class_info = $stmt_class->fetch();

    if(!$class_info) {
        die("<div style='padding:50px; text-align:center;'><h2>Class not found or unauthorized access.</h2><a href='index.php'>Go Back</a></div>");
    }

    // 3. I-SAVE ANG GRADES KAPAG PININDOT ANG SUBMIT
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grades'])) {
        $enrollment_ids = $_POST['enrollment_id'];
        $grades = $_POST['grade'];
        $remarks = $_POST['remarks'];

        // I-loop natin para i-update isa-isa ang grade ng bawat estudyante
        $update_stmt = $pdo->prepare("UPDATE enrollments SET grade = ?, remarks = ? WHERE id = ?");
        
        for($i = 0; $i < count($enrollment_ids); $i++) {
            $update_stmt->execute([
                trim($grades[$i]), 
                $remarks[$i], 
                $enrollment_ids[$i]
            ]);
        }
        $msg = "✅ Grades successfully updated!";
    }

    // 4. Kunin ang listahan ng mga estudyante na naka-enroll sa klaseng ito
    $stmt_students = $pdo->prepare("
        SELECT e.id as enrollment_id, s.student_no, s.first_name, s.last_name, e.grade, e.remarks 
        FROM enrollments e 
        JOIN students s ON e.student_id = s.id 
        WHERE e.class_id = ?
        ORDER BY s.last_name ASC
    ");
    $stmt_students->execute([$class_id]);
    $students = $stmt_students->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode Grades | Faculty Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Premium Faculty Theme (Teal) */
        :root {
            --bg-color: #f0fdfa; --card-bg: #ffffff; --text-color: #1e293b;
            --nav-bg: #ffffff; --primary-color: #0d9488; --secondary-color: #0f766e; 
            --border-color: rgba(0,0,0,0.08); --shadow-sm: 0 4px 15px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a; --card-bg: #1e293b; --text-color: #f1f5f9;
            --nav-bg: #1e293b; --primary-color: #14b8a6; --border-color: rgba(255,255,255,0.08);
        }

        body { background-color: var(--bg-color); color: var(--text-color); transition: 0.3s; }
        
        /* Navigation */
        .custom-nav { background: var(--nav-bg); border-bottom: 1px solid var(--border-color); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
        .logo-badge { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 8px 18px; border-radius: 8px; font-weight: 900; }
        .nav-btn { color: var(--text-color); font-weight: bold; text-decoration: none; font-size: 14px; margin-left: 15px;}
        .nav-btn:hover { color: var(--primary-color); }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        /* Header Info */
        .class-header { background: var(--card-bg); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;}
        .class-title { font-size: 1.5rem; font-weight: 800; color: var(--primary-color); margin-bottom: 5px; }
        .class-meta { color: gray; font-size: 14px; }

        /* Form & Table */
        .grade-card { background: var(--card-bg); border-radius: 12px; padding: 30px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background: rgba(0,0,0,0.02); font-size: 12px; text-transform: uppercase; color: gray; }
        [data-theme="dark"] .data-table th { background: rgba(255,255,255,0.02); }

        .grade-input { width: 80px; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: transparent; color: var(--text-color); text-align: center; font-weight: bold; outline: none; transition: 0.3s;}
        .grade-select { padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: transparent; color: var(--text-color); outline: none; transition: 0.3s;}
        .grade-input:focus, .grade-select:focus { border-color: var(--primary-color); }

        .btn-primary { background: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; width: 100%;}
        .btn-primary:hover { background: var(--secondary-color); transform: translateY(-2px); }

        .success-msg { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; border: 1px solid rgba(16, 185, 129, 0.2);}
    </style>
</head>
<body>

    <nav class="custom-nav">
        <div>
            <span class="logo-badge">UMS</span>
            <span style="margin-left: 10px; font-weight: bold;">Encode Grades</span>
        </div>
        <div>
            <a href="index.php" class="nav-btn">⬅️ Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        
        <?php if($msg): ?>
            <div class="success-msg"><?= $msg ?></div>
        <?php endif; ?>

        <div class="class-header">
            <div>
                <h2 class="class-title"><?= htmlspecialchars($class_info['subject_code']) ?> - <?= htmlspecialchars($class_info['subject_name']) ?></h2>
                <div class="class-meta">🗓️ <?= htmlspecialchars($class_info['schedule']) ?> &nbsp;|&nbsp; 🚪 Rm: <?= htmlspecialchars($class_info['room']) ?></div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12px; color: gray; font-weight: bold;">TOTAL STUDENTS</div>
                <div style="font-size: 24px; font-weight: 900; color: var(--text-color);"><?= count($students) ?></div>
            </div>
        </div>

        <div class="grade-card">
            <?php if(count($students) > 0): ?>
                <form method="POST" action="">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student No.</th>
                                    <th>Name</th>
                                    <th>Grade</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $s): ?>
                                    <tr>
                                        <td style="font-weight: bold; color: gray;"><?= htmlspecialchars($s['student_no']) ?></td>
                                        <td style="font-weight: bold;"><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></td>
                                        <td>
                                            <input type="hidden" name="enrollment_id[]" value="<?= $s['enrollment_id'] ?>">
                                            
                                            <input type="text" name="grade[]" class="grade-input" 
                                                   value="<?= htmlspecialchars($s['grade']) ?>" 
                                                   placeholder="e.g. 1.0">
                                        </td>
                                        <td>
                                            <select name="remarks[]" class="grade-select">
                                                <option value="Pending" <?= $s['remarks'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Passed" <?= $s['remarks'] == 'Passed' ? 'selected' : '' ?>>Passed</option>
                                                <option value="Failed" <?= $s['remarks'] == 'Failed' ? 'selected' : '' ?>>Failed</option>
                                                <option value="Incomplete" <?= $s['remarks'] == 'Incomplete' ? 'selected' : '' ?>>Incomplete</option>
                                                <option value="Dropped" <?= $s['remarks'] == 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="save_grades" class="btn-primary">💾 Save All Grades</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; color: gray; padding: 40px;">
                    <span style="font-size: 40px;">📭</span><br><br>
                    No students are enrolled in this class yet.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Automatic na ilapat ang current theme ng user
        document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
</body>
</html>