<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';
$room_id = $_POST['room_id'] ?? '';

// Get User ID
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$userRes = $userQ->get_result()->fetch_assoc();

if (!$userRes) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}

$user_id = $userRes['id'];

// Check if already favorite
$check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND room_id = ?");
$check->bind_param("ii", $user_id, $room_id);
$check->execute();
$checkRes = $check->get_result();

if ($checkRes->num_rows > 0) {
    // Remove
    $del = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND room_id = ?");
    $del->bind_param("ii", $user_id, $room_id);
    $del->execute();
    echo json_encode(["status" => "removed"]);
} else {
    // Add
    $add = $conn->prepare("INSERT INTO favorites (user_id, room_id) VALUES (?, ?)");
    $add->bind_param("ii", $user_id, $room_id);
    $add->execute();
    echo json_encode(["status" => "added"]);
}
?>