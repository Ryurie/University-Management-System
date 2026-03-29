<?php
// modules/admin/process/add_payment.php
session_start();
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ die("Unauthorized Access"); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $amount_paid = (float)$_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];
    $reference_no = trim($_POST['reference_no']);

    try {
        $pdo->beginTransaction(); // Start Transaction para safe ang pera

        // 1. I-save ang payment record
        $stmt = $pdo->prepare("INSERT INTO payments (student_id, amount_paid, payment_method, reference_no) VALUES (?, ?, ?, ?)");
        $stmt->execute([$student_id, $amount_paid, $payment_method, $reference_no]);

        // 2. I-update ang Invoices ng estudyante (FIFO - First In, First Out)
        $remaining_payment = $amount_paid;
        
        // Kunin ang mga Unpaid o Partial na invoices ng student na ito
        $invoices = $pdo->prepare("SELECT * FROM invoices WHERE student_id = ? AND status != 'Paid' ORDER BY id ASC");
        $invoices->execute([$student_id]);
        
        foreach ($invoices->fetchAll() as $inv) {
            if ($remaining_payment <= 0) break; // Kung ubos na ang binayad, stop na

            $balance = $inv['total_amount'] - $inv['paid_amount'];
            
            if ($remaining_payment >= $balance) {
                // Kayang bayaran ng buo yung invoice na ito
                $pdo->query("UPDATE invoices SET paid_amount = total_amount, status = 'Paid' WHERE id = " . $inv['id']);
                $remaining_payment -= $balance;
            } else {
                // Partial payment lang sa invoice na ito
                $new_paid = $inv['paid_amount'] + $remaining_payment;
                $pdo->query("UPDATE invoices SET paid_amount = $new_paid, status = 'Partial' WHERE id = " . $inv['id']);
                $remaining_payment = 0;
            }
        }

        $pdo->commit();
        echo "success";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Database Error: " . $e->getMessage();
    }
}
?>