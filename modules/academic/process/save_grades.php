<?php
// modules/academic/process/save_grades.php
require_once '../../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['grades'])) {
    try {
        $pdo->beginTransaction();

        // Gagamit tayo ng ON DUPLICATE KEY UPDATE para kung may grade na, i-uupdate na lang niya imbes na mag-error
        $stmt = $pdo->prepare("
            INSERT INTO grades (enrollment_id, prelim, midterm, finals, final_grade, remarks) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            prelim = VALUES(prelim), midterm = VALUES(midterm), finals = VALUES(finals), 
            final_grade = VALUES(final_grade), remarks = VALUES(remarks)
        ");

        foreach ($_POST['grades'] as $enrollment_id => $g) {
            $prelim = ($g['prelim'] !== '') ? (float)$g['prelim'] : null;
            $midterm = ($g['midterm'] !== '') ? (float)$g['midterm'] : null;
            $finals = ($g['finals'] !== '') ? (float)$g['finals'] : null;

            $final_grade = null;
            $remarks = null;

            // Kung kumpleto na ang 3 grades, automatic niyang iko-compute ang Final at Remarks
            if ($prelim !== null && $midterm !== null && $finals !== null) {
                $final_grade = round(($prelim + $midterm + $finals) / 3, 2);
                $remarks = ($final_grade >= 75) ? 'Passed' : 'Failed'; // 75 ang passing natin
            }

            $stmt->execute([$enrollment_id, $prelim, $midterm, $finals, $final_grade, $remarks]);
        }

        $pdo->commit();
        echo "success";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No data received.";
}
?>