<?php
// modules/admin/index.php
session_start();
require_once '../../config/constants.php';
require_once '../../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin'){ 
    header("Location: " . BASE_URL . "modules/auth/login.php");
    exit(); 
}

try {
    // --- QUICK STATS ---
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn() ?: 0;
    $total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn() ?: 0;
    
    // --- DATA PARA SA CHARTS ---
    $stmt_status = $pdo->query("SELECT status, COUNT(*) as count FROM students GROUP BY status");
    $status_counts = $stmt_status->fetchAll(PDO::FETCH_ASSOC);
    $status_labels = []; $status_data = [];
    foreach($status_counts as $row) {
        $status_labels[] = $row['status'] ? $row['status'] : 'Unknown';
        $status_data[] = $row['count'];
    }

    $stmt_finance = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total, COALESCE(SUM(paid_amount), 0) as paid FROM invoices");
    $finance = $stmt_finance->fetch(PDO::FETCH_ASSOC);
    $total_paid = $finance['paid'];
    $total_unpaid = $finance['total'] - $finance['paid'];

    $stmt_courses = $pdo->query("
        SELECT c.course_code, COUNT(s.id) as student_count 
        FROM courses c 
        LEFT JOIN students s ON c.id = s.course_id 
        GROUP BY c.id
    ");
    $course_stats = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
    $course_labels = []; $course_data = [];
    foreach($course_stats as $row) {
        $course_labels[] = $row['course_code'];
        $course_data[] = $row['student_count'];
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 1. TATAWAGIN ANG MASTER HEADER
include '../../includes/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .page-header { margin-bottom: 20px; }
    .profile-banner { background: var(--card-bg); border-radius: 15px; padding: 30px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);}
    .page-title { font-size: 2rem; font-weight: 900; margin-bottom: 5px; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: var(--card-bg) !important; border-radius: 15px; padding: 25px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; display: flex; justify-content: space-between; align-items: center; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md) !important; border-color: var(--primary-color) !important;}
    .stat-label { font-size: 12px; font-weight: 700; color: gray; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 0.5px;}
    .stat-number { font-size: 2.5rem; font-weight: 800; }

    /* CSS Grid Fix para hindi itulak ng Chart yung kabilang box */
    .charts-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-bottom: 40px; }
    .chart-card { min-width: 0; background: var(--card-bg) !important; border-radius: 15px; padding: 25px; box-shadow: var(--shadow-sm) !important; border: 1px solid var(--border-color) !important; }
    .chart-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;}

    @media (max-width: 900px) {
        .charts-grid, .stats-grid { grid-template-columns: 1fr; }
        .profile-banner { flex-direction: column; text-align: center; gap: 15px; padding: 25px 15px; }
        .page-title { font-size: 1.8rem; }
    }
</style>

<div class="container">
    <div class="page-header">
        <div class="profile-banner">
            <div>
                <h1 class="page-title">Executive Dashboard</h1>
                <p style="color: gray; font-size: 1.1rem;">Real-time overview of your university's data.</p>
            </div>
            <div style="font-size: 60px;">👨‍💻</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Registered Students</div>
                <div class="stat-number" style="color: #3b82f6;"><?= number_format($total_students) ?></div>
            </div>
            <div style="font-size: 40px; opacity: 0.15;">👥</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Classes</div>
                <div class="stat-number" style="color: #8b5cf6;"><?= number_format($total_classes) ?></div>
            </div>
            <div style="font-size: 40px; opacity: 0.15;">📚</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Collection</div>
                <div class="stat-number" style="color: var(--success-color);">₱ <?= number_format($total_paid, 2) ?></div>
            </div>
            <div style="font-size: 40px; opacity: 0.15;">💰</div>
        </div>
    </div>

    <section class="charts-grid">
        <div class="chart-card">
            <h3 class="chart-title">📊 Student Demographics</h3>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="studentStatusChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <h3 class="chart-title">💸 Financial Overview</h3>
            <div style="position: relative; height: 280px; width: 100%;">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <div class="chart-card" style="grid-column: 1 / -1;">
            <h3 class="chart-title">📈 Population per Course</h3>
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="courseChart"></canvas>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const statusLabels = <?php echo json_encode($status_labels); ?>;
            const statusData = <?php echo json_encode($status_data); ?>;
            const courseLabels = <?php echo json_encode($course_labels); ?>;
            const courseData = <?php echo json_encode($course_data); ?>;
            const totalPaid = <?php echo $total_paid; ?>;
            const totalUnpaid = <?php echo $total_unpaid; ?>;

            Chart.defaults.color = '#888';
            Chart.defaults.font.family = "'Segoe UI', Roboto, sans-serif";

            // 1. Student Status Pie Chart
            const ctxStatus = document.getElementById('studentStatusChart');
            if(ctxStatus) {
                new Chart(ctxStatus.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#64748b'],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 2. Financial Doughnut Chart
            const ctxFinance = document.getElementById('financeChart');
            if(ctxFinance) {
                new Chart(ctxFinance.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Collected (Paid)', 'Outstanding (Unpaid)'],
                        datasets: [{
                            data: [totalPaid, totalUnpaid],
                            backgroundColor: ['#3b82f6', '#ef4444'],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // 3. Population per Course Bar Chart
            const ctxCourse = document.getElementById('courseChart');
            if(ctxCourse) {
                new Chart(ctxCourse.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: courseLabels,
                        datasets: [{
                            label: 'Enrolled Students',
                            data: courseData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        } catch(e) {
            console.log("Chart Error: ", e);
        }
    });
</script>

<?php 
// 5. TATAWAGIN ANG MASTER FOOTER 
include '../../includes/footer.php'; 
?>