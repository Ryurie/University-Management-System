<?php
// modules/admin/process/fetch_students.php
require_once '../../../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    $data = $stmt->fetchAll();

    if (count($data) > 0) {
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td><strong style='color:var(--primary-color);'>" . htmlspecialchars($row['student_number']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['last_name']) . ", " . htmlspecialchars($row['first_name']) . "</strong></td>";
            echo "<td>" . (htmlspecialchars($row['email']) ?: 'N/A') . "</td>";
            echo "<td>" . (htmlspecialchars($row['contact_number']) ?: 'N/A') . "</td>";
            echo "</tr>";
        }
    } else { 
        echo "<tr><td colspan='4' style='text-align:center;'>No students registered yet.</td></tr>"; 
    }
} catch (PDOException $e) { echo "<tr><td colspan='4' style='color:red;'>Error: " . $e->getMessage() . "</td></tr>"; }
?>