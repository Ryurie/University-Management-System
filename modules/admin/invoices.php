<?php
// modules/admin/invoices.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // Kunin ang mga Estudyante para sa dropdown
    // Note: Pinalagay kong 'first_name' at 'last_name' ang column names mo. I-adjust na lang kung iba!
    $students = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM students ORDER BY last_name ASC")->fetchAll();
    
    // Kunin ang mga Fee Structures na ginawa natin kanina
    $fees = $pdo->query("SELECT id, fee_name, amount FROM fee_structures ORDER BY fee_name ASC")->fetchAll();

    // Kunin ang lahat ng Invoices para ilagay sa table
    $stmt = $pdo->query("
        SELECT i.*, CONCAT(s.first_name, ' ', s.last_name) as student_name, f.fee_name 
        FROM invoices i 
        JOIN students s ON i.student_id = s.id 
        JOIN fee_structures f ON i.fee_id = f.id 
        ORDER BY i.created_at DESC
    ");
    $invoices = $stmt->fetchAll();

} catch (PDOException $e) {
    // Kung mag-error dahil wala pang students table, wag mag-panic.
    $db_error = "Database warning: Make sure your 'students' table exists and has 'first_name' and 'last_name' columns. Error: " . $e->getMessage();
}

include '../../includes/header.php'; 
?>

<style>
    /* Premium Finance Theme CSS (Same as finance.php) */
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    html, body { width: 100%; max-width: 100%; overflow-x: hidden !important; }

    :root {
        --bg-color: #f4f6f9; --card-bg: #ffffff; --text-color: #2c3e50;
        --nav-bg: #ffffff; --primary-color: #27ae60; --secondary-color: #f39c12;
        --danger-color: #e74c3c; --border-color: rgba(0,0,0,0.08);
        --shadow-sm: 0 4px 15px rgba(0,0,0,0.05); --modal-overlay: rgba(0,0,0,0.5);
    }

    [data-theme="dark"] {
        --bg-color: #0b0e14 !important; --card-bg: #1c1f26 !important; --text-color: #e4e6eb !important;
        --nav-bg: #1c1f26 !important; --primary-color: #2ecc71 !important;
        --border-color: rgba(255,255,255,0.08) !important; --modal-overlay: rgba(0,0,0,0.8) !important;
    }

    body { background-color: var(--bg-color) !important; color: var(--text-color) !important; transition: 0.4s ease; }
    .custom-nav, .card, .modal-box, .custom-input, th, td { transition: 0.4s ease; }

    .custom-nav { background: var(--nav-bg) !important; border-bottom: 1px solid var(--border-color) !important; padding: 12px 30px; position: sticky; top: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm) !important; }
    .logo-badge { background: var(--primary-color); color: white; padding: 6px 15px; border-radius: 6px; font-weight: 900; letter-spacing: 1px; }
    .nav-btn { background: none; border: none; color: var(--text-color) !important; font-weight: bold; padding: 10px 15px; cursor: pointer; border-radius: 6px; text-decoration: none; font-size: 14px; transition: 0.2s; }
    .nav-btn:hover { background: rgba(39, 174, 96, 0.1); color: var(--primary-color) !important; }
    .theme-btn-small { background: rgba(0,0,0,0.02); border: 1px solid var(--border-color) !important; color: var(--text-color) !important; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: 0.3s; }

    .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn-green { background: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px;}
    .btn-green:hover { background: #219653; transform: translateY(-2px); }

    .data-table-container { background: var(--card-bg) !important; border-radius: 15px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 18px 20px; text-align: left; border-bottom: 1px solid var(--border-color) !important; }
    .data-table th { background: rgba(0,0,0,0.02); font-size: 13px; text-transform: uppercase; color: gray; }
    [data-theme="dark"] .data-table th { background: rgba(255,255,255,0.02); }
    
    .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .status-unpaid { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }
    .status-partial { background: rgba(243, 156, 18, 0.1); color: var(--secondary-color); }
    .status-paid { background: rgba(39, 174, 96, 0.1); color: var(--primary-color); }

    /* Modal Styles */
    .custom-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: var(--modal-overlay) !important; backdrop-filter: blur(5px); justify-content: center; align-items: center;}
    .modal-content { background: var(--card-bg) !important; padding: 40px; border-radius: 15px; width: 90%; max-width: 500px; position: relative; border: 1px solid var(--border-color) !important;}
    .floating-group { position: relative; margin-bottom: 20px; }
    .floating-input, .floating-select { width: 100%; padding: 14px; border: 2px solid var(--border-color) !important; border-radius: 8px; background: transparent; color: var(--text-color) !important; font-size: 15px; outline: none; transition: 0.3s; }
    .floating-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; color: gray; }
    .floating-input:focus, .floating-select:focus { border-color: var(--primary-color) !important; }
