<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if($_SESSION['role'] !== 'Admin'){ die("Access Denied."); }

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <div class="display-1 text-warning mb-4">🗄️</div>
            <h2 class="fw-bold">Database Backup</h2>
            <p class="text-muted mb-4">I-download ang pinakabagong kopya ng iyong database para masiguradong safe ang iyong data.</p>
            
            <a href="process/export_db.php" class="btn btn-primary btn-lg px-5 shadow">Download SQL Backup</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>