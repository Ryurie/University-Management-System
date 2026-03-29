<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

$invoice_id = $_GET['invoice_id'] ?? die("Invoice ID Required.");

// Kunin ang invoice details
$stmt = $pdo->prepare("SELECT i.*, s.first_name, s.last_name 
                       FROM invoices i 
                       JOIN students s ON i.student_id = s.id 
                       WHERE i.id = ?");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

// Kunin ang total payments na nagawa na para sa invoice na ito
$stmtPay = $pdo->prepare("SELECT SUM(amount_paid) as paid FROM payments WHERE invoice_id = ?");
$stmtPay->execute([$invoice_id]);
$paid_data = $stmtPay->fetch();
$amount_paid = $paid_data['paid'] ?? 0;
$balance = $invoice['total_amount'] - $amount_paid;

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Invoice Summary</div>
                <div class="card-body">
                    <h5>Student: <?= $invoice['first_name'] ?> <?= $invoice['last_name'] ?></h5>
                    <p class="text-muted">Invoice No: <?= $invoice['invoice_no'] ?></p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Total Bill:</span> <strong><?= CURRENCY ?> <?= number_format($invoice['total_amount'], 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between text-success">
                        <span>Total Paid:</span> <strong><?= CURRENCY ?> <?= number_format($amount_paid, 2) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between text-danger fs-5 fw-bold">
                        <span>Balance:</span> <span><?= CURRENCY ?> <?= number_format($balance, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Add New Payment</div>
                <div class="card-body">
                    <form action="process/record_payment.php" method="POST">
                        <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">
                        <div class="mb-3">
                            <label class="form-label">Amount to Pay (PHP)</label>
                            <input type="number" name="amount" class="form-control" max="<?= $balance ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="method" class="form-select">
                                <option>Cash</option>
                                <option>Bank Transfer</option>
                                <option>GCash</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Post Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>