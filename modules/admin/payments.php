<?php
// modules/admin/payments.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // 1. Kunin lahat ng Invoices na HINDI PA BAYAD (Unpaid/Partial) para sa Dropdown natin
    $stmt_pending = $pdo->query("
        SELECT i.id, CONCAT(s.first_name, ' ', s.last_name) as student_name, f.fee_name, (i.total_amount - i.paid_amount) as balance 
        FROM invoices i 
        JOIN students s ON i.student_id = s.id 
        JOIN fee_structures f ON i.fee_id = f.id 
        WHERE i.status != 'Paid'
        ORDER BY i.created_at ASC
    ");
    $pending_invoices = $stmt_pending->fetchAll();

    // 2. Kunin ang Payment History para sa Table
    $stmt_payments = $pdo->query("
        SELECT p.*, i.id as inv_id, CONCAT(s.first_name, ' ', s.last_name) as student_name, f.fee_name 
        FROM payments p 
        JOIN invoices i ON p.invoice_id = i.id 
        JOIN students s ON i.student_id = s.id 
        JOIN fee_structures f ON i.fee_id = f.id 
        ORDER BY p.payment_date DESC
    ");
    $payments = $stmt_payments->fetchAll();

} catch (PDOException $e) {
    $db_error = "Database Error: " . $e->getMessage();
}

include '../../includes/header.php'; 
?>

<style>
    /* Premium Finance Theme (Same standard as your previous files) */
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
    .btn-green:hover { background: #219653; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3); }

    .data-table-container { background: var(--card-bg) !important; border-radius: 15px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; overflow: hidden; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 18px 20px; text-align: left; border-bottom: 1px solid var(--border-color) !important; }
    .data-table th { background: rgba(0,0,0,0.02); font-size: 13px; text-transform: uppercase; color: gray; }
    [data-theme="dark"] .data-table th { background: rgba(255,255,255,0.02); }
    
    .method-badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: bold; background: rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
    [data-theme="dark"] .method-badge { background: rgba(255,255,255,0.05); }

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
        <a href="invoices.php" class="nav-btn">📄 Invoices</a>
        <a href="payments.php" class="nav-btn" style="color: var(--primary-color) !important;">💵 Payments</a>
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
            <h1 style="font-size: 2.2rem; font-weight: 800;">Payment Records</h1>
            <p style="color: gray;">Log and track all student payments and transactions.</p>
        </div>
        <button class="btn-green" onclick="openModal('paymentModal')">💳 Log Payment</button>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice / Student</th>
                    <th>Fee Type</th>
                    <th>Amount Paid</th>
                    <th>Method</th>
                    <th>Ref No.</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($payments) && count($payments) > 0): ?>
                    <?php foreach($payments as $pay): ?>
                        <tr>
                            <td style="color: gray; font-size: 14px;"><?= date('M d, Y h:i A', strtotime($pay['payment_date'])) ?></td>
                            <td>
                                <div style="font-weight: bold;">INV-<?= str_pad($pay['inv_id'], 4, '0', STR_PAD_LEFT) ?></div>
                                <div style="font-size: 12px; color: gray;"><?= htmlspecialchars($pay['student_name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($pay['fee_name']) ?></td>
                            <td style="color: var(--primary-color) !important; font-weight: 900; font-size: 1.1rem;">₱ <?= number_format($pay['amount_paid'], 2) ?></td>
                            <td><span class="method-badge"><?= htmlspecialchars($pay['payment_method']) ?></span></td>
                            <td style="font-family: monospace; color: gray;"><?= htmlspecialchars($pay['reference_no'] ?: 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: gray; padding: 40px;">No payments recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="paymentModal" class="custom-modal">
    <div class="modal-content">
        <button onclick="closeModal('paymentModal')" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; background: none; border: none; color: gray;">&times;</button>
        <h2 style="margin-bottom: 25px;">Log a Payment</h2>

        <form id="paymentForm">
            <div class="floating-group">
                <label class="floating-label">Select Pending Invoice</label>
                <select name="invoice_id" class="floating-select" required>
                    <option value="" disabled selected>-- Choose Unpaid Invoice --</option>
                    <?php if(isset($pending_invoices)) { foreach($pending_invoices as $pi): ?>
                        <option value="<?= $pi['id'] ?>">INV-<?= str_pad($pi['id'], 4, '0', STR_PAD_LEFT) ?> : <?= htmlspecialchars($pi['student_name']) ?> (Bal: ₱<?= number_format($pi['balance'], 2) ?>)</option>
                    <?php endforeach; } ?>
                </select>
            </div>

            <div class="floating-group">
                <label class="floating-label">Amount to Pay (₱)</label>
                <input type="number" name="amount_paid" class="floating-input" step="0.01" min="1" required placeholder="e.g. 1500.00">
            </div>

            <div class="floating-group">
                <label class="floating-label">Payment Method</label>
                <select name="payment_method" class="floating-select" required>
                    <option value="Cash">💵 Cash</option>
                    <option value="GCash">📱 GCash</option>
                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                    <option value="Cheque">📜 Cheque</option>
                </select>
            </div>

            <div class="floating-group">
                <label class="floating-label">Reference No. (Optional for Cash)</label>
                <input type="text" name="reference_no" class="floating-input" placeholder="e.g. TXN123456789">
            </div>
            
            <button type="submit" class="btn-green" id="savePayBtn" style="width: 100%; padding: 15px;">Confirm Payment ➔</button>
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
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('savePayBtn');
        const msg = document.getElementById('msgBox');
        
        btn.disabled = true; btn.innerText = "Processing...";

        fetch('process/add_payment.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.text())
        .then(data => { 
            if(data.trim() === "success") {
                msg.style.color = "var(--primary-color)"; msg.innerHTML = "✅ Payment Successful!";
                setTimeout(() => location.reload(), 1500);
            } else {
                msg.style.color = "var(--danger-color)"; msg.innerHTML = "❌ " + data;
                btn.disabled = false; btn.innerText = "Confirm Payment ➔";
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>