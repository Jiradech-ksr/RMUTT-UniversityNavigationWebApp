<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';
$room_id = $_POST['room_id'] ?? '';
$issue_type = $_POST['issue_type'] ?? 'General';
$description = $_POST['description'] ?? '';

// Get User ID
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$res = $userQ->get_result();
if ($res->num_rows == 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}
$user_id = $res->fetch_assoc()['id'];

// Handle Image Upload
$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $target_dir = "../uploads/reports/";
    if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);

    $filename = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {

        // --- DYNAMIC IP LOGIC START ---
        // 1. Check if using HTTP or HTTPS
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        // 2. Get the host (e.g., "192.168.100.35" or "localhost")
        $host = $_SERVER['HTTP_HOST'];

        // 3. Construct the base URL automatically
        $image_url = $protocol . "://" . $host . "/uploads/reports/" . $filename;
        // --- DYNAMIC IP LOGIC END ---
    }
}

// Insert Report
$stmt = $conn->prepare("INSERT INTO reports (user_id, room_id, issue_type, description, image_url) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $user_id, $room_id, $issue_type, $description, $image_url);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>