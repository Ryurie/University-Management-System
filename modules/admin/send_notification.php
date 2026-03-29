<?php
// modules/admin/process/send_notification.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    echo "Unauthorized access."; exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $target_audience = $_POST['target_audience'] ?? 'All';

    if(empty($title) || empty($message)) {
        echo "Please provide a title and a message.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, target_audience) VALUES (?, ?, ?)");
        if($stmt->execute([$title, $message, $target_audience])) {
            echo "success";
        } else {
            echo "Failed to send announcement.";
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>