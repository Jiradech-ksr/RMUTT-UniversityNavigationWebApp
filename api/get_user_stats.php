<?php
// 1. Enable Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_GET['email'] ?? '';

// Default empty response
$response = [
    "total_searches" => 0,
    "top_rooms" => []
];

if (!$email) {
    echo json_encode($response);
    exit();
}

// 2. Check User
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$res = $userQ->get_result();

if ($res->num_rows == 0) {
    echo json_encode($response);
    exit();
}

$user_id = $res->fetch_assoc()['id'];

// 3. Get Total Searches
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM history WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['c'];
$response['total_searches'] = $total;

// 4. Get Top 5 Rooms
$sql = "SELECT r.name, COUNT(*) as visit_count 
        FROM history h 
        JOIN rooms r ON h.room_id = r.id 
        WHERE h.user_id = ? 
        GROUP BY r.id 
        ORDER BY visit_count DESC 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $response['top_rooms'][] = $row;
}

echo json_encode($response);
?>