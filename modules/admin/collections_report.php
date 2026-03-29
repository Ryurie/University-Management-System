<?php
// modules/admin/collections_report.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // Kunin ang total revenue (Lahat ng pumasok na pera)
    $total_revenue = $pdo->query("SELECT SUM(amount_paid) FROM payments")->fetchColumn() ?: 0;
    
    // Kunin ang buong listahan ng payments
    $stmt = $pdo->query("
        SELECT p.payment_date, p.amount_paid, p.payment_method, p.reference_no, 
               s.student_number, s.first_name, s.last_name 
        FROM payments p
        JOIN students s ON p.student_id = s.id
        ORDER BY p.payment_date DESC
    ");
    $payments = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

include '../../includes/header.php'; 
?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    html, body { width: 100%; max-width: 100%; overflow-x: hidden; }

    :root {
        --bg-color: #f8fafc; --card-bg: #ffffff; --text-color: #1e293b;
        --nav-bg: #ffffff; --primary-color: #6366f1; /* Indigo color for reports */
        --success-color: #10b981; --danger-color: #ef4444; --border-color: rgba(0,0,0,0.06);
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1); --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    [data-theme="dark"] {
        --bg-color: #0f172a; --card-bg: #1e293b; --text-color: #f1f5f9;
        --nav-bg: #1e293b; --border-color: rgba(255,255,255,0.06);
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.3); --shadow-md: 0 10px 20px rgba(0,0,0,0.4);
    }

    body { background-color: var(--bg-color); color: var(--text-color); transition: 0.4s ease; }
    .main-vanilla-wrapper { display: flex; flex-direction: column; width: 100%; min-height: 100vh; }
    .flex-row { display: flex; align-items: center; } .space-between { justify-content: space-between; }

    /* Navigation */
    .custom-nav { background-color: var(--nav-bg); border-bottom: 1px solid var(--border-color); padding: 12px 40px; position: sticky; top: 0; z-index: 1000; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); }
    .nav-brand-group { display: flex; align-items: center; gap: 15px; }
    .logo-badge { background: linear-gradient(135deg, var(--primary-color), #4f46e5); color: white; padding: 0 18px; height: 40px; display: inline-flex; align-items: center; border-radius: 8px; font-weight: 800; letter-spacing: 1px; font-size: 14px; }
    .nav-menu { display: flex; align-items: center; gap: 5px; }
    .nav-btn { background: none; border: none; color: var(--text-color); font-weight: 600; padding: 10px 16px; cursor: pointer; border-radius: 8px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
    .nav-btn:hover { background-color: rgba(99, 102, 241, 0.08); color: var(--primary-color); }
    .btn-danger { color: var(--danger-color); }
    
    .theme-btn-small { background-color: rgba(0,0,0,0.04); border: 1px solid var(--border-color); color: var(--text-color); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; transition: 0.3s; }
    [data-theme="dark"] .theme-btn-small { background-color: rgba(255,255,255,0.05); }

    /* Header & Print Button */
    .page-header { padding: 40px; background-color: var(--card-bg); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .page-title { font-size: 2.2rem; font-weight: 800; color: var(--text-color); letter-spacing: -1px; }
    .btn-print { background-color: var(--primary-color); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; transition: 0.2s; display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-sm); }
    .btn-print:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); background-color: #4f46e5; }

    /* Report Container & Table */
    .report-container { padding: 40px; }
    .report-card { background-color: var(--card-bg); border-radius: 15px; padding: 30px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow-x: auto; }
    
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .data-table th { background-color: var(--bg-color); font-size: 12px; text-transform: uppercase; color: gray; }

    /* PRINT STYLES - Itatago ang mga buttons kapag nag-print */
    @media print {
        @page { margin: 1cm; size: portrait; }
        .custom-nav, .btn-print, .theme-btn-small { display: none !important; }
        body, .main-vanilla-wrapper { background-color: white !important; color: black !important; }
        .report-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        .data-table th, .data-table td { border-color: #ccc !important; color: black !important; padding: 10px !important; }
        .page-header { border-bottom: 2px solid black !important; padding: 20px 0 !important; }
    }
</style>

<div class="main-vanilla-wrapper">
    <nav class="custom-nav">
        <div class="nav-brand-group">
            <span class="logo-badge">REPORTING</span>
            <button class="theme-btn-small" id="themeBtn" onclick="toggleTheme()">🌙</button>
        </div>
        <div class="nav-menu">
            <a href="enrollment_report.php" class="nav-btn">📄 Enrollment Report</a>
            <a href="collections_report.php" class="nav-btn" style="color: var(--primary-color);">💵 Collections Report</a>
            <a href="index.php" class="nav-btn" style="color: var(--danger-color);">⬅️ Back to Admin</a>
        </div>
    </nav>

    <div class="page-header">
        <div>
            <h1 class="page-title">Master Collections Report</h1>
            <p style="color: gray; margin-top:5px;">Total System Revenue: <strong style="color: var(--success-color); font-size: 1.2rem;">₱ <?= number_format($total_revenue, 2) ?></strong></p>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="report-container">
        <div class="report-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date of Payment</th>
                        <th>Student Name</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Reference No.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($payments)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 40px; color: gray;">No payment records found.</td></tr>
                    <?php else: ?>
                        <?php foreach($payments as $row): ?>
                            <tr>
                                <td style="color: gray; font-size: 14px;"><?= date('M d, Y h:i A', strtotime($row['payment_date'])) ?></td>
                                <td style="font-weight: 600; color: var(--text-color);"><?= htmlspecialchars($row['last_name']) ?>, <?= htmlspecialchars($row['first_name']) ?> <br><small style="color:gray; font-weight:normal;"><?= htmlspecialchars($row['student_number']) ?></small></td>
                                <td style="font-weight: bold; color: var(--success-color);">₱ <?= number_format($row['amount_paid'], 2) ?></td>
                                <td>
                                    <span style="background: var(--bg-color); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight:bold; border: 1px solid var(--border-color);">
                                        <?= htmlspecialchars($row['payment_method']) ?>
                                    </span>
                                </td>
                                <td style="color: gray; font-family: monospace; font-size: 13px;"><?= htmlspecialchars($row['reference_no']) ?: 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleTheme() {
        const html = document.documentElement;
        const btn = document.getElementById('themeBtn');
        let newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        if (btn) { btn.innerHTML = newTheme === 'dark' ? '☀️' : '🌙'; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        let savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const btn = document.getElementById('themeBtn');
        if (btn) btn.innerHTML = savedTheme === 'dark' ? '☀️' : '🌙';
    });
</script>

<?php include '../../includes/footer.php'; ?>