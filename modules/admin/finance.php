<?php
// modules/admin/finance.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("<div style='padding:50px; text-align:center;'><h2>Access Denied. Administrator Portal only.</h2></div>");
}

$msg = '';
$msgType = '';

// --- 1. PAYMENT PROCESSING LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_payment'])) {
    $invoice_id = $_POST['invoice_id'];
    $payment_amount = (float)$_POST['payment_amount'];

    try {
        $stmt_check = $pdo->prepare("SELECT total_amount, paid_amount FROM invoices WHERE id = ?");
        $stmt_check->execute([$invoice_id]);
        $inv = $stmt_check->fetch();

        if ($inv) {
            $current_paid = (float)$inv['paid_amount'];
            $total_due = (float)$inv['total_amount'];
            $balance = $total_due - $current_paid;

            if ($payment_amount > $balance) {
                $msg = "Sobra ang ibinabayad mo! Ang balanse na lang ay ₱" . number_format($balance, 2);
                $msgType = "error";
            } else {
                $new_paid = $current_paid + $payment_amount;
                $status = 'Unpaid';
                if ($new_paid >= $total_due) { $status = 'Paid'; } 
                elseif ($new_paid > 0) { $status = 'Partial'; }

                $stmt_update = $pdo->prepare("UPDATE invoices SET paid_amount = ?, status = ? WHERE id = ?");
                if ($stmt_update->execute([$new_paid, $status, $invoice_id])) {
                    $msg = "Success! Payment of ₱" . number_format($payment_amount, 2) . " processed.";
                    $msgType = "success";
                } else {
                    $msg = "System Error: Hindi pumasok ang bayad.";
                    $msgType = "error";
                }
            }
        }
    } catch (PDOException $e) {
        $msg = "Database Error: " . $e->getMessage();
        $msgType = "error";
    }
}

// --- 2. KUNIN ANG MGA INVOICES PARA SA UI ---
$invoices = [];
$pending_invoices = [];

try {
    $invoices = $pdo->query("
        SELECT i.*, s.first_name, s.last_name, s.student_no 
        FROM invoices i
        JOIN students s ON i.student_id = s.id
        ORDER BY i.status DESC, i.id DESC
    ")->fetchAll();

    // I-filter ang mga hindi pa bayad para sa Dropdown
    $pending_invoices = array_filter($invoices, function($inv) { 
        return $inv['status'] !== 'Paid'; 
    });
} catch (PDOException $e) {
    $msg = "Error loading financial records: " . $e->getMessage();
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
        <h3 class="card-title">💵 Receive Payment</h3>
        <form method="POST" action="">
            <div class="input-group">
                <label>Select Student / Invoice</label>
                <select name="invoice_id" class="custom-select" required>
                    <option value="" disabled selected>-- Choose Account --</option>
                    <?php foreach($pending_invoices as $inv): 
                        $bal = $inv['total_amount'] - $inv['paid_amount'];
                    ?>
                        <option value="<?= $inv['id'] ?>">
                            <?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?> (Bal: ₱<?= number_format($bal, 2) ?>)
                        </option>
                    <?php endforeach; ?>
                    <?php if(count($pending_invoices) == 0): ?>
                        <option value="" disabled>🎉 No pending balances!</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Amount to Pay (₱)</label>
                <input type="number" step="0.01" name="payment_amount" class="custom-input" required placeholder="e.g. 5000">
            </div>
            <button type="submit" name="process_payment" class="btn-primary"><span>💳</span> Process Payment</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">📜 Financial Ledger</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Total Fee</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Status / Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($invoices) > 0): ?>
                        <?php foreach($invoices as $inv): 
                            $balance = $inv['total_amount'] - $inv['paid_amount'];
                            
                            // Badge Styling
                            $badge_style = 'background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);'; // Unpaid Default
                            if($inv['status'] === 'Paid') {
                                $badge_style = 'background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);';
                            } elseif($inv['status'] === 'Partial') {
                                $badge_style = 'background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);';
                            }
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: bold;"><?= htmlspecialchars($inv['first_name'] . ' ' . $inv['last_name']) ?></div>
                                    <div style="font-size: 12px; color: gray;"><?= htmlspecialchars($inv['student_no']) ?></div>
                                </td>
                                <td style="font-weight: bold;">₱ <?= number_format($inv['total_amount'], 2) ?></td>
                                <td style="color: var(--success-color); font-weight: bold;">₱ <?= number_format($inv['paid_amount'], 2) ?></td>
                                <td style="color: var(--danger-color); font-weight: bold;">₱ <?= number_format($balance, 2) ?></td>
                                <td>
                                    <div style="margin-bottom: 8px;">
                                        <span style="padding: 5px 10px; border-radius: 6px; font-weight: bold; font-size: 12px; <?= $badge_style ?>"><?= htmlspecialchars($inv['status']) ?></span>
                                    </div>
                                    <?php if($inv['paid_amount'] > 0): ?>
                                        <a href="receipt.php?id=<?= $inv['id'] ?>" target="_blank" style="background: #3b82f6; color: white; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; transition: 0.2s;">🖨️ Print Receipt</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px; color: gray;">Walang financial records.</td></tr>
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