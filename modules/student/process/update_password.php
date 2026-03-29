<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_pass, $_SESSION['user_id']]);
        $_SESSION['success'] = "Password updated successfully!";
    } catch(PDOException $e) {
        die("Error updating password.");
    }
    header("Location: ../profile.php");
    exit();
}