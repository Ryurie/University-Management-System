<?php
// modules/auth/process/login_process.php
session_start();
require_once '../../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'] ?? '';
    $identifier = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($role) || empty($identifier) || empty($password)) {
        echo "Please fill in all fields completely.";
        exit();
    }

    try {
        if ($role === 'Student') {
            // LOGIN PARA SA STUDENT
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_number = ? AND password = ? LIMIT 1");
            $stmt->execute([$identifier, $password]);
            $student = $stmt->fetch();

            if ($student) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_number'] = $student['student_number'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['role'] = 'Student';
                echo "success"; 
            } else {
                echo "Invalid Student Number or Password.";
            }

        } elseif ($role === 'Teacher') {
            // LOGIN PARA SA TEACHER (BAGO!)
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE employee_number = ? AND password = ? LIMIT 1");
            $stmt->execute([$identifier, $password]);
            $teacher = $stmt->fetch();

            if ($teacher) {
                $_SESSION['teacher_id'] = $teacher['id'];
                $_SESSION['employee_number'] = $teacher['employee_number'];
                $_SESSION['teacher_name'] = $teacher['first_name'] . ' ' . $teacher['last_name'];
                $_SESSION['role'] = 'Teacher';
                echo "success"; 
            } else {
                echo "Invalid Employee Number or Password.";
            }

        } else {
            // LOGIN PARA SA ADMIN / REGISTRAR
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? LIMIT 1");
            $stmt->execute([$identifier, $password]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = 'Admin'; // Default admin
                echo "success"; 
            } else {
                echo "Invalid Administrator Credentials.";
            }
        }
    } catch (PDOException $e) {
        echo "Database Connection Error.";
    }
} else {
    echo "Invalid Request.";
}
?>