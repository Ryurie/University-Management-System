<?php
session_start();
require_once '../../../config/database.php';

// Rate settings (Sa totoong project, kukunin ito sa database settings table)
$rate_per_unit = 500;
$misc_fee = 2500;

try {
    // Kunin ang mga estudyante na may enrollment pero wala pang invoice
    $students = $pdo->query("SELECT s.id, SUM(c.credits) as total_units 
                             FROM students s 
                             JOIN enrollments e ON s.id = e.student_id 
                             JOIN courses c ON e.course_id = c.id 
                             GROUP BY s.id")->fetchAll();

    foreach($students as $s) {
        $total_amount = ($s['total_units'] * $rate_per_unit) + $misc_fee;
        $invoice_no = "INV-" . date('Y') . "-" . str_pad($s['id'], 4, '0', STR_PAD_LEFT);

        // I-save ang invoice
        $stmt = $pdo->prepare("INSERT IGNORE INTO invoices (student_id, invoice_no, total_amount, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$s['id'], $invoice_no, $total_amount, date('Y-12-31')]);
    }

    header("Location: ../invoices.php?success=Generated");
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}