<?php
session_start();
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    die("<tr><td colspan='4'>Unauthorized Access</td></tr>");
}

try {
    // Kukunin natin ang Class Data + Pangalan ng Course + Pangalan ng Prof + Bilang ng Enrolled Students
    $stmt = $pdo->query("
        SELECT cls.*, c.course_code, c.course_name, f.first_name, f.last_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = cls.id) as enrolled_count
        FROM classes cls
        JOIN courses c ON cls.course_id = c.id
        JOIN faculties f ON cls.faculty_id = f.id
        ORDER BY cls.id DESC
    ");
    
    $classes = $stmt->fetchAll();

    if (count($classes) > 0) {
        foreach ($classes as $row) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['section_name']) . "</strong><br><small style='color:gray;'>" . htmlspecialchars($row['course_code']) . " - " . htmlspecialchars($row['course_name']) . "</small></td>";
            echo "<td>" . htmlspecialchars($row['schedule']) . "<br><small style='color:gray;'>" . htmlspecialchars($row['room']) . " | " . htmlspecialchars($row['semester']) . "</small></td>";
            echo "<td>Prof. " . htmlspecialchars($row['last_name']) . ", " . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td style='text-align:right; font-weight:bold; color:var(--primary-color);'>" . $row['enrolled_count'] . " Enrolled</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='text-align:center; padding: 40px; color: gray;'>No active classes found. Open a new class!</td></tr>";
    }

} catch (PDOException $e) {
    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
}
?>