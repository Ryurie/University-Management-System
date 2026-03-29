<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if($_SESSION['role'] !== 'Admin'){
    die("Access Denied.");
}

// Kunin ang lahat ng payments
$payments = $pdo->query("
    SELECT p.*, s.first_name, s.last_name, i.invoice_no 
    FROM payments p 
    JOIN invoices i ON p.invoice_id = i.id 
    JOIN students s ON i.student_id = s.id 
    ORDER BY p.payment_date DESC
")->fetchAll();

$total = array_sum(array_column($payments, 'amount_paid'));

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Financial Report</h2>
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Financial Statement</button>
    </div>

    <div class="card shadow-sm border-0 p-4 shadow">
        <div class="text-center mb-4">
            <h4><?= SYSTEM_NAME ?></h4>
            <h5 class="text-success">Collection Summary Report</h5>
            <p class="text-muted small">Generated on: <?= date('Y-m-d H:i') ?></p>
        </div>

        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Student Name</th>
                    <th>Method</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payments as $pay): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($pay['payment_date'])) ?></td>
                    <td><?= $pay['invoice_no'] ?></td>
                    <td><?= $pay['first_name'] ?> <?= $pay['last_name'] ?></td>
                    <td><?= $pay['payment_method'] ?></td>
                    <td class="text-end"><?= number_format($pay['amount_paid'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="table-dark">
                    <td colspan="4" class="text-end fw-bold">TOTAL COLLECTION:</td>
                    <td class="text-end fw-bold"><?= CURRENCY ?> <?= number_format($total, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>