<?php
session_start();
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
                <h4><i class="fas fa-map-marked-alt text-warning"></i> Campus Nav</h4>
                <small>Admin Console</small>
            </div>

            <ul class="list-unstyled components">
                <li class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fas fa-home"></i> แดชบอร์ด (Dashboard)</a>
                </li>
                <li class="<?= ($current_page == 'manage_rooms.php') ? 'active' : ''; ?>">
                    <a href="manage_rooms.php"><i class="fas fa-building"></i> จัดการอาคาร/ห้อง</a>
                </li>
                <li class="<?= ($current_page == 'manage_users.php') ? 'active' : ''; ?>">
                    <a href="manage_users.php"><i class="fas fa-user-graduate"></i> จัดการนักศึกษา</a>
                </li>
                <li class="<?= ($current_page == 'manage_staff.php') ? 'active' : ''; ?>">
                    <a href="manage_staff.php"><i class="fas fa-user-tie"></i> จัดการเจ้าหน้าที่</a>
                </li>
                <li class="<?= ($current_page == 'reports.php') ? 'active' : ''; ?>">
                    <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> ข้อเสนอแนะ/รายงาน</a>
                </li>
            </ul>
        </nav>

        <div id="content" class="w-100 bg-light">
            <nav class="navbar navbar-expand-lg navbar-light top-navbar px-4 py-3 mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1 fw-bold text-indigo">RMUTT Navigation System</span>
                    <div class="d-flex align-items-center">
                        <span class="me-3"><i class="fas fa-user-circle fs-4 text-secondary"></i> Administrator</span>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4">