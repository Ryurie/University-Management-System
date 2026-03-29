<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
    $target = "../../../assets/images/profiles/" . $filename;

    // Check kung image ba talaga
    $check = getimagesize($file['tmp_name']);
    if($check !== false) {
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE students SET profile_pic = ? WHERE user_id = ?");
            $stmt->execute([$filename, $_SESSION['user_id']]);
            $_SESSION['success'] = "Photo updated successfully!";
        }
    }
    header("Location: ../profile.php");
    exit();
}