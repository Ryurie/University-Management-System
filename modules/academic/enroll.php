<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Kunin ang lahat ng students at courses para sa dropdown
$students = $pdo->query("SELECT id, student_number, first_name, last_name FROM students")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <h2>Student Course Enrollment</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="process/enroll_student.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Student</label>
                    <select name="student_id" class="form-select" required>
                        <?php foreach($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['student_number'] ?> - <?= $s['first_name'] ?> <?= $s['last_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Course to Enroll</label>
                    <select name="course_id" class="form-select" required>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['course_code'] ?> - <?= $c['course_title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester" class="form-select">
                        <option>1st Semester 2026</option>
                        <option>2nd Semester 2026</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">Confirm Enrollment</button>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>