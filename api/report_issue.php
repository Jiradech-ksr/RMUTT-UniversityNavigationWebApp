<?php
error_reporting(0); // Turned off display_errors for production to prevent JSON breaks
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';
include 'upload_helper.php'; // Include your new shiny helper function

$email = $_POST['email'] ?? '';
$room_id = $_POST['room_id'] ?? '';
$issue_type = $_POST['issue_type'] ?? 'General';
$description = $_POST['description'] ?? '';

// 1. Get User ID
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$res = $userQ->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}
$user_id = $res->fetch_assoc()['id'];

// 2. Handle Image Upload (The Clean Way)
// We pass "../" as the base path because this script is inside the api/ folder
$image_url = uploadFileSafely($_FILES['image'] ?? null, "reports", "../");

// 3. Insert Report
// Note: $image_url now correctly holds either null or a relative path like "uploads/reports/12345_img.jpg"
$stmt = $conn->prepare("INSERT INTO reports (user_id, room_id, issue_type, description, image_url) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $user_id, $room_id, $issue_type, $description, $image_url);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>