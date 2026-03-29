<?php
// modules/student/index.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check: Siguraduhing Student ang naka-login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_info = [];
$my_grades = [];
$my_invoices = [];

try {
    // 1. KUNIN ANG IMPORMASYON NG ESTUDYANTE
    $stmt_info = $pdo->prepare("
        SELECT s.*, c.course_code, c.course_name 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt_info->execute([$student_id]);
    $student_info = $stmt_info->fetch();

    // 2. KUNIN ANG GRADES AT SCHEDULE
    $stmt_grades = $pdo->prepare("
        SELECT e.grade, e.status as enroll_status, cl.schedule, cl.room, 
               co.course_code, co.course_name, t.first_name as prof_fname, t.last_name as prof_lname 
        FROM enrollments e 
        JOIN classes cl ON e.class_id = cl.id 
        JOIN courses co ON cl.course_id = co.id 
        LEFT JOIN teachers t ON cl.teacher_id = t.id 
        WHERE e.student_id = ?
        ORDER BY co.course_code ASC
    ");
    $stmt_grades->execute([$student_id]);
    $my_grades = $stmt_grades->fetchAll();

    // 3. KUNIN ANG FINANCIAL RECORDS (Statement of Account)
    $stmt_finance = $pdo->prepare("SELECT * FROM invoices WHERE student_id = ? ORDER BY id DESC");
    $stmt_finance->execute([$student_id]);
    $my_invoices = $stmt_finance->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER HEADER
// ==========================================
include '../../includes/header.php'; 
?>

<div class="container">
    
    <div class="profile-banner" style="background: linear-gradient(135deg, var(--card-bg) 60%, rgba(59, 130, 246, 0.1) 100%);">
        <div>
            <div style="font-size: 12px; font-weight: bold; color: var(--primary-color); letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase;">
                Student ID: <?= htmlspecialchars($student_info['student_no']) ?>
            </div>
            <h1 class="page-title">Hello, <?= htmlspecialchars($student_info['first_name']) ?>! 👋</h1>
            <p style="color: gray; font-size: 1.1rem; font-weight: 500;">
                <?= htmlspecialchars($student_info['course_name'] ?? 'Unassigned Program') ?> 
                (<span style="color: var(--text-color); font-weight: bold;"><?= htmlspecialchars($student_info['course_code'] ?? 'N/A') ?></span>)
            </p>
        </div>
        <div style="font-size: 60px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.1));">🎓</div>
    </div>

    <div class="split-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <div class="card">
            <h3 class="card-title">📚 My Academic Records</h3>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject & Professor</th>
                            <th>Schedule & Room</th>
                            <th style="text-align: center;">Final Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($my_grades) > 0): ?>
                            <?php foreach($my_grades as $g): 
                                // Kulay ng Grade Logic
                                $grade_color = 'gray';
                                $grade_bg = 'rgba(0,0,0,0.05)';
                                $grade_text = $g['grade'];

                                if (is_numeric($grade_text)) {
                                    if ((float)$grade_text <= 3.0) {
                                        $grade_color = '#10b981'; // Passed
                                        $grade_bg = 'rgba(16, 185, 129, 0.1)';
                                    } elseif ((float)$grade_text > 3.0) {
                                        $grade_color = '#ef4444'; // Failed
                                        $grade_bg = 'rgba(239, 68, 68, 0.1)';
                                    }
                                } elseif ($grade_text === 'Pending' || empty($grade_text)) {
                                    $grade_text = 'TBA';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: bold; color: var(--primary-color); font-size: 14px;"><?= htmlspecialchars($g['course_code']) ?></div>
                                        <div style="font-size: 12px; color: gray;">Prof. <?= htmlspecialchars($g['prof_lname'] ?? 'TBA') ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: bold; font-size: 13px;"><?= htmlspecialchars($g['schedule']) ?></div>
                                        <div style="font-size: 12px; color: gray;">Room: <?= htmlspecialchars($g['room']) ?></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="display: inline-block; padding: 6px 12px; border-radius: 8px; font-weight: 900; font-size: 15px; color: <?= $grade_color ?>; background: <?= $grade_bg ?>; border: 1px solid <?= $grade_color ?>33;">
                                            <?= htmlspecialchars($grade_text) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; padding: 30px; color: gray;">Hindi ka pa naka-enroll sa kahit anong klase.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="height: fit-content;">
            <h3 class="card-title">💸 Statement of Account</h3>
            
            <?php 
                $total_due = 0; $total_paid = 0;
                foreach($my_invoices as $inv) {
                    $total_due += $inv['total_amount'];
                    $total_paid += $inv['paid_amount'];
                }
                $total_balance = $total_due - $total_paid;
            ?>

            <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px;">
                <div style="font-size: 12px; font-weight: bold; color: gray; text-transform: uppercase;">Outstanding Balance</div>
                <div style="font-size: 2.5rem; font-weight: 900; color: <?= $total_balance > 0 ? 'var(--danger-color)' : 'var(--success-color)' ?>;">
                    ₱ <?= number_format($total_balance, 2) ?>
                </div>
            </div>

            <h4 style="font-size: 13px; color: gray; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Recent Transactions</h4>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php if(count($my_invoices) > 0): ?>
                    <?php foreach($my_invoices as $inv): 
                        $status_color = $inv['status'] === 'Paid' ? '#10b981' : ($inv['status'] === 'Partial' ? '#f59e0b' : '#ef4444');
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--card-bg);">
                            <div>
                                <div style="font-weight: bold; font-size: 14px;">Assessment</div>
                                <div style="font-size: 11px; font-weight: bold; color: <?= $status_color ?>; text-transform: uppercase;"><?= $inv['status'] ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: bold; font-size: 14px;">₱ <?= number_format($inv['total_amount'], 2) ?></div>
                                <div style="font-size: 11px; color: gray;">Paid: ₱ <?= number_format($inv['paid_amount'], 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: gray; font-size: 13px;">No financial records found.</div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<?php 
// ==========================================
// 🚀 TATAWAGIN NATIN ANG MASTER FOOTER
// ==========================================
include '../../includes/footer.php'; 
?>