</style>

<nav class="custom-nav">
    <div style="display: flex; gap: 10px; align-items: center;">
        <span class="logo-badge">FINANCE</span>
        <button class="theme-btn-small" id="finThemeBtn" onclick="toggleFinTheme()">🌙</button>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="finance.php" class="nav-btn">💰 Dashboard</a>
        <a href="invoices.php" class="nav-btn" style="color: var(--primary-color) !important;">📄 Invoices</a>
        <a href="payments.php" class="nav-btn">💵 Payments</a>
    </div>
</nav>

<div class="container">
    <?php if(isset($db_error)): ?>
        <div style="background: rgba(231, 76, 60, 0.1); color: var(--danger-color); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $db_error ?>
        </div>
    <?php endif; ?>

    <div class="header-flex">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 800;">Student Invoices</h1>
            <p style="color: gray;">Bill students based on active fee structures.</p>
        </div>
        <button class="btn-green" onclick="openModal('invoiceModal')">➕ Create Invoice</button>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Student Name</th>
                    <th>Fee Description</th>
                    <th>Total Amount</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($invoices) && count($invoices) > 0): ?>
                    <?php foreach($invoices as $inv): 
                        $balance = $inv['total_amount'] - $inv['paid_amount'];
                        $status_class = 'status-unpaid';
                        if($inv['status'] == 'Paid') $status_class = 'status-paid';
                        if($inv['status'] == 'Partial') $status_class = 'status-partial';
                    ?>
                        <tr>
                            <td style="font-weight: bold; color: gray;">#INV-<?= str_pad($inv['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td style="font-weight: bold;"><?= htmlspecialchars($inv['student_name']) ?></td>
                            <td><?= htmlspecialchars($inv['fee_name']) ?></td>
                            <td>₱ <?= number_format($inv['total_amount'], 2) ?></td>
                            <td style="color: var(--danger-color) !important; font-weight: bold;">₱ <?= number_format($balance, 2) ?></td>
                            <td><span class="status-badge <?= $status_class ?>"><?= strtoupper($inv['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: gray; padding: 40px;">No invoices generated yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="invoiceModal" class="custom-modal">
    <div class="modal-content">
        <button onclick="closeModal('invoiceModal')" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; background: none; border: none; color: gray;">&times;</button>
        <h2 style="margin-bottom: 25px;">Create New Invoice</h2>

        <form id="invoiceForm">
            <div class="floating-group">
                <label class="floating-label">Select Student</label>
                <select name="student_id" class="floating-select" required>
                    <option value="" disabled selected>-- Choose Student --</option>
                    <?php if(isset($students)) { foreach($students as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                    <?php endforeach; } ?>
                </select>
            </div>

            <div class="floating-group">
                <label class="floating-label">Select Fee Structure</label>
                <select name="fee_id" class="floating-select" required>
                    <option value="" disabled selected>-- Choose Fee --</option>
                    <?php if(isset($fees)) { foreach($fees as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['fee_name']) ?> (₱<?= number_format($f['amount'], 2) ?>)</option>
                    <?php endforeach; } ?>
                </select>
            </div>

            <div class="floating-group">
                <label class="floating-label">Due Date (Optional)</label>
                <input type="date" name="due_date" class="floating-input">
            </div>
            
            <button type="submit" class="btn-green" id="saveInvBtn" style="width: 100%; padding: 15px;">Generate Invoice ➔</button>
            <div id="msgBox" style="margin-top: 15px; text-align: center; font-weight: bold; font-size: 14px;"></div>
        </form>
    </div>
</div>

<script>
    function toggleFinTheme() {
        const html = document.documentElement;
        let isDark = html.getAttribute('data-theme') === 'dark';
        let newTheme = isDark ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        document.getElementById('finThemeBtn').innerHTML = newTheme === 'dark' ? '☀️' : '🌙';
    }

    document.addEventListener('DOMContentLoaded', () => {
        let savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('finThemeBtn').innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
    });

    function openModal(id) { document.getElementById(id).style.display = "flex"; }
    function closeModal(id) { document.getElementById(id).style.display = "none"; }

    // AJAX Submission
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveInvBtn');
        const msg = document.getElementById('msgBox');
        
        btn.disabled = true; btn.innerText = "Generating...";

        fetch('process/create_invoice.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.text())
        .then(data => { 
            if(data.trim() === "success") {
                msg.style.color = "var(--primary-color)"; msg.innerHTML = "✅ Invoice Generated!";
                setTimeout(() => location.reload(), 1500);
            } else {
                msg.style.color = "var(--danger-color)"; msg.innerHTML = "❌ " + data;
                btn.disabled = false; btn.innerText = "Generate Invoice ➔";
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>