<?php
// reset_db.php - The Factory Reset Script
require_once 'config/database.php';

echo "<div style='font-family: Arial; padding: 50px; max-width: 600px; margin: 50px auto; text-align: center; border: 2px solid #ef4444; border-radius: 15px; background: #fef2f2;'>";
echo "<h1 style='color: #ef4444; font-size: 3rem; margin-bottom: 0;'>⚠️</h1>";
echo "<h1 style='color: #ef4444;'>SYSTEM FACTORY RESET</h1>";

try {
    // 1. I-disable muna ang restrictions ng database para makapag-bura tayo nang tuluyan
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. I-TRUNCATE (Burahin ang laman at i-reset ang ID to 1) ang lahat ng tables
    $pdo->exec("TRUNCATE TABLE invoices");
    $pdo->exec("TRUNCATE TABLE enrollments");
    $pdo->exec("TRUNCATE TABLE classes");
    $pdo->exec("TRUNCATE TABLE students");
    $pdo->exec("TRUNCATE TABLE teachers");
    $pdo->exec("TRUNCATE TABLE courses");

    // 3. I-enable ulit ang restrictions para secure ulit ang database
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<h2 style='color: #10b981;'>✅ Database Successfully Cleared!</h2>";
    echo "<p style='color: #555;'>Lahat ng dummy accounts, classes, grades, at pera sa cashier ay nabura na. Back to zero na ang system mo at handa na para sa mga totoong records!</p>";
    
    echo "<a href='modules/admin/index.php' style='display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; transition: 0.3s;'>⬅️ Return to Empty Dashboard</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo "</div>";
?>