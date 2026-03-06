<?php
include 'includes/header.php';

// --- 0. จัดการตัวกรองวันที่ (Date Filter) ---
$days = isset($_GET['days']) ? $_GET['days'] : '7';

if ($days === 'all') {
    $where_pending = "WHERE status = 'pending'";
    $where_resolved = "WHERE status = 'resolved'";
    $date_label = "ทั้งหมด (All Time)";
    $sql_time = "SELECT YEAR(visited_at) as raw_year, COUNT(*) as count FROM history GROUP BY raw_year ORDER BY raw_year ASC";
} elseif ($days === '365') {
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $date_label = "ย้อนหลัง 1 ปี (1 Year)";
    $sql_time = "SELECT DATE_FORMAT(visited_at, '%Y-%m-01') as raw_date, DATE_FORMAT(visited_at, '%b') as label, COUNT(*) as count FROM history WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) GROUP BY raw_date, label ORDER BY raw_date ASC";
} elseif ($days === '30') {
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $date_label = "ย้อนหลัง 30 วัน (4 Weeks)";
    $sql_time = "SELECT YEARWEEK(visited_at, 1) as raw_week, COUNT(*) as count FROM history WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY raw_week ORDER BY raw_week ASC";
} else { 
    $days = '7';
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $date_label = "ย้อนหลัง 7 วัน (7 Days)";
    $sql_time = "SELECT DATE(visited_at) as raw_date, DATE_FORMAT(visited_at, '%W') as label, COUNT(*) as count FROM history WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY raw_date, label ORDER BY raw_date ASC";
}

// --- 1. ดึงข้อมูลสถิติพื้นฐาน ---
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_buildings = $conn->query("SELECT COUNT(*) as count FROM buildings")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM reports $where_pending")->fetch_assoc()['count'];
$resolved_reports = $conn->query("SELECT COUNT(*) as count FROM reports $where_resolved")->fetch_assoc()['count'];

// --- 2. เตรียมข้อมูลสำหรับกราฟแท่ง ---
$res_time = $conn->query($sql_time);
$chart_labels_time = [];
$chart_data_time = [];
$wk_counter = 1;

if ($res_time && $res_time->num_rows > 0) {
    while ($row = $res_time->fetch_assoc()) {
        if ($days === '30') {
            $chart_labels_time[] = "Week " . $wk_counter++;
        } elseif ($days === '7') {
            $chart_labels_time[] = substr($row['label'], 0, 3);
        } elseif ($days === '365') {
            $chart_labels_time[] = $row['label'];
        } else {
            $chart_labels_time[] = $row['raw_year'];
        }
        $chart_data_time[] = $row['count'];
    }
} else {
    $chart_labels_time = ['ไม่มีข้อมูล'];
    $chart_data_time = [0];
}

$chart_data_doughnut = ($pending_reports == 0 && $resolved_reports == 0) ? [1, 0] : [$pending_reports, $resolved_reports];

// --- 4. ดึงข้อมูลห้องที่ถูกค้นหาสูงสุด ---
$popular_rooms_sql = "SELECT rm.name, rm.room_number, b.name as building_name, COUNT(*) as visits 
                      FROM history h 
                      JOIN rooms rm ON h.location_id = rm.id AND h.location_type = 'Room'
                      JOIN buildings b ON rm.building_id = b.id
                      GROUP BY rm.id ORDER BY visits DESC LIMIT 10";
$popular_rooms_result = $conn->query($popular_rooms_sql);
?>

<style>
    .bg-indigo { background-color: #1A237E !important; color: white; }
    .text-indigo { color: #1A237E !important; }
    .card-stat { transition: transform 0.2s; }
    .card-stat:hover { transform: translateY(-5px); }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h3 class="text-dark mb-2"><i class="fas fa-chart-line text-primary me-2"></i> ภาพรวมระบบ (Dashboard)</h3>
        <div class="btn-group shadow-sm" role="group">
            <a href="?days=7" class="btn <?= ($days == '7') ? 'btn-primary' : 'btn-outline-primary' ?>">7 วัน</a>
            <a href="?days=30" class="btn <?= ($days == '30') ? 'btn-primary' : 'btn-outline-primary' ?>">30 วัน</a>
            <a href="?days=365" class="btn <?= ($days == '365') ? 'btn-primary' : 'btn-outline-primary' ?>">1 ปี</a>
            <a href="?days=all" class="btn <?= ($days == 'all') ? 'btn-primary' : 'btn-outline-primary' ?>">ทั้งหมด</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat border-start border-primary border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">ผู้ใช้งานทั้งหมด</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($total_users); ?> คน</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat border-start border-success border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">อาคาร / ห้องเรียน</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= $total_buildings; ?> อาคาร / <?= $total_rooms; ?> ห้อง</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-building fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat border-start border-warning border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">รายงานปัญหารอแก้ไข</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($pending_reports); ?> รายการ</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stat border-start border-info border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">แก้ไขเสร็จสิ้นแล้ว</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($resolved_reports); ?> รายการ</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>สถิติการค้นหา (Search Count Trend)</h6>
                </div>
                <div class="card-body">
                    <div style="height: 320px;">
                        <canvas id="myBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>สถานะรายงานปัญหา</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div style="height: 250px; width: 250px;">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small fw-bold">
                        <span class="me-2"><i class="fas fa-circle text-warning"></i> รอดำเนินการ</span>
                        <span class="me-2"><i class="fas fa-circle text-indigo"></i> แก้ไขแล้ว</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-trophy text-warning me-2"></i>ห้องที่ถูกค้นหาสูงสุดตลอดกาล
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">อันดับ</th>
                                    <th>ชื่อห้อง</th>
                                    <th>เลขห้อง</th>
                                    <th>อาคาร</th>
                                    <th class="text-end px-4">จำนวนการค้นหา</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                if ($popular_rooms_result && $popular_rooms_result->num_rows > 0): 
                                    while ($row = $popular_rooms_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4"><span class="badge rounded-pill bg-indigo"><?= $rank++; ?></span></td>
                                        <td class="fw-semi bold"><?= htmlspecialchars($row['name']); ?></td>
                                        <td><?= htmlspecialchars($row['room_number']); ?></td>
                                        <td><?= htmlspecialchars($row['building_name']); ?></td>
                                        <td class="text-end px-4 fw-semi bold text-success"><?= number_format($row['visits']); ?> ครั้ง</td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">ยังไม่มีข้อมูลการเข้าชม</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Prompt', sans-serif";
    
    // Bar Chart
    new Chart(document.getElementById("myBarChart"), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels_time); ?>,
            datasets: [{
                label: "ครั้ง",
                backgroundColor: "#1A237E",
                hoverBackgroundColor: "#2e59d9",
                data: <?= json_encode($chart_data_time); ?>,
                barPercentage: 0.5,
                borderRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // Doughnut Chart
    new Chart(document.getElementById("myPieChart"), {
        type: 'doughnut',
        data: {
            labels: ["รอดำเนินการ", "แก้ไขแล้ว"],
            datasets: [{
                data: <?= json_encode($chart_data_doughnut); ?>,
                backgroundColor: ['#ffc107', '#1A237E'],
                borderWidth: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
</script>