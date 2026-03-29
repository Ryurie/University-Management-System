<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice_id = $_POST['invoice_id'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];

    try {
        $pdo->beginTransaction();

        // 1. I-save ang payment record
        $stmt = $pdo->prepare("INSERT INTO payments (invoice_id, amount_paid, payment_method) VALUES (?, ?, ?)");
        $stmt->execute([$invoice_id, $amount, $method]);

        // 2. Re-calculate total paid para sa invoice na ito
        $stmtCheck = $pdo->prepare("SELECT SUM(amount_paid) as total_paid FROM payments WHERE invoice_id = ?");
        $stmtCheck->execute([$invoice_id]);
        $total_paid = $stmtCheck->fetch()['total_paid'];

        // 3. Kunin ang total amount ng invoice
        $stmtInv = $pdo->prepare("SELECT total_amount FROM invoices WHERE id = ?");
        $stmtInv->execute([$invoice_id]);
        $total_bill = $stmtInv->fetch()['total_amount'];

        // 4. I-update ang status ng Invoice
        $status = 'Unpaid';
        if ($total_paid >= $total_bill) {
            $status = 'Paid';
        } elseif ($total_paid > 0) {
            $status = 'Partial';
        }

        $stmtUpdate = $pdo->prepare("UPDATE invoices SET status = ? WHERE id = ?");
        $stmtUpdate->execute([$status, $invoice_id]);

        $pdo->commit();
        header("Location: ../invoices.php?paid=1");
    } catch(Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}