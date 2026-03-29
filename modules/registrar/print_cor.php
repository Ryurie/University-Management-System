<?php
// modules/registrar/print_cor.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Pwedeng pumasok ang Registrar o Admin
if(!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Registrar', 'Admin'])){ 
    die("Access Denied.");
}

$student_id = $_GET['id'] ?? 0;

try {
    // 1. Kunin ang impormasyon ng estudyante
    $stmt_student = $pdo->prepare("
        SELECT s.*, c.course_name, c.course_code 
        FROM students s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.id = ?
    ");
    $stmt_student->execute([$student_id]);
    $student = $stmt_student->fetch();

    if(!$student) {
        die("<h2 style='text-align:center; padding: 50px;'>Student not found.</h2>");
    }

    // 2. Kunin ang mga enrolled subjects niya
    $stmt_classes = $pdo->prepare("
        SELECT co.course_code AS subject_code, co.course_name AS subject_name, 
               c.schedule, c.room, 
               CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
        FROM enrollments e 
        JOIN classes c ON e.class_id = c.id 
        LEFT JOIN courses co ON c.course_id = co.id 
        LEFT JOIN teachers t ON c.teacher_id = t.id 
        WHERE e.student_id = ?
    ");
    $stmt_classes->execute([$student_id]);
    $enrolled_classes = $stmt_classes->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COR - <?= htmlspecialchars($student['last_name']) ?></title>
    <style>
        /* General Web Styling */
        body { background: #e2e8f0; font-family: 'Times New Roman', Times, serif; color: #000; margin: 0; padding: 20px; }
        
        .paper-container {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Toolbar para sa printing */
        .no-print-toolbar { text-align: center; margin-bottom: 20px; }
        .btn-print { background: #e11d48; color: white; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 5px; font-family: Arial, sans-serif;}
        .btn-back { background: gray; color: white; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 5px; text-decoration: none; font-family: Arial, sans-serif; margin-right: 10px;}
        
        /* Header Section */
        .doc-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .university-name { font-size: 24px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .university-sub { font-size: 14px; margin-bottom: 10px; }
        .doc-title { font-size: 20px; font-weight: bold; margin-top: 15px; letter-spacing: 2px; }

        /* Student Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 25px; font-size: 14px; }
        .info-row { display: flex; }
        .info-label { width: 120px; font-weight: bold; }
        .info-value { border-bottom: 1px solid #ccc; flex: 1; text-transform: uppercase; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f0f0f0; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }

        /* Signatures */
        .signature-section { display: flex; justify-content: space-between; margin-top: 50px; }
        .sign-box { width: 30%; text-align: center; font-size: 14px; }
        .sign-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; font-weight: bold; text-transform: uppercase;}

        /* MAGIC CSS PARA SA PRINTER */
        @media print {
            @page { size: A4; margin: 15mm; }
            body { background: white; padding: 0; }
            .paper-container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print-toolbar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <a href="index.php" class="btn-back">⬅️ Back</a>
        <button onclick="window.print()" class="btn-print">🖨️ Print Document</button>
    </div>

    <div class="paper-container">
        
        <div class="doc-header">
            <div class="university-name">University Management System</div>
            <div class="university-sub">Concepcion, Central Luzon, Philippines<br>Office of the University Registrar</div>
            <div class="doc-title">CERTIFICATE OF REGISTRATION</div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-row"><div class="info-label">Student No:</div><div class="info-value"><?= htmlspecialchars($student['student_no']) ?></div></div>
                <div class="info-row" style="margin-top: 8px;"><div class="info-label">Name:</div><div class="info-value" style="font-weight: bold;"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></div></div>
            </div>
            <div>
                <div class="info-row"><div class="info-label">Program:</div><div class="info-value"><?= htmlspecialchars($student['course_code'] ?? 'N/A') ?></div></div>
                <div class="info-row" style="margin-top: 8px;"><div class="info-label">Academic Year:</div><div class="info-value">2025 - 2026</div></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Section / Code</th>
                    <th style="width: 35%;">Subject Description</th>
                    <th style="width: 10%;">Units</th>
                    <th style="width: 25%;">Schedule & Room</th>
                    <th style="width: 15%;">Instructor</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($enrolled_classes) > 0): ?>
                    <?php foreach($enrolled_classes as $class): ?>
                        <tr>
                            <td class="text-center"><strong><?= htmlspecialchars($class['subject_code']) ?></strong></td>
                            <td><?= htmlspecialchars($class['subject_name']) ?></td>
                            <td class="text-center">3.0</td> <td style="font-size: 11px;"><?= htmlspecialchars($class['schedule']) ?> <br> (Rm: <?= htmlspecialchars($class['room']) ?>)</td>
                            <td style="font-size: 12px;"><?= htmlspecialchars($class['teacher_name'] ?? 'TBA') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL UNITS:</td>
                        <td class="text-center" style="font-weight: bold;"><?= count($enrolled_classes) * 3 ?>.0</td>
                        <td colspan="2"></td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center" style="padding: 20px;">No subjects enrolled for this semester.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="font-size: 12px; margin-bottom: 40px;">
            <em>* This document is computer-generated and is valid only if it bears the official signature of the University Registrar. Any erasure or alteration invalidates this certificate.</em>
        </div>

        <div class="signature-section">
            <div class="sign-box">
                <div class="sign-line"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
                <div style="font-size: 12px; color: gray;">Student Signature</div>
            </div>
            
            <div class="sign-box">
                <div class="sign-line"><?= htmlspecialchars($_SESSION['first_name']) ?></div>
                <div style="font-size: 12px; color: gray;">University Registrar</div>
            </div>
        </div>

    </div>

</body>
</html>