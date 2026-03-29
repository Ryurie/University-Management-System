<?php
// modules/admin/process/add_fee.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Unauthorized access.";
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kunin ang data mula sa form
    $fee_name = trim($_POST['fee_name'] ?? '');
    $amount = $_POST['amount'] ?? 0;
    $description = trim($_POST['description'] ?? '');

    // Basic Validation
    if(empty($fee_name) || empty($amount) || $amount <= 0) {
        echo "Please provide a valid Fee Name and Amount.";
        exit();
    }

    try {
        // I-check kung may kaparehas na fee name para iwas duplicate (optional, pero good practice)
        $check = $pdo->prepare("SELECT id FROM fee_structures WHERE fee_name = ?");
        $check->execute([$fee_name]);
        if($check->rowCount() > 0) {
            echo "A fee structure with this name already exists!";
            exit();
        }

        // I-save sa database
        $stmt = $pdo->prepare("INSERT INTO fee_structures (fee_name, amount, description) VALUES (?, ?, ?)");
        if($stmt->execute([$fee_name, $amount, $description])) {
            echo "success"; // Ito 'yung binabasa ng JavaScript natin para mag-reload ang page
        } else {
            echo "Failed to save fee structure.";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>