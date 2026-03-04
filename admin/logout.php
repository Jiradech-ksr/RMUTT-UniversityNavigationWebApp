<?php
session_start();

// ล้างค่า Session ทั้งหมดที่เคยจำไว้ตอนล็อกอิน
session_unset();
session_destroy();

// เด้งกลับไปที่หน้า Login
header("Location: login.php");
exit();
?>