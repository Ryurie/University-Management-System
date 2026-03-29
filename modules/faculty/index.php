<?php
// modules/faculty/index.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Check if Teacher is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$msg = '';
$msgType = '';

// --- ENCODE GRADE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_grade'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $grade = trim($_POST['grade']);
    
    try {
        $pdo->prepare("UPDATE enrollments SET grade = ? WHERE id = ?")->execute([$grade, $enrollment_id]);
        $msg = "Success! Grade saved.";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error updating grade: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- FETCH TEACHER'S CLASSES & STUDENTS ---
$my_classes = [];
try {
    // Kunin lahat ng klase na hawak ni Teacher
    $stmt_classes = $pdo->prepare("
        SELECT cl.id as class_id, co.course_code, co.course_name, cl.schedule, cl.room 
        FROM classes cl 
        JOIN courses co ON cl.course_id = co.id 
        WHERE cl.teacher_id = ? AND cl.status = 'Active'
    ");
    $stmt_classes->execute([$teacher_id]);
    $classes = $stmt_classes->fetchAll();

    // Kunin ang mga estudyante per class
    foreach ($classes as $class) {
        $stmt_students = $pdo->prepare("
            SELECT e.id as enrollment_id, s.student_no, s.first_name, s.last_name, e.grade 
            FROM enrollments e 
            JOIN students s ON e.student_id = s.id 
            WHERE e.class_id = ?
            ORDER BY s.last_name ASC
        ");
        $stmt_students->execute([$class['class_id']]);
        
        $my_classes[] = [
            'info' => $class,
            'students' => $stmt_students->fetchAll()
        ];
    }
} catch (PDOException $e) {
    $msg = "Database Error: " . $e->getMessage();
    $msgType = "error";
}

include '../../includes/header.php'; 
?>

<div class="container">
    <div class="profile-banner">
        <div>
            <h1 class="page-title">Welcome, Prof. <?= htmlspecialchars($_SESSION['last_name']) ?>!</h1>
            <p style="color: gray; font-size: 1.1rem;">Faculty Portal | Manage your classes and encode grades.</p>
        </div>
        <div style="font-size: 60px;">👨‍🏫</div>
    </div>

    <?php if(!empty($msg)): ?>
        <div style="padding: 15px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; <?= $msgType === 'success' ? 'background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);' : 'background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);' ?>">
            <?= $msgType === 'success' ? '✅' : '❌' ?> <?= $msg ?>
        </div>
    <?php endif; ?>

    <?php if(count($my_classes) > 0): ?>
        <?php foreach($my_classes as $class_data): 
            $info = $class_data['info'];
            $students = $class_data['students'];
        ?>
            <div class="card" style="margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px;">
                    <div>
                        <h3 style="color: var(--primary-color); font-size: 1.5rem;"><?= htmlspecialchars($info['course_code']) ?></h3>
                        <div style="color: gray; font-size: 13px; font-weight: bold;"><?= htmlspecialchars($info['course_name']) ?></div>
                    </div>
                    <div style="text-align: right; background: rgba(0,0,0,0.03); padding: 10px 15px; border-radius: 8px;">
                        <div style="font-weight: bold; font-size: 14px;">📅 <?= htmlspecialchars($info['schedule']) ?></div>
                        <div style="color: gray; font-size: 12px;">📍 Room: <?= htmlspecialchars($info['room']) ?></div>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th style="width: 150px;">Encode Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($students) > 0): ?>
                                <?php foreach($students as $st): 
                                    $grade_color = 'gray';
                                    if (is_numeric($st['grade'])) {
                                        if ((float)$st['grade'] <= 3.0) $grade_color = '#10b981';
                                        if ((float)$st['grade'] > 3.0) $grade_color = '#ef4444';
                                    }
                                ?>
                                    <tr>
                                        <td style="font-weight: bold; color: gray;"><?= htmlspecialchars($st['student_no']) ?></td>
                                        <td style="font-weight: bold;"><?= htmlspecialchars($st['last_name'] . ', ' . $st['first_name']) ?></td>
                                        <td>
                                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="enrollment_id" value="<?= $st['enrollment_id'] ?>">
                                                <input type="text" name="grade" value="<?= htmlspecialchars($st['grade']) ?>" style="width: 70px; padding: 6px; text-align: center; border: 1px solid var(--border-color); border-radius: 5px; font-weight: bold; color: <?= $grade_color ?>;" required>
                                                <button type="submit" name="update_grade" style="background: var(--primary-color); color: white; border: none; padding: 7px; border-radius: 5px; cursor: pointer;">💾</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center; color: gray; padding: 20px;">Walang naka-enroll sa klaseng ito.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 50px;">
            <h2 style="color: gray;">Wala ka pang hawak na klase.</h2>
            <p style="color: gray;">Makipag-ugnayan sa Admin para sa inyong schedule.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>