<?php
session_start();
require_once '../../../config/database.php';

// Security Check: Siguraduhing Admin lang ang makakapag-add
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Error: Unauthorized access.";
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Linisin ang text para walang extra spaces sa dulo
    $program_name = trim($_POST['program_name']);

    if(empty($program_name)) {
        echo "Error: Program name cannot be empty.";
        exit();
    }

    try {
        // 1. I-check kung may ganito nang course sa database para iwas duplicate
        $stmtCheck = $pdo->prepare("SELECT id FROM programs WHERE program_name = ?");
        $stmtCheck->execute([$program_name]);
        
        if($stmtCheck->rowCount() > 0) {
            echo "Error: The program '" . htmlspecialchars($program_name) . "' already exists.";
            exit();
        }

        // 2. I-save sa database kapag wala pang duplicate
        $stmtInsert = $pdo->prepare("INSERT INTO programs (program_name) VALUES (?)");
        if($stmtInsert->execute([$program_name])) {
            echo "success";
        } else {
            echo "Error: Failed to save program.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>