<?php
session_start();
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    die("Unauthorized Access");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $program_id = $_POST['program_id'];
    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $units = $_POST['units'];

    try {
        // Check muna kung may kaparehong course code para iwas duplicate
        $check = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
        $check->execute([$course_code]);
        if ($check->fetchColumn() > 0) {
            die("Error: Course Code '{$course_code}' already exists!");
        }

        // Insert sa database
        $stmt = $pdo->prepare("INSERT INTO courses (program_id, course_code, course_name, units) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$program_id, $course_code, $course_name, $units])) {
            echo "success";
        } else {
            echo "Failed to save course.";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
}
?>