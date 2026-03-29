<?php
// modules/admin/process/delete_course.php
session_start();
require_once '../../../config/database.php';

// 1. Security Check: Dapat Admin lang ang pwedeng mag-delete
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access.");
}

// 2. Check kung may pinadalang ID
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        // 3. Check muna natin kung may students na naka-enroll sa course na ito
        // Para hindi mag-error ang database (Foreign Key Constraint)
        $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE program_id = ?");
        $check->execute([$id]);
        
        if ($check->fetchColumn() > 0) {
            die("Hindi pwedeng burahin ang course na ito dahil may mga students pang naka-enroll dito.");
        }

        // 4. Kung wala nang students, burahin na ang course (program)
        $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "success";
        } else {
            echo "Failed to delete the course.";
        }

    } catch (PDOException $e) {
        // I-return ang specific database error
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request: No ID provided.";
}