<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Hanapin ang user sa database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // I-verify ang password kung nag-match sa Hashed Password sa DB
        if ($user && password_verify($password, $user['password'])) {
            
            // I-set ang session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // I-redirect depende sa role (admin, faculty, student, registrar)
            $role_folder = strtolower($user['role']);
            header("Location: ../$role_folder/index.php");
            exit();

        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "System Error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>