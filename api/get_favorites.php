<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "No email provided"]);
    exit();
}

$sql = "SELECT r.*, b.name_en as building_name, b.latitude, b.longitude 
        FROM favorites f
        JOIN users u ON f.user_id = u.id
        JOIN rooms r ON f.room_id = r.id
        JOIN buildings b ON r.building_id = b.id
        WHERE u.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

echo json_encode($favorites);
?>