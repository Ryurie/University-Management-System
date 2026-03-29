<?php
// modules/admin/teachers.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("Access Denied.");
}

$msg = '';
$msgType = '';
$teachers_list = []; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    try {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $department = trim($_POST['department']);

        $count = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn() + 1;
        $employee_no = 'EMP-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $hashed_password = password_hash($last_name, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO teachers (employee_no, first_name, last_name, email, department, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->execute([$employee_no, $first_name, $last_name, $email, $department, $hashed_password]);
            $msg = "Success! Na-register na si Prof. {$last_name}.";
            $msgType = "success";
        } catch (PDOException $e) {
            $stmt2 = $pdo->prepare("INSERT INTO teachers (first_name, last_name, email, password, status) VALUES (?, ?, ?, ?, 'Active')");
            $stmt2->execute([$first_name, $last_name, $email, $hashed_password]);
            $msg = "Success! Na-register na si Prof. {$last_name}.";
            $msgType = "success";
        }
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
        $msgType = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $target_id = $_POST['teacher_id'];
    $new_status = $_POST['new_status'];
    $pdo->prepare("UPDATE teachers SET status = ? WHERE id = ?")->execute([$new_status, $target_id]);
    $msg = "Status updated to " . $new_status . "!";
    $msgType = "success";
}

try {
    $teachers_list = $pdo->query("SELECT * FROM teachers ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {}

// TAWAGIN ANG HEADER
include '../../includes/header.php'; 
?>

<?php if(!empty($msg)): ?>
    <div style="max-width: 1200px; margin: 20px auto 0 auto; padding: 0 20px;">
        <div style="padding: 15px; border-radius: 8px; font-weight: bold; margin-bottom: 20px; <?= $msgType === 'success' ? 'background: rgba(16, 185, 129, 0.1); color: #10b981;' : 'background: rgba(239, 68, 68, 0.1); color: #ef4444;' ?>">
            <?= $msgType === 'success' ? '✅' : '❌' ?> <?= $msg ?>
        </div>
    </div>
<?php endif; ?>

<div class="container split-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <div class="card" style="height: fit-content;">
        <h3 class="card-title">➕ Hire New Faculty</h3>
        <form method="POST" action="">
            <div class="input-group">
                <label>First Name</label><input type="text" name="first_name" class="custom-input" required>
            </div>
            <div class="input-group">
                <label>Last Name</label><input type="text" name="last_name" class="custom-input" required>
            </div>
            <div class="input-group">
                <label>Email Address</label><input type="email" name="email" class="custom-input" required>
            </div>
            <div class="input-group">
                <label>Department</label>
                <select name="department" class="custom-select" required>
                    <option value="General Education">General Education</option>
                    <option value="Computer Studies">Computer Studies</option>
                    <option value="Business & Finance">Business & Finance</option>
                </select>
            </div>
            <button type="submit" name="add_teacher" class="btn-primary">Register Teacher</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">👨‍🏫 Faculty Roster</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead><tr><th>Employee ID</th><th>Name & Contact</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($teachers_list as $t): 
                        $emp_no = $t['employee_no'] ?? 'EMP-00'.$t['id'];
                        $badge = $t['status'] === 'Active' ? 'background: rgba(16,185,129,0.1); color: #10b981;' : 'background: rgba(239,68,68,0.1); color: #ef4444;';
                    ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--primary-color);"><?= htmlspecialchars($emp_no) ?></td>
                            <td>
                                <b><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></b>
                                <div style="font-size:12px;color:gray;"><?= htmlspecialchars($t['email'] ?? 'No email') ?></div>
                            </td>
                            <td><span style="padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; <?= $badge ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                                    <?php if($t['status'] === 'Active'): ?>
                                        <input type="hidden" name="new_status" value="Inactive">
                                        <button type="submit" name="update_status" style="background:#ef4444; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">Deactivate</button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="Active">
                                        <button type="submit" name="update_status" style="background:#10b981; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">Re-activate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>