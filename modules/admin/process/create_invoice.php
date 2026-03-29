<?php
// modules/admin/process/create_invoice.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Unauthorized access."; exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $fee_id = $_POST['fee_id'] ?? '';
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;

    if(empty($student_id) || empty($fee_id)) {
        echo "Please select both a student and a fee structure.";
        exit();
    }

    try {
        // 1. Kunin muna kung magkano yung total amount ng piniling fee_id
        $stmt_fee = $pdo->prepare("SELECT amount FROM fee_structures WHERE id = ?");
        $stmt_fee->execute([$fee_id]);
        $fee_data = $stmt_fee->fetch();

        if(!$fee_data) {
            echo "Invalid Fee Structure selected.";
            exit();
        }

        $total_amount = $fee_data['amount'];

        // 2. I-check kung may existing invoice na yung student para sa fee na 'to (iwas double billing)
        $check = $pdo->prepare("SELECT id FROM invoices WHERE student_id = ? AND fee_id = ? AND status != 'Paid'");
        $check->execute([$student_id, $fee_id]);
        if($check->rowCount() > 0) {
            echo "Student already has an active invoice for this fee.";
            exit();
        }

        // 3. Gawaing utang (Insert Invoice)
        $insert = $pdo->prepare("INSERT INTO invoices (student_id, fee_id, total_amount, paid_amount, status, due_date) VALUES (?, ?, ?, 0.00, 'Unpaid', ?)");
        if($insert->execute([$student_id, $fee_id, $total_amount, $due_date])) {
            echo "success";
        } else {
            echo "Failed to generate invoice.";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>