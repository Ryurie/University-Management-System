<?php
// modules/admin/process/fetch_invoices.php
require_once '../../../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT i.*, s.first_name, s.last_name 
        FROM invoices i 
        JOIN students s ON i.student_id = s.id 
        ORDER BY i.id DESC
    ");
    $data = $stmt->fetchAll();

    if (count($data) > 0) {
        foreach ($data as $row) {
            // Setup ng Badge Colors
            $badge = "<span style='background:#fee2e2; color:#ef4444; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold;'>UNPAID</span>";
            if ($row['status'] == 'Partial') {
                $badge = "<span style='background:#fef3c7; color:#f59e0b; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold;'>PARTIAL</span>";
            } elseif ($row['status'] == 'Paid') {
                $badge = "<span style='background:#d1fae5; color:#10b981; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold;'>PAID</span>";
            }

            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['last_name']) . ", " . htmlspecialchars($row['first_name']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td style='font-weight:bold;'>₱ " . number_format($row['total_amount'], 2) . "</td>";
            echo "<td style='color:var(--primary-color);'>₱ " . number_format($row['paid_amount'], 2) . "</td>";
            echo "<td>" . $badge . "</td>";
            echo "</tr>";
        }
    } else { 
        echo "<tr><td colspan='5' style='text-align:center;'>No invoices found.</td></tr>"; 
    }
} catch (PDOException $e) { echo "<tr><td colspan='5' style='color:red;'>Error: " . $e->getMessage() . "</td></tr>"; }
?>