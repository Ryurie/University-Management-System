<?php
// modules/academic/process/fetch_enrollments.php
require_once '../../../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT e.enrollment_date, s.first_name, s.last_name, c.section_name, crs.course_code 
        FROM enrollments e 
        JOIN students s ON e.student_id = s.id 
        JOIN classes c ON e.class_id = c.id 
        JOIN courses crs ON c.course_id = crs.id 
        ORDER BY e.id DESC LIMIT 10
    ");
    $data = $stmt->fetchAll();

    if (count($data) > 0) {
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['last_name']) . ", " . htmlspecialchars($row['first_name']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row['section_name']) . " (" . htmlspecialchars($row['course_code']) . ")</td>";
            echo "<td>" . date('M d, Y', strtotime($row['enrollment_date'])) . "</td>";
            echo "</tr>";
        }
    } else { 
        echo "<tr><td colspan='3' style='text-align:center;'>No enrollments yet.</td></tr>"; 
    }
} catch (PDOException $e) { 
    echo "<tr><td colspan='3' style='color:red;'>Error: " . $e->getMessage() . "</td></tr>"; 
}
?>