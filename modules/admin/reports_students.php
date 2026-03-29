<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Registrar'){
    die("Access Denied.");
}

// Kunin ang data ng lahat ng estudyante
$students = $pdo->query("
    SELECT s.*, p.program_name 
    FROM students s 
    LEFT JOIN programs p ON s.program_id = p.id 
    ORDER BY s.last_name ASC
")->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Enrollment Report</h2>
        <button onclick="window.print()" class="btn btn-dark">🖨️ Print Report</button>
    </div>

    <div class="card shadow-sm border-0 p-4 print-area">
        <div class="text-center mb-4">
            <h4><?= SYSTEM_NAME ?></h4>
            <p class="text-muted">Official Student Enrollment List<br>As of <?= date('F d, Y') ?></p>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Student Number</th>
                    <th>Full Name</th>
                    <th>Program/Course</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($students as $s): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= $s['student_number'] ?></strong></td>
                    <td><?= $s['last_name'] ?>, <?= $s['first_name'] ?></td>
                    <td><?= $s['program_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="mt-5 d-none d-print-block">
            <div class="row">
                <div class="col-6">
                    <p>Prepared by:</p>
                    <br>
                    <strong>__________________________</strong>
                    <p><?= $_SESSION['username'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS para sa Printing */
@media print {
    .no-print, #sidebar, .navbar { display: none !important; }
    #content { width: 100% !important; padding: 0 !important; margin: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .container { max-width: 100% !important; width: 100% !important; }
}
</style>

<?php include '../../includes/footer.php'; ?>