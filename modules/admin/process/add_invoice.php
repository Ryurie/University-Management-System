<?php
// modules/admin/process/add_invoice.php
session_start();
require_once '../../../config/database.php';

// Security Check: Siguraduhing Admin lang ang makakapag-generate ng invoice
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Error: Unauthorized access.";
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Kunin at linisin ang mga data mula sa JS FormData
    $student_id = filter_var($_POST['student_id'], FILTER_VALIDATE_INT);
    $description = trim($_POST['description']);
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    $due_date = trim($_POST['due_date']);

    // 2. Validation (Bawal ang blanko o maling amount)
    if(empty($student_id) || empty($description) || empty($amount) || empty($due_date)) {
        echo "Error: All fields are required.";
        exit();
    }

    if($amount <= 0) {
        echo "Error: Amount must be greater than zero.";
        exit();
    }

    try {
        // 3. I-save ang Invoice sa database (Default status ay 'Pending')
        $stmt = $pdo->prepare("
            INSERT INTO invoices (student_id, description, amount, due_date, status) 
            VALUES (?, ?, ?, ?, 'Pending')
        ");
        
        if($stmt->execute([$student_id, $description, $amount, $due_date])) {
            echo "success";
        } else {
            echo "Error: Failed to generate invoice.";
        }

    } catch (PDOException $e) {
        // Kapag may error sa SQL/Database
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Error: Invalid request method.";
}
?>