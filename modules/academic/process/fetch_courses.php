<?php
session_start();
require_once '../../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Academic')){ 
    die("<tr><td colspan='4'>Unauthorized Access</td></tr>");
}

try {
    // Kukunin natin ang course pati na rin ang pangalan ng program niya
    $stmt = $pdo->query("
        SELECT c.*, p.program_name 
        FROM courses c 
        LEFT JOIN programs p ON c.program_id = p.id 
        ORDER BY c.course_code ASC
    ");
    
    $courses = $stmt->fetchAll();

    if (count($courses) > 0) {
        foreach ($courses as $row) {
            echo "<tr>";
            echo "<td style='font-weight: bold; color: var(--primary-color);'>" . htmlspecialchars($row['course_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['course_name']) . "<br><small style='color: gray;'>" . htmlspecialchars($row['program_name']) . "</small></td>";
            echo "<td>" . htmlspecialchars($row['units']) . "</td>";
            echo "<td style='text-align:right;'>
                    <button style='background:var(--danger-color); color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:bold;' onclick='alert(\"Delete functionality coming soon!\")'>Delete</button>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='text-align:center; padding: 40px; color: gray;'>No courses found. Create one first!</td></tr>";
    }

} catch (PDOException $e) {
    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
}
?>