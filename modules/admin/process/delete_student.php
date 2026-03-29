<?php
// modules/admin/process/delete_student.php
session_start();
require_once '../../../config/database.php';

// Security Check: Admin Only
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Error: Unauthorized access.";
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if(empty($student_id)) {
        echo "Error: Invalid Student ID.";
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Kunin muna ang user_id bago burahin ang student record
        // (Para mabura rin natin yung login credentials niya sa 'users' table)
        $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $user_id = $stmt->fetchColumn();

        // 2. Burahin ang student sa 'students' table
        $stmtDeleteStudent = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmtDeleteStudent->execute([$student_id]);

        // 3. Burahin ang login account sa 'users' table
        if($user_id) {
            $stmtDeleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtDeleteUser->execute([$user_id]);
        }

        $pdo->commit();
        echo "success";

    } catch (PDOException $e) {
        $pdo->rollBack();
        // Kung mag-error ito, ibig sabihin may record pa siya sa Finance o Grades na kailangang burahin muna
        echo "Error: Cannot delete this student because they have existing records (Invoices/Grades).";
    }
}
?>