<?php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

// Kunin ang data ng student
$stmt = $pdo->prepare("SELECT s.*, p.program_name FROM students s LEFT JOIN programs p ON s.program_id = p.id WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="mb-3">
                    <?php 
                        $photo = !empty($student['profile_pic']) ? "../../assets/images/profiles/".$student['profile_pic'] : "https://ui-avatars.com/api/?name=".urlencode($student['first_name'])."&size=128";
                    ?>
                    <img src="<?= $photo ?>" class="rounded-circle border shadow-sm" width="150" height="150" style="object-fit: cover;">
                </div>
                <form action="process/upload_photo.php" method="POST" enctype="multipart/form-data" class="mt-2">
                    <input type="file" name="profile_pic" class="form-control form-control-sm mb-2" required>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Photo</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Account Settings</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="process/update_password.php" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name</label>
                                <input type="text" class="form-control" value="<?= $student['first_name'] ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" class="form-control" value="<?= $student['last_name'] ?>" readonly>
                            </div>
                        </div>
                        <hr>
                        <h6 class="fw-bold text-danger">Change Password</h6>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>