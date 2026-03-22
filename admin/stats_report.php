<?php
include 'includes/header.php';

// --- 0. จัดการตัวกรองวันที่ (Date Filter) ---
$days = isset($_GET['days']) ? $_GET['days'] : 'all';

if ($days === '7') {
    $where_clause = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $date_label = "ย้อนหลัง 7 วัน (7 Days)";
    $sql_time = "SELECT DATE(visited_at) as raw_date, DATE_FORMAT(visited_at, '%W') as label, COUNT(*) as count FROM history $where_clause GROUP BY raw_date, label ORDER BY raw_date ASC";
} elseif ($days === '30') {
    $where_clause = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $date_label = "ย้อนหลัง 30 วัน (30 Days)";
    $sql_time = "SELECT DATE(visited_at) as raw_date, DATE_FORMAT(visited_at, '%d/%m') as label, COUNT(*) as count FROM history $where_clause GROUP BY raw_date, label ORDER BY raw_date ASC";
} elseif ($days === '365') {
    $where_clause = "WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $date_label = "ย้อนหลัง 1 ปี (1 Year)";
    $sql_time = "SELECT DATE_FORMAT(visited_at, '%Y-%m-01') as raw_date, DATE_FORMAT(visited_at, '%b') as label, COUNT(*) as count FROM history $where_clause GROUP BY raw_date, label ORDER BY raw_date ASC";
} else { 
    $days = 'all';
    $where_clause = "";
    $date_label = "ทั้งหมด (All Time)";
    $sql_time = "SELECT YEAR(visited_at) as raw_year, COUNT(*) as count FROM history GROUP BY raw_year ORDER BY raw_year ASC";
}

// --- 1. ดึงข้อมูลสถิติพื้นฐาน ---
$total_history_logs = $conn->query("SELECT COUNT(*) as count FROM history $where_clause")->fetch_assoc()['count'];

$unique_active_users = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM history $where_clause")->fetch_assoc()['count'];

$most_popular_type = "ไม่ระบุ";
$type_query = $conn->query("SELECT location_type, COUNT(*) as c FROM history $where_clause GROUP BY location_type ORDER BY c DESC LIMIT 1");
if ($type_query && $type_query->num_rows > 0) {
    $pop_row = $type_query->fetch_assoc();
    $most_popular_type = ($pop_row['location_type'] == 'Building') ? 'อาคาร (Building)' : 'ห้อง (Room)';
}

// --- 2. เตรียมข้อมูลสำหรับกราฟแท่ง (Search Counts over time) ---
$res_time = $conn->query($sql_time);
$chart_labels_time = [];
$chart_data_time = [];

$data_map = [];
if ($res_time && $res_time->num_rows > 0) {
    while ($row = $res_time->fetch_assoc()) {
        if ($days === 'all') {
            $data_map[$row['raw_year']] = $row['count'];
        } else {
            $data_map[$row['raw_date']] = $row['count'];
        }
    }
}

if ($days === '7') {
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime("-$i days"));
        $chart_labels_time[] = $label;
        $chart_data_time[] = isset($data_map[$date]) ? (int)$data_map[$date] : 0;
    }
} elseif ($days === '30') {
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $label = date('d/m', strtotime("-$i days"));
        $chart_labels_time[] = $label;
        $chart_data_time[] = isset($data_map[$date]) ? (int)$data_map[$date] : 0;
    }
} elseif ($days === '365') {
    $months_map = [];
    for ($i = 11; $i >= 0; $i--) {
        $ts = mktime(0, 0, 0, date('n') - $i, 1, date('Y'));
        $date = date('Y-m-01', $ts);
        $label = date('M', $ts);
        $months_map[$date] = $label;
    }
    foreach ($data_map as $date => $val) {
        if (!isset($months_map[$date])) {
            $months_map[$date] = date('M', strtotime($date));
        }
    }
    ksort($months_map);
    foreach($months_map as $date => $label) {
        $chart_labels_time[] = $label;
        $chart_data_time[] = isset($data_map[$date]) ? (int)$data_map[$date] : 0;
    }
} else {
    if (empty($data_map)) {
        $chart_labels_time = ['ไม่มีข้อมูล'];
        $chart_data_time = [0];
    } else {
        $min_year = min(array_keys($data_map));
        $max_year = date('Y');
        $max_year = max($max_year, max(array_keys($data_map)));
        for ($y = $min_year; $y <= $max_year; $y++) {
            $chart_labels_time[] = (string)$y;
            $chart_data_time[] = isset($data_map[$y]) ? (int)$data_map[$y] : 0;
        }
    }
}

// --- 3. ดึงข้อมูล 10 ห้องที่ถูกค้นหาสูงสุด ---
$top_rooms_sql = "SELECT rm.name_en as name, rm.name_th, rm.room_number, b.name_en as building_name, COUNT(*) as visits 
                  FROM history h 
                  JOIN rooms rm ON h.location_id = rm.id AND h.location_type = 'Room'
                  JOIN buildings b ON rm.building_id = b.id
                  $where_clause
                  GROUP BY rm.id ORDER BY visits DESC LIMIT 10";
