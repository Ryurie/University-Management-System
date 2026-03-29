<?php
// modules/admin/receipt.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    die("Access Denied.");
}

if (!isset($_GET['id'])) {
    die("Invoice ID is missing.");
}

$invoice_id = $_GET['id'];

try {
    // Kunin ang kumpletong impormasyon para sa resibo
    $stmt = $pdo->prepare("
        SELECT i.*, s.student_no, s.first_name, s.last_name, c.course_code 
        FROM invoices i
        JOIN students s ON i.student_id = s.id
        LEFT JOIN courses c ON s.course_id = c.id
        WHERE i.id = ?
    ");
    $stmt->execute([$invoice_id]);
    $data = $stmt->fetch();

    if (!$data) {
        die("Record not found.");
    }

    $balance = $data['total_amount'] - $data['paid_amount'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt - <?= htmlspecialchars($data['student_no']) ?></title>
    <style>
        /* RECEIPT STYLING (Black & White for Printers) */
        body { font-family: 'Courier New', Courier, monospace; background: #e2e8f0; margin: 0; padding: 20px; display: flex; justify-content: center; }
        
        .receipt-container { 
            background: white; width: 100%; max-width: 400px; padding: 30px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); color: black; border-top: 5px solid #10b981;
        }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #ccc; padding-bottom: 20px; }
        .school-name { font-size: 22px; font-weight: 900; margin: 0; letter-spacing: 1px;}
        .receipt-title { font-size: 14px; font-weight: bold; margin-top: 5px; color: #555; }
        .date-time { font-size: 12px; margin-top: 5px; color: #777;}

        .student-details { margin-bottom: 20px; font-size: 14px; line-height: 1.6; }
        .student-details span { font-weight: bold; }

        .transaction-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        .transaction-table th, .transaction-table td { padding: 8px 0; border-bottom: 1px solid #eee; text-align: left; }
        .transaction-table th { text-align: right; color: #555; width: 50%; font-weight: normal;}
        .transaction-table td { text-align: right; font-weight: bold; }
        
        .total-row { font-size: 18px; border-top: 2px solid black !important; }

        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #555; border-top: 2px dashed #ccc; padding-top: 20px;}
        
        .print-btn { 
            display: block; width: 100%; padding: 15px; background: #10b981; color: white; 
            text-align: center; text-decoration: none; font-weight: bold; border-radius: 5px; 
            margin-top: 20px; border: none; cursor: pointer; font-family: inherit; font-size: 16px;
        }

        /* Kapag pinrint talaga, itatago natin 'yung background at 'yung Print Button para malinis sa papel */
        @media print {
            body { background: white; padding: 0; display: block;}
            .receipt-container { box-shadow: none; max-width: 100%; border-top: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="header">
            <h1 class="school-name">BENRU'S NETWORK</h1>
            <div class="receipt-title">OFFICIAL RECEIPT</div>
            <div class="date-time">Date: <?= date('F j, Y, g:i A') ?></div>
            <div class="date-time">Ref No: INV-000<?= htmlspecialchars($data['id']) ?></div>
        </div>

        <div class="student-details">
            <div>Student: <span><?= htmlspecialchars($data['first_name'] . ' ' . $data['last_name']) ?></span></div>
            <div>Student No: <span><?= htmlspecialchars($data['student_no']) ?></span></div>
            <div>Program: <span><?= htmlspecialchars($data['course_code'] ?? 'N/A') ?></span></div>
        </div>

        <table class="transaction-table">
            <tr>
                <th>Total Assessment:</th>
                <td>₱ <?= number_format($data['total_amount'], 2) ?></td>
            </tr>
            <tr>
                <th>Amount Paid:</th>
                <td style="color: #10b981;">₱ <?= number_format($data['paid_amount'], 2) ?></td>
            </tr>
            <tr class="total-row">
                <th style="font-weight: bold; color: black;">Balance Due:</th>
                <td>₱ <?= number_format($balance, 2) ?></td>
            </tr>
        </table>

        <div class="footer">
            <p>Cashier: <?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></p>
            <p>*** This document is system generated ***</p>
        </div>

        <button class="print-btn" onclick="window.print()">🖨️ Print this Receipt</button>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>

</body>
</html>