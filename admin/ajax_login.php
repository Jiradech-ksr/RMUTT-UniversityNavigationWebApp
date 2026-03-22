<?php
session_start();
include '../api/db_connect.php';

if (isset($_POST['credential'])) {
    // แกะรหัส JWT Token ที่ได้จาก Google
    $jwt = $_POST['credential'];
    $tokenParts = explode(".", $jwt);
    $tokenPayload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $jwtPayload = json_decode($tokenPayload);

    $email = $jwtPayload->email;

    // ดึงรูปภาพจาก Google JWT Payload (ถ้ามี)
    $google_photo = isset($jwtPayload->picture) ? $jwtPayload->picture : '';

    // ตรวจสอบฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, display_name, email, role, status FROM users WHERE email = ?");
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

            // อัปเดตรูปภาพโปรไฟล์และ Google ID ในฐานข้อมูลให้เป็นข้อมูลล่าสุดจาก Google
            $google_id = $jwtPayload->sub; // รหัส Google ID ของผู้ใช้
            if (!empty($google_photo)) {
                $update_stmt = $conn->prepare("UPDATE users SET photo_url = ?, google_id = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $google_photo, $google_id, $user['id']);
                $update_stmt->execute();
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $update_stmt->bind_param("si", $google_id, $user['id']);
                $update_stmt->execute();
            }

            // ผ่านเงื่อนไข -> สร้าง Session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['display_name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];
            // ใช้รูปจาก Google ถ้ามี ถ้าไม่มีให้ใช้จากฐานข้อมูลเดิม
            $_SESSION['admin_photo'] = !empty($google_photo) ? $google_photo : '';

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