<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Registrar')){
    header("Location: ../auth/login.php"); exit();
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-primary">Student Masterlist</h4>
            <div class="d-flex gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search name or ID..." style="width: 300px;">
                <a href="register.php" class="btn btn-primary">+ Add Student</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Student Number</th>
                            <th>Full Name</th>
                            <th>Program</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Function para kuhanin ang data
    function load_data(query = '') {
        $.ajax({
            url: "process/search_students.php",
            method: "POST",
            data: {query: query},
            success: function(data) {
                $('#studentTableBody').html(data);
            }
        });
    }

    // Load agad pagkabukas ng page
    load_data();

    // Trigger kapag nag-type sa search box
    $('#searchInput').keyup(function(){
        var search = $(this).val();
        load_data(search);
    });
});
</script>

<?php include '../../includes/footer.php'; ?>