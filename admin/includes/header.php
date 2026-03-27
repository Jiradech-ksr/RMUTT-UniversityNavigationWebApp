<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
include '../api/db_connect.php'; // เรียกใช้การเชื่อมต่อ DB ตัวเดียวกับแอป

// กำหนดหน้าปัจจุบันเพื่อทำ Active Menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Nav - Admin Console</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div class="sidebar-header text-center">
                <h4><i class="fas fa-map-marked-alt text-warning"></i> RMUTT Navigation System</h4>
                <small>Admin Console</small>
            </div>
            <ul class="list-unstyled components">
                <li class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fas fa-home"></i>ภาพรวมระบบ</a>
                </li>
                <li class="<?= ($current_page == 'manage_rooms.php') ? 'active' : ''; ?>">
                    <a href="manage_rooms.php"><i class="fas fa-map-marked-alt"></i> จัดการสถานที่</a>
                </li>
                <li class="<?= ($current_page == 'manage_users.php') ? 'active' : ''; ?>">
                    <a href="manage_users.php"><i class="fas fa-user-graduate"></i> จัดการบัญชีผู้ใช้งาน</a>
                </li>
                <li class="<?= ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> ข้อเสนอแนะ/รายงาน</a>
                </li>
                <li class="<?= ($current_page == 'stats_report.php') ? 'active' : ''; ?>">
                    <a href="stats_report.php"><i class="fas fa-chart-bar"></i> สถิติการใช้งาน</a>
                </li>
            </ul>
        </nav>
        <div id="content" class="w-100 bg-light">
            <nav class="navbar navbar-expand-lg navbar-light top-navbar px-4 py-3 mb-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1 fw-bold text-indigo">RMUTT Navigator System</span>
                    <div class="d-flex align-items-center">
                        <?php
                        $default_avatar = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['admin_name']) . "&background=1A237E&color=fff&rounded=true";
                        $display_photo = !empty($_SESSION['admin_photo']) ? $_SESSION['admin_photo'] : $default_avatar;
                        ?>
                        <img src="<?= $display_photo ?>" alt="Profile" class="rounded-circle me-2 border shadow-sm"
                            width="35" height="35" onerror="this.onerror=null; this.src='<?= $default_avatar ?>';">
                        <div class="d-flex flex-column lh-1">
                            <span class="fw-bold"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                            <small class="text-muted text-uppercase"
                                style="font-size: 0.7rem;"><?= htmlspecialchars($_SESSION['admin_role']) ?></small>
                        </div>
                        <a href="logout.php" class="btn btn-sm btn-outline-danger ms-4" title="ออกจากระบบ"><i
                                class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </nav>
            <div class="container-fluid px-4">