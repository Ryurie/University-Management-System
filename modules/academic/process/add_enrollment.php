<?php
// modules/academic/process/add_enrollment.php
session_start();
require_once '../../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];

    try {
        // Check kung naka-enroll na ang student sa klase na ito
        $check = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?");
        $check->execute([$student_id, $class_id]);
        
        if ($check->rowCount() > 0) {
            die("Student is already enrolled in this class.");
        }

        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, class_id) VALUES (?, ?)");
        if ($stmt->execute([$student_id, $class_id])) {
            echo "success";
        }
    } catch (PDOException $e) { 
        echo "Error: " . $e->getMessage(); 
    }
}
?>