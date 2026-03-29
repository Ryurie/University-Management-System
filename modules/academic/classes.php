<?php
// modules/academic/classes.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Administrator Portal only.</h2></div>");
}

$msg = '';
$msgType = '';

// --- 1. CREATE CLASS LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_class'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO classes (course_id, teacher_id, schedule, room, status) VALUES (?, ?, ?, ?, 'Active')");
        $stmt->execute([
            $_POST['course_id'], 
            $_POST['teacher_id'], 
            trim($_POST['schedule']), 
            trim($_POST['room'])
        ]);
        $msg = "Success! Na-create na ang bagong klase.";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error creating class: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 2. UPDATE STATUS LOGIC (DISSOLVE / RE-OPEN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    try {
        $pdo->prepare("UPDATE classes SET status = ? WHERE id = ?")->execute([$_POST['new_status'], $_POST['class_id']]);
        $msg = "Success! Class status updated to " . htmlspecialchars($_POST['new_status']) . ".";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error updating status: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 3. FETCH DATA PARA SA UI ---
$courses_list = [];
$teachers_list = [];
$classes_list = [];

try {
    // Kunin ang mga available na courses at active teachers para sa Dropdown
    $courses_list = $pdo->query("SELECT id, course_code FROM courses")->fetchAll();
    $teachers_list = $pdo->query("SELECT id, first_name, last_name FROM teachers WHERE status = 'Active'")->fetchAll();
    
    // Kunin ang master list ng classes (Naka-JOIN para makuha yung pangalan ng teacher at course)
    $classes_list = $pdo->query("
        SELECT cl.*, co.course_code, co.course_name, t.first_name, t.last_name 
        FROM classes cl 
        LEFT JOIN courses co ON cl.course_id = co.id 
        LEFT JOIN teachers t ON cl.teacher_id = t.id 
        ORDER BY cl.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
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
        <h3 class="card-title">➕ Create Class</h3>
        <form method="POST">
            <div class="input-group">
                <label>Subject / Program</label>
                <select name="course_id" class="custom-select" required>
                    <option value="" disabled selected>-- Select Program --</option>
                    <?php foreach($courses_list as $co): ?>
                        <option value="<?= $co['id'] ?>"><?= htmlspecialchars($co['course_code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Assigned Faculty</label>
                <select name="teacher_id" class="custom-select" required>
                    <option value="" disabled selected>-- Select Teacher --</option>
                    <?php foreach($teachers_list as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Schedule (Day & Time)</label>
                <input type="text" name="schedule" class="custom-input" required placeholder="e.g. MWF 9:00AM - 10:30AM">
            </div>
            <div class="input-group">
                <label>Room Assignment</label>
                <input type="text" name="room" class="custom-input" required placeholder="e.g. Room 302">
            </div>
            <button type="submit" name="create_class" class="btn-primary">Create Class</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">📅 Master Schedule</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Faculty</th>
                        <th>Schedule & Room</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($classes_list) > 0): ?>
                        <?php foreach($classes_list as $c): 
                            $stat = $c['status'] ?? 'Active';
                        ?>
                            <tr>
                                <td style="color: var(--primary-color); font-weight: bold;">
                                    <?= htmlspecialchars($c['course_code'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    Prof. <?= htmlspecialchars($c['last_name'] ?? 'TBA') ?>
                                </td>
                                <td>
                                    <div style="font-weight: bold; font-size: 13px;"><?= htmlspecialchars($c['schedule']) ?></div>
                                    <div style="font-size: 12px; color: gray;">Room: <?= htmlspecialchars($c['room']) ?></div>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                        <?php if($stat === 'Active'): ?>
                                            <input type="hidden" name="new_status" value="Dissolved">
                                            <button type="submit" name="update_status" style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; cursor: pointer;" onclick="return confirm('Sigurado ka bang i-di-dissolve ang klaseng ito?');">Dissolve</button>
                                        <?php else: ?>
                                            <span style="color: #ef4444; font-size: 12px; font-weight: bold; margin-right: 5px;">[Dissolved]</span>
                                            <input type="hidden" name="new_status" value="Active">
                                            <button type="submit" name="update_status" style="background: #10b981; color: white; border: none; padding: 6px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; cursor: pointer;">Re-open</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 40px; color: gray;">Walang naka-schedule na klase.</td></tr>
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