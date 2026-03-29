<?php
session_start();
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $student_number = $_POST['student_number']; // Ito ang magiging USERNAME
    $program_id = $_POST['program_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 1. Handle Profile Photo Upload
    $photo_name = "default_user.png";
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = time() . '_' . $_FILES['photo']['name'];
        // TAMA: Gagamit ng tmp_name
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../../assets/uploads/profiles/" . $photo_name);
    }

    try {
        $pdo->beginTransaction();

        // I-check muna kung existing na ang Student Number (Username)
        $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkUser->execute([$student_number]);
        if ($checkUser->rowCount() > 0) {
            echo "Error: Ang Student Number na ito ay gamit na ng ibang account.";
            exit();
        }

        // Insert to Users
        $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Student')");
        $stmtUser->execute([$student_number, $password]);
        $user_id = $pdo->lastInsertId();

        // Insert to Students
        $stmtStudent = $pdo->prepare("INSERT INTO students (user_id, student_number, first_name, last_name, program_id, photo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtStudent->execute([$user_id, $student_number, $first_name, $last_name, $program_id, $photo_name]);
        $student_db_id = $pdo->lastInsertId();

        // 2. Handle Document Uploads (PSA & F138)
        $docs = ['doc_psa' => 'PSA Birth Certificate', 'doc_f138' => 'Form 138'];
        foreach ($docs as $key => $label) {
            if (!empty($_FILES[$key]['name'])) {
                $file_name = time() . '_' . $_FILES[$key]['name'];
                if (move_uploaded_file($_FILES[$key]['tmp_name'], "../../../assets/uploads/documents/" . $file_name)) {
                    $stmtDoc = $pdo->prepare("INSERT INTO student_documents (student_id, doc_type, file_path) VALUES (?, ?, ?)");
                    $stmtDoc->execute([$student_db_id, $label, $file_name]);
                }
            }
        }

        $pdo->commit();
        echo "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}