$top_rooms_result = $conn->query($top_rooms_sql);

// --- 4. ดึงข้อมูล 10 อาคารที่ถูกค้นหาสูงสุด ---
$top_buildings_sql = "SELECT b.name_en, b.name_th, COUNT(*) as visits
                      FROM history h
                      JOIN buildings b ON h.location_id = b.id AND h.location_type = 'Building'
                      $where_clause
                      GROUP BY b.id ORDER BY visits DESC LIMIT 10";
$top_buildings_result = $conn->query($top_buildings_sql);

?>

<style>
    .bg-indigo { background-color: #1A237E !important; color: white; }
    .text-indigo { color: #1A237E !important; }
    .card-stat { transition: transform 0.2s; }
    .card-stat:hover { transform: translateY(-5px); }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h3 class="text-dark mb-2"><i class="fas fa-chart-bar text-primary me-2"></i> สถิติการใช้งาน (Usage Statistics)</h3>
        <div class="btn-group shadow-sm" role="group">
            <a href="?days=7" class="btn <?= ($days == '7') ? 'btn-primary' : 'btn-outline-primary' ?>">7 วัน</a>
            <a href="?days=30" class="btn <?= ($days == '30') ? 'btn-primary' : 'btn-outline-primary' ?>">30 วัน</a>
            <a href="?days=365" class="btn <?= ($days == '365') ? 'btn-primary' : 'btn-outline-primary' ?>">1 ปี</a>
            <a href="?days=all" class="btn <?= ($days == 'all') ? 'btn-primary' : 'btn-outline-primary' ?>">ทั้งหมด</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card card-stat border-start border-primary border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">จำนวนการค้นหา (รวม)</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($total_history_logs); ?> ครั้ง</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-search fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card card-stat border-start border-success border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">จำนวนผู้ใช้ที่ค้นหา (Unique Users)</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($unique_active_users); ?> คน</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-check fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-4">
            <div class="card card-stat border-start border-warning border-4 h-100 py-2 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">ประเภทที่ถูกค้นหามากที่สุด</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= htmlspecialchars($most_popular_type); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-star fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>แนวโน้มการค้นหาสถานที่ (<?= $date_label ?>)</h6>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="statsBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Searched Tables Row -->
    <div class="row">
        <!-- Top 10 Rooms -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-door-open text-info me-2"></i>Top 10 ห้องที่ถูกค้นหามากที่สุด
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">อันดับ</th>
                                    <th>ชื่อห้อง</th>
                                    <th>อาคาร</th>
                                    <th class="text-end px-3">การค้นหา</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                if ($top_rooms_result && $top_rooms_result->num_rows > 0): 
                                    while ($row = $top_rooms_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-3"><span class="badge rounded-pill bg-info"><?= $rank++; ?></span></td>
                                        <td class="fw-semi bold">
                                            <?= htmlspecialchars($row['name']); ?> 
                                            <div class="small text-muted"><?= htmlspecialchars($row['room_number'] ?? ''); ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['building_name']); ?></td>
                                        <td class="text-end px-3 fw-semi bold text-success"><?= number_format($row['visits']); ?> ครั้ง</td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">ยังไม่มีข้อมูล</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Buildings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-building text-warning me-2"></i>Top 10 อาคารที่ถูกค้นหามากที่สุด
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">อันดับ</th>
                                    <th>ชื่ออาคาร (EN)</th>
                                    <th>ชื่ออาคาร (TH)</th>
                                    <th class="text-end px-3">การค้นหา</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                if ($top_buildings_result && $top_buildings_result->num_rows > 0): 
                                    while ($row = $top_buildings_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-3"><span class="badge rounded-pill bg-warning text-dark"><?= $rank++; ?></span></td>
                                        <td class="fw-semi bold"><?= htmlspecialchars($row['name_en']); ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($row['name_th'] ?? '-'); ?></td>
                                        <td class="text-end px-3 fw-semi bold text-success"><?= number_format($row['visits']); ?> ครั้ง</td>
                                    </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">ยังไม่มีข้อมูล</td></tr>
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
    new Chart(document.getElementById("statsBarChart"), {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels_time); ?>,
            datasets: [{
                label: "จำนวนการค้นหา (ครั้ง)",
                backgroundColor: "rgba(26, 35, 126, 0.1)",
                borderColor: "#1A237E",
                pointBackgroundColor: "#1A237E",
                pointBorderColor: "#fff",
                pointHoverBackgroundColor: "#fff",
                pointHoverBorderColor: "#1A237E",
                fill: true,
                tension: 0.3,
                data: <?= json_encode($chart_data_time); ?>,
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: { legend: { display: true, position: 'top' } }
        }
    });
</script>
