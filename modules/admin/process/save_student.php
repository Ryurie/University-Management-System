<?php
// modules/admin/process/save_student.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Unauthorized access."; exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_no = trim($_POST['student_no'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $course_id = $_POST['course_id'] ?? null;

    if(empty($student_no) || empty($first_name) || empty($last_name) || empty($course_id)) {
        echo "Please fill in all required fields.";
        exit();
    }

    try {
        // I-check kung may kaparehas na Student Number
        $check = $pdo->prepare("SELECT id FROM students WHERE student_no = ?");
        $check->execute([$student_no]);
        if($check->rowCount() > 0) {
            echo "Student Number already exists!";
            exit();
        }

        // I-save sa database
        $stmt = $pdo->prepare("INSERT INTO students (student_no, first_name, last_name, email, contact_no, course_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        if($stmt->execute([$student_no, $first_name, $last_name, $email, $contact_no, $course_id])) {
            echo "success";
        } else {
            echo "Failed to register student.";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>