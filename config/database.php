<?php
// config/database.php

$host = 'localhost';
$dbname = 'university_db'; // Ang pangalan ng database na ginawa natin kanina
$username = 'root';        // Default username sa XAMPP
$password = '';            // Default password sa XAMPP (blanko)

try {
    // Gumawa ng bagong PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // I-set ang PDO error mode to exception para madali nating makita kung may mali sa SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Kung sakaling hindi makakonekta, ito ang lalabas
    die("ERROR: Hindi makakonekta sa database. " . $e->getMessage());
}
?>