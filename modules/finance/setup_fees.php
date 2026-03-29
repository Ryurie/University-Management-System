<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Registrar'){
    die("Access Denied.");
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <h2>Finance Setup</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Set Tuition Rate</div>
                <div class="card-body">
                    <form action="process/update_rates.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Price per Unit (PHP)</label>
                            <input type="number" name="price_per_unit" class="form-control" value="500" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Miscellaneous Fee (PHP)</label>
                            <input type="number" name="misc_fee" class="form-control" value="2500" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Rates</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>