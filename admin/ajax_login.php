<?php
session_start();
include '../api/db_connect.php';

if (isset($_POST['credential'])) {
    // แกะรหัส JWT Token ที่ได้จาก Google
    $jwt = $_POST['credential'];
    $tokenParts = explode(".", $jwt);
    $tokenPayload = base64_decode($tokenParts[1]);
    $jwtPayload = json_decode($tokenPayload);

    $email = $jwtPayload->email;

    // ตรวจสอบฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, display_name, email, role, status, photo_url FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 1. เช็คว่าโดนแบนไหม
        if ($user['status'] == 'banned') {
            echo json_encode(["status" => "error", "message" => "บัญชีนี้ถูกระงับการใช้งาน (Account Banned)"]);
            exit();
        }

        // 2. เช็คว่ามีสิทธิ์เข้าหลังบ้านไหม (ต้องไม่ใช่ student)
        if (in_array($user['role'], ['admin', 'staff', 'technician'])) {

            // ผ่านเงื่อนไข -> สร้าง Session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['display_name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_photo'] = $user['photo_url'];

            echo json_encode(["status" => "success"]);
            exit();

        } else {
            echo json_encode(["status" => "error", "message" => "ไม่มีสิทธิ์เข้าถึง (Access Denied). สำหรับเจ้าหน้าที่เท่านั้น"]);
            exit();
        }

    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบบัญชีในระบบ กรุณาติดต่อผู้ดูแลระบบ"]);
        exit();
    }
}
?>