<?php
// modules/teacher/index.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

// I-check kung Teacher nga ang naka-login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal | Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0fdf4;
            color: #1e293b;
        }

        /* Light green background */

        /* Navbar */
        .navbar {
            background-color: #047857;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #ef4444;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-left: 8px solid #10b981;
        }

        .teacher-name {
            font-size: 2rem;
            font-weight: 800;
            color: #047857;
        }

        .teacher-id {
            color: #64748b;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 5px;
        }

        .btn-primary {
            background: #10b981;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 10px;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="brand">
            <div
                style="background: white; color: #047857; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                🍎</div>
            FACULTY PORTAL
        </div>
        <a href="../auth/logout.php" class="logout-btn">🚪 Sign Out</a>
    </nav>

    <div class="container">
        <div class="welcome-banner">
            <div>
                <div class="teacher-name">Welcome, Prof. <?= htmlspecialchars($_SESSION['teacher_name']) ?>!</div>
                <div class="teacher-id">Employee No: <?= htmlspecialchars($_SESSION['employee_number']) ?></div>
                <br>

                <a href="grades.php" class="btn-primary">📝 Manage My Classes & Grades</a>
            </div>
            <div style="font-size: 80px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">👨‍🏫</div>
        </div>
    </div>

</body>

</html>