<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// Security: Kick out if not authorized
if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Registrar')){
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit();
}

// Fetch programs for dropdown
$programs = $pdo->query("SELECT * FROM programs")->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h4 class="mb-0 fw-bold text-primary">Student Registration</h4>
                    <p class="text-muted mb-0 small">Punan ang form para makagawa ng bagong student account.</p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success border-0 shadow-sm"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger border-0 shadow-sm"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <form action="process/register.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name</label>
                                <input type="text" name="first_name" class="form-control" required placeholder="Juan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required placeholder="Dela Cruz">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Student Number</label>
                                <input type="text" name="student_number" class="form-control" placeholder="2026-0001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Program / Course</label>
                                <select name="program_id" class="form-select" required>
                                    <option value="">-- Choose Course --</option>
                                    <?php foreach($programs as $prog): ?>
                                        <option value="<?= $prog['id'] ?>"><?= $prog['program_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="p-3 bg-light rounded border">
                                    <label class="form-label fw-bold mb-0">Security</label>
                                    <p class="small text-muted mb-2">Set ang initial password para sa student login.</p>
                                    <input type="password" name="password" class="form-control" required placeholder="Enter temporary password">
                                </div>
                            </div>
                            <div class="col-12 mt-4 d-grid">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">Confirm Registration</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>