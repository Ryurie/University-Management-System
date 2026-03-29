<?php
// modules/academic/process/add_class.php
session_start();
require_once '../../../config/constants.php';
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    echo "Unauthorized access."; exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    $subject_code = trim($_POST['subject_code'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $room = trim($_POST['room'] ?? '');

    if(empty($course_id) || empty($teacher_id) || empty($subject_code) || empty($schedule) || empty($room)) {
        echo "Please fill in all required fields.";
        exit();
    }

    try {
        // I-check kung may conflict sa kwarto at schedule (Basic checking)
        $check = $pdo->prepare("SELECT id FROM classes WHERE room = ? AND schedule = ? AND status = 'Active'");
        $check->execute([$room, $schedule]);
        if($check->rowCount() > 0) {
            echo "Schedule and Room conflict detected! Another class is already using this slot.";
            exit();
        }

        // I-save sa database
        $stmt = $pdo->prepare("INSERT INTO classes (course_id, teacher_id, subject_code, subject_name, schedule, room, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
        if($stmt->execute([$course_id, $teacher_id, $subject_code, $subject_name, $schedule, $room])) {
            echo "success";
        } else {
            echo "Failed to create class.";
        }

    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request Method.";
}
?>