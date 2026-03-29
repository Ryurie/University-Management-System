<?php
// modules/student/process/search_students.php
require_once '../../../config/database.php';

$search = $_POST['query'] ?? '';

// Query: Maghanap base sa pangalan o student number
if(!empty($search)) {
    $stmt = $pdo->prepare("SELECT s.*, p.program_name FROM students s 
                           LEFT JOIN programs p ON s.program_id = p.id 
                           WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_number LIKE ?");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT s.*, p.program_name FROM students s 
                         LEFT JOIN programs p ON s.program_id = p.id 
                         ORDER BY s.id DESC");
}

$results = $stmt->fetchAll();

if(count($results) > 0) {
    foreach($results as $row) {
        // Sa loob ng iyong foreach loop sa search_students.php:
echo "<tr>
        <td>{$row['student_number']}</td>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['program_name']}</td>
        <td style='text-align:right;'>
            <button class='nav-btn' style='color:#0d6efd;' 
                    onclick='viewID(\"{$row['first_name']}\", \"{$row['last_name']}\", \"{$row['student_number']}\", \"{$row['program_name']}\", \"{$row['photo']}\")'>
                🆔 View ID
            </button>
            
            <button class='nav-btn' style='color:#dc3545;' onclick='removeStudent({$row['id']})'>🗑️ Remove</button>
        </td>
      </tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center; padding:50px; color:gray;'>Walang nahanap na record.</td></tr>";
}