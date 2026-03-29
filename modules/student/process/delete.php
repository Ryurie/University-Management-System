<?php
// modules/student/process/delete.php
session_start();
require_once '../../../config/database.php';

// Security: Check kung Registrar o Admin ang nagbubura
if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Registrar' && $_SESSION['role'] !== 'Admin')){ 
    echo "Unauthorized access.";
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $student_id = $_POST['id'];

    try {
        $pdo->beginTransaction();

        // 1. Kunin muna ang user_id para mabura rin sa users table
        $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();

        if ($student) {
            $user_id = $student['user_id'];

            // 2. Burahin sa students table
            $delStudent = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $delStudent->execute([$student_id]);

            // 3. Burahin sa users table (para hindi sayang sa space at iwas kalat)
            $delUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delUser->execute([$user_id]);

            $pdo->commit();
            echo "success";
        } else {
            echo "Student not found.";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}