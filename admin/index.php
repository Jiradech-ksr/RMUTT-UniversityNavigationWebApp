<?php
include 'includes/header.php';

// --- 0. จัดการตัวกรองวันที่ (Date Filter) สำหรับรายงาน ---
$days = isset($_GET['days']) ? $_GET['days'] : '7';

if ($days === 'all') {
    $where_pending = "WHERE status = 'pending'";
    $where_resolved = "WHERE status = 'resolved'";
    $date_label = "ทั้งหมด (All Time)";
} elseif ($days === '365') {
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
    $date_label = "ย้อนหลัง 1 ปี (1 Year)";
} elseif ($days === '30') {
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $date_label = "ย้อนหลัง 30 วัน (30 Days)";
} else {
    $days = '7';
    $where_pending = "WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $where_resolved = "WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $date_label = "ย้อนหลัง 7 วัน (7 Days)";
}

// --- 1. ดึงข้อมูลสถิติพื้นฐาน ---
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_buildings = $conn->query("SELECT COUNT(*) as count FROM buildings")->fetch_assoc()['count'];
$total_rooms = $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'];
$pending_reports = $conn->query("SELECT COUNT(*) as count FROM reports $where_pending")->fetch_assoc()['count'];
$resolved_reports = $conn->query("SELECT COUNT(*) as count FROM reports $where_resolved")->fetch_assoc()['count'];

$chart_data_doughnut = ($pending_reports == 0 && $resolved_reports == 0) ? [1, 0] : [$pending_reports, $resolved_reports];

// --- 2. ดึงรายงานปัญحال่าสุด 5 รายการ ---
$recent_reports_sql = "SELECT r.*, u.display_name, rm.name_en as room_name, rm.room_number 
                       FROM reports r 
                       JOIN users u ON r.user_id = u.id 
                       LEFT JOIN rooms rm ON r.room_id = rm.id 
                       ORDER BY r.created_at DESC LIMIT 5";
$recent_reports = $conn->query($recent_reports_sql);
?>

<style>
    .bg-indigo {
        background-color: #1A237E !important;
        color: white;
    }

    .text-indigo {
        color: #1A237E !important;
    }

    .card-stat {
        transition: transform 0.2s;
    }

    .card-stat:hover {
        transform: translateY(-5px);
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h3 class="text-dark mb-2"><i class="fas fa-home text-primary me-2"></i> ภาพรวมระบบ (Dashboard)</h3>
        <div class="btn-group shadow-sm" role="group">
            <a href="?days=7" class="btn <?= ($days == '7') ? 'btn-primary' : 'btn-outline-primary' ?>">7 วัน</a>
            <a href="?days=30" class="btn <?= ($days == '30') ? 'btn-primary' : 'btn-outline-primary' ?>">30 วัน</a>
            <a href="?days=365" class="btn <?= ($days == '365') ? 'btn-primary' : 'btn-outline-primary' ?>">1 ปี</a>
            <a href="?days=all" class="btn <?= ($days == 'all') ? 'btn-primary' : 'btn-outline-primary' ?>">ทั้งหมด</a>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="row mb-1">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-stat border-start border-primary border-4 h-100 py-1 shadow-sm">
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
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-stat border-start border-success border-4 h-100 py-1 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">อาคาร / ห้องเรียน</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= $total_buildings; ?> อาคาร /
                                <?= $total_rooms; ?> ห้อง
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-building fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-stat border-start border-warning border-4 h-100 py-1 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">รายงานปัญหารอแก้ไข</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($pending_reports); ?> รายการ
                            </div>
                        </div>
                        <div class="col-auto"><i
                                class="fas fa-exclamation-triangle fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-stat border-start border-info border-4 h-100 py-1 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">แก้ไขข้อเสนอแนะสำเร็จ</div>
                            <div class="h5 mb-0 fw-semi bold text-dark"><?= number_format($resolved_reports); ?> รายการ
                            </div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-secondary opacity-25"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Reports List -->
        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>รายงานและข้อเสนอแนะล่าสุด
                        (Recent Reports)</h6>
                    <a href="reports.php" class="btn btn-sm btn-outline-primary">ดูทั้งหมด</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 290px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">วันที่</th>
                                    <th>สถานที่</th>
                                    <th>พิกัด</th>
                                    <th>ผู้แจ้ง</th>
                                    <th class="text-end px-4">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_reports && $recent_reports->num_rows > 0):
                                    while ($rp = $recent_reports->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-4"><small
                                                    class="text-muted"><?= date('d/m/Y H:i', strtotime($rp['created_at'])); ?></small>
                                            </td>
                                            <td class="fw-semi bold"><?= htmlspecialchars($rp['issue_type']); ?></td>
                                            <td>
                                                    <?php if ($rp['room_name']): ?>
                                                    <small><?= htmlspecialchars($rp['room_name']); ?>
                                                        (<?= htmlspecialchars($rp['room_number']); ?>)</small>
                                                    <?php else: ?>
                                                    <small class="text-muted">ไม่ระบุ</small>
                                                    <?php endif; ?>
                                            </td>
                                            <td><small><?= htmlspecialchars($rp['display_name']); ?></small></td>
                                            <td class="text-end px-4">
                                                    <?php if ($rp['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">รอดำเนินการ</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-success">แก้ไขแล้ว</span>
                                                    <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">ไม่มีรายการแจ้งปัญหาใหม่</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Status Pie Chart -->
        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>สถานะรายงาน
                        (<?= $date_label ?>)</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div style="height: 200px; width: 200px;">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-2 text-center small fw-bold">
                        <span class="me-3"><i class="fas fa-circle text-warning"></i> รอดำเนินการ
                            (<?= number_format($pending_reports); ?>)</span>
                        <span class="me-3"><i class="fas fa-circle text-indigo"></i> แก้ไขแล้ว
                            (<?= number_format($resolved_reports); ?>)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links / Shortcuts -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-location-arrow me-2"></i>เมนูลัด (Quick
                        Navigation)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="manage_rooms.php" class="text-decoration-none">
                                <div
                                    class="d-flex align-items-center p-3 border rounded shadow-sm bg-light text-dark user-select-none card-stat h-100">
                                    <div class="bg-primary text-white rounded-circle p-3 me-3"><i
                                            class="fas fa-map-marked-alt fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">จัดการสถานที่</h6>
                                        <small class="text-muted">อัปเดตข้อมูลสถานที่</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <a href="stats_report.php" class="text-decoration-none">
                                <div
                                    class="d-flex align-items-center p-3 border rounded shadow-sm bg-light text-dark user-select-none card-stat h-100">
                                    <div class="bg-success text-white rounded-circle p-3 me-3"><i
                                            class="fas fa-chart-bar fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">ดูสถิติการค้นหาสถานที่</h6>
                                        <small class="text-muted">วิเคราะห์ข้อมูลการใช้งาน</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="reports.php" class="text-decoration-none">
                                <div
                                    class="d-flex align-items-center p-3 border rounded shadow-sm bg-light text-dark user-select-none card-stat h-100">
                                    <div class="bg-warning text-dark rounded-circle p-3 me-3"><i
                                            class="fas fa-envelope-open-text fa-lg"></i></div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">จัดการข้อเสนอแนะ</h6>
                                        <small class="text-muted">แก้ไขปัญหาของผู้ใช้งาน</small>
                                    </div>
                                </div>
                            </a>
                        </div>
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

    // Doughnut Chart for Reports
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