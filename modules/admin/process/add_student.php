<?php
// modules/admin/process/add_student.php
require_once '../../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);

    try {
        // Check kung nag-e-exist na yung student ID
        $check = $pdo->prepare("SELECT id FROM students WHERE student_number = ?");
        $check->execute([$student_number]);
        if ($check->rowCount() > 0) {
            die("Student ID Number already exists.");
        }

        $stmt = $pdo->prepare("INSERT INTO students (student_number, first_name, last_name, email, contact_number) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$student_number, $first_name, $last_name, $email, $contact_number])) {
            echo "success";
        }
    } catch (PDOException $e) { echo "Error: " . $e->getMessage(); }
}
?>