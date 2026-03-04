<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Debug mode

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';
$room_id = $_POST['room_id'] ?? '';

if (empty($email) || empty($room_id)) {
    echo json_encode(["status" => "error", "message" => "Missing email or room_id"]);
    exit();
}

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

// 2. Insert into History
$stmt = $conn->prepare("INSERT INTO history (user_id, room_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $room_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>