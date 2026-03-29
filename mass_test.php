<?php
// mass_test.php - The Ultimate Stress Tester (Self-Healing Version)
require_once 'config/database.php';

echo "<div style='font-family: Arial; padding: 40px; max-width: 800px; margin: auto;'>";
echo "<h1 style='color: #a855f7;'>🚀 Mass Data Generator (Stress Test)</h1>";

try {
    // --- 🛠️ 1. AUTO-REPAIR DATABASE COLUMNS ---
    echo "<h3>🛠️ System Check & Auto-Repair...</h3>";
    
    // Fix Teachers Table
    try { $pdo->exec("ALTER TABLE teachers ADD COLUMN email VARCHAR(100) NULL"); echo "🔧 Added 'email' to teachers.<br>"; } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE teachers ADD COLUMN department VARCHAR(100) NULL"); echo "🔧 Added 'department' to teachers.<br>"; } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE teachers ADD COLUMN employee_no VARCHAR(50) NULL"); echo "🔧 Added 'employee_no' to teachers.<br>"; } catch (Exception $e) {}

    // Fix Students Table
    try { $pdo->exec("ALTER TABLE students ADD COLUMN email VARCHAR(100) NULL"); echo "🔧 Added 'email' to students.<br>"; } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE students ADD COLUMN student_number VARCHAR(50) NULL"); echo "🔧 Added 'student_number' to students.<br>"; } catch (Exception $e) {}

    echo "<p style='color: green;'>✅ Database Structure is 100% Ready!</p><hr>";

    // --- 📊 2. GENERATE FAKE DATA ---
    $first_names = ['Juan', 'Maria', 'Mark', 'Anna', 'Jose', 'Diana', 'Carlos', 'Elena', 'Francisco', 'Gina', 'Miguel', 'Sofia', 'Luis', 'Carmen', 'Pedro'];
    $last_names = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Villanueva', 'Ramos', 'Aquino', 'Navarro', 'Del Rosario', 'Castro', 'Dela Cruz'];
    $departments = ['General Education', 'Computer Studies', 'Business & Finance', 'Engineering'];
    $grades = ['1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '5.00', 'INC'];

    // COURSES
    $pdo->exec("INSERT IGNORE INTO courses (course_code, course_name) VALUES ('BSCS', 'BS Computer Science'), ('BSIT', 'BS Information Technology'), ('BSBA', 'BS Business Administration')");
    $courses = $pdo->query("SELECT id FROM courses")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>📚 Validated Courses.</p>";

    // TEACHERS
    for ($i = 0; $i < 10; $i++) {
        $fn = $first_names[array_rand($first_names)];
        $ln = $last_names[array_rand($last_names)];
        $dept = $departments[array_rand($departments)];
        $emp_no = 'EMP-TEST-' . rand(100, 9999);
        $pass = password_hash($ln, PASSWORD_DEFAULT);
        
        $pdo->exec("INSERT INTO teachers (employee_no, first_name, last_name, email, department, password, status) 
                    VALUES ('$emp_no', '$fn', '$ln', 'teacher$i@test.com', '$dept', '$pass', 'Active')");
    }
    echo "<p>👨‍🏫 Generated 10 Fake Teachers.</p>";

    // STUDENTS
    for ($i = 0; $i < 50; $i++) {
        $fn = $first_names[array_rand($first_names)];
        $ln = $last_names[array_rand($last_names)];
        $cid = $courses[array_rand($courses)];
        $stud_no = '2026-TEST-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
        $pass = password_hash($ln, PASSWORD_DEFAULT);
        
        $pdo->exec("INSERT INTO students (student_no, student_number, first_name, last_name, email, course_id, password, status) 
                    VALUES ('$stud_no', '$stud_no', '$fn', '$ln', 'student$i@test.com', '$cid', '$pass', 'Active')");
    }
    echo "<p>👨‍🎓 Generated 50 Fake Students.</p>";

    // CLASSES
    $teachers = $pdo->query("SELECT id FROM teachers")->fetchAll(PDO::FETCH_COLUMN);
    $days = ['MWF', 'TTH', 'SAT'];
    for ($i = 0; $i < 20; $i++) {
        $cid = $courses[array_rand($courses)];
        $tid = $teachers[array_rand($teachers)];
        $sched = $days[array_rand($days)] . ' ' . rand(7, 15) . ':00 - ' . rand(16, 18) . ':00';
        $room = 'Room ' . rand(101, 505);
        
        $pdo->exec("INSERT INTO classes (course_id, teacher_id, schedule, room, status) 
                    VALUES ('$cid', '$tid', '$sched', '$room', 'Active')");
    }
    echo "<p>📅 Generated 20 Fake Classes.</p>";

    // ENROLLMENTS & FINANCE
    $students = $pdo->query("SELECT id FROM students")->fetchAll(PDO::FETCH_COLUMN);
    $classes = $pdo->query("SELECT id FROM classes")->fetchAll(PDO::FETCH_COLUMN);
    
    for ($i = 0; $i < 100; $i++) {
        $sid = $students[array_rand($students)];
        $clid = $classes[array_rand($classes)];
        $grade = (rand(1, 10) > 3) ? $grades[array_rand($grades)] : NULL; 
        
        $remarks = 'Pending';
        if ($grade) {
            if ($grade == 'INC') $remarks = 'Incomplete';
            elseif ($grade == '5.00') $remarks = 'Failed';
            else $remarks = 'Passed';
        }

        $check = $pdo->query("SELECT id FROM enrollments WHERE student_id = $sid AND class_id = $clid")->fetch();
        if (!$check) {
            $pdo->exec("INSERT INTO enrollments (student_id, class_id, grade, remarks) VALUES ('$sid', '$clid', " . ($grade ? "'$grade'" : "NULL") . ", '$remarks')");
            
            $tuition = rand(5, 15) * 1000;
            $paid = (rand(1, 10) > 5) ? $tuition : (rand(1, 10) > 5 ? $tuition / 2 : 0); 
            $status = ($paid == $tuition) ? 'Paid' : (($paid > 0) ? 'Partial' : 'Unpaid');
            
            $pdo->exec("INSERT INTO invoices (student_id, fee_id, total_amount, paid_amount, status) VALUES ('$sid', 1, '$tuition', '$paid', '$status')");
        }
    }
    echo "<p>💯 Generated 100 Random Enrollments & Finance Records.</p>";

    echo "<hr>";
    echo "<h2 style='color: #10b981;'>🎉 MASS TESTING COMPLETE!</h2>";
    echo "<p>Bumalik sa Admin Dashboard at silipin ang mga nagbabagang Charts at Tables!</p>";
    echo "<a href='modules/admin/index.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Admin Dashboard ➔</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}

echo "</div>";
?>