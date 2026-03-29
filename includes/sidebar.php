<?php 
// includes/sidebar.php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }

/** * LOGIC: Kung Registrar ang naka-login, i-disable ang sidebar view
 * dahil gumagamit na tayo ng Top Navigation sa modules/registrar/index.php
 */
if(isset($_SESSION['role']) && $_SESSION['role'] === 'Registrar') {
    ?>
    <style>
        /* Tinatanggal ang margin sa kaliwa para sa Registrar */
        #sidebar { display: none !important; }
        #content { 
            margin-left: 0 !important; 
            width: 100% !important; 
            padding: 0 !important;
            transition: none !important;
        }
    </style>
    <?php
    return; // Hihinto na ang code dito, hindi na babasahin ang sidebar sa ibaba
}
?>

<nav id="sidebar" class="bg-dark text-white shadow">
    <div class="p-4 border-bottom border-secondary">
        <h4 class="mb-0 fw-bold text-primary">UMS PORTAL</h4>
        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">
            Mode: <?= $_SESSION['role'] ?>
        </small>
    </div>
    
    <ul class="list-unstyled components px-3 mt-3">
        <li class="nav-item mb-2">
            <a href="<?= BASE_URL ?>modules/<?= strtolower($_SESSION['role']) ?>/index.php" class="nav-link text-white rounded p-3 sidebar-link">
                🏠 Dashboard
            </a>
        </li>

        <?php if($_SESSION['role'] == 'Admin'): ?>
            <li class="mt-4 text-muted small fw-bold px-3 mb-2">MANAGEMENT</li>
            <li><a href="<?= BASE_URL ?>modules/student/list.php" class="nav-link text-white p-2 ps-4">👥 Students</a></li>
            <li><a href="<?= BASE_URL ?>modules/academic/courses.php" class="nav-link text-white p-2 ps-4">📚 Courses</a></li>
            <li><a href="<?= BASE_URL ?>modules/finance/invoices.php" class="nav-link text-white p-2 ps-4">💰 Finance</a></li>
            
            <li class="mt-4 text-muted small fw-bold px-3 mb-2">REPORTS</li>
            <li><a href="<?= BASE_URL ?>modules/admin/reports_students.php" class="nav-link text-white p-2 ps-4">📄 Enrollment</a></li>
            <li><a href="<?= BASE_URL ?>modules/admin/reports_finance.php" class="nav-link text-white p-2 ps-4">💵 Collections</a></li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'Student'): ?>
            <li class="mt-4 text-muted small fw-bold px-3 mb-2">MY PORTAL</li>
            <li><a href="<?= BASE_URL ?>modules/student/profile.php" class="nav-link text-white p-2 ps-4">👤 My Profile</a></li>
            <li><a href="#" class="nav-link text-white p-2 ps-4">📖 My Grades</a></li>
        <?php endif; ?>

        <li class="mt-5 border-top border-secondary pt-3">
            <a href="<?= BASE_URL ?>modules/auth/logout.php" class="nav-link text-danger fw-bold p-3">
                🚪 Sign Out
            </a>
        </li>
    </ul>
</nav>

<style>
    /* SIMPLE HOVER ANIMATION PARA SA SIDEBAR LINKS */
    .sidebar-link {
        transition: all 0.3s ease;
    }
    .sidebar-link:hover {
        background: rgba(13, 110, 253, 0.2);
        color: #0d6efd !important;
        padding-left: 20px !important;
    }
    #sidebar {
        min-width: 250px;
        max-width: 250px;
        min-height: 100vh;
        transition: all 0.3s;
    }
    .nav-link {
        font-size: 0.95rem;
        display: block;
    }
</style>

<div id="content" class="w-100 bg-light">