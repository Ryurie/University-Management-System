<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Kunin ang mga invoice at i-join sa student names
$query = "SELECT i.*, s.first_name, s.last_name, s.student_number 
          FROM invoices i 
          JOIN students s ON i.student_id = s.id";
$invoices = $pdo->query($query)->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Student Invoices</h2>
        <a href="process/generate_all_invoices.php" class="btn btn-success">Generate New Invoices</a>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Invoice #</th>
                    <th>Student Name</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoices as $inv): ?>
                <tr>
                    <td><?= $inv['invoice_no'] ?></td>
                    <td><?= $inv['first_name'] ?> <?= $inv['last_name'] ?></td>
                    <td><?= CURRENCY ?> <?= number_format($inv['total_amount'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= ($inv['status'] == 'Paid') ? 'success' : 'danger' ?>">
                            <?= $inv['status'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="payments.php?invoice_id=<?= $inv['id'] ?>" class="btn btn-sm btn-info text-white">View Payment</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>