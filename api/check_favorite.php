<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';
$room_id = $_POST['room_id'] ?? '';

$sql = "SELECT f.id FROM favorites f 
        JOIN users u ON f.user_id = u.id 
        WHERE u.email = ? AND f.room_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $room_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["is_favorite" => true]);
} else {
    echo json_encode(["is_favorite" => false]);
}
?>