<?php
session_start();
if($_SESSION['role'] !== 'Admin'){ die("Access Denied."); }

$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'university_db';

// Gagamit tayo ng mysqldump via exec (kung naka-install ang mysql sa PATH ng XAMPP)
$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Simple alternative: I-send ang filename at sabihing success (sa XAMPP, madalas ay via phpMyAdmin ang backup)
// Pero kung gusto mo ng direct download, ito ang header logic:
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $filename . "\"");

// Sa ngayon, dahil limitado ang exec sa ilang XAMPP, ito ay magsisilbing placeholder.
// Pinakamaganda pa ring paraan ang "Export" sa phpMyAdmin para sa full data integrity.
echo "-- University Management System Backup\n";
echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
echo "-- Mangyaring gamitin ang phpMyAdmin Export para sa kumpletong kopya.";
?>