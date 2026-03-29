<?php
// modules/academic/process/save_grade.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    echo "Unauthorized access."; exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'enroll') {
            // -- LOGIC PARA SA PAG-ENROLL NG ESTUDYANTE --
            $student_id = $_POST['student_id'] ?? '';
            $class_id = $_POST['class_id'] ?? '';

            if(empty($student_id) || empty($class_id)) {
                echo "Please select both student and class."; exit();
            }

            // Check duplicate enrollment
            $check = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?");
            $check->execute([$student_id, $class_id]);
            if($check->rowCount() > 0) {
                echo "Student is already enrolled in this class."; exit();
            }

            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, class_id) VALUES (?, ?)");
            if($stmt->execute([$student_id, $class_id])) {
                echo "success";
            } else {
                echo "Failed to enroll student.";
            }

        } elseif ($action === 'grade') {
            // -- LOGIC PARA SA PAG-ENCODE NG GRADE --
            $enrollment_id = $_POST['enrollment_id'] ?? '';
            $grade = $_POST['grade'] ?? 'TBA';
            $remarks = $_POST['remarks'] ?? 'Pending';

            if(empty($enrollment_id)) {
                echo "Invalid enrollment record."; exit();
            }

            $stmt = $pdo->prepare("UPDATE enrollments SET grade = ?, remarks = ? WHERE id = ?");
            if($stmt->execute([$grade, $remarks, $enrollment_id])) {
                echo "success";
            } else {
                echo "Failed to update grade.";
            }
        } else {
            echo "Unknown action.";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>