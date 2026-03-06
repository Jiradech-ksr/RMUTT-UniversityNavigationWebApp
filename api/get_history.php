<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'db_connect.php';

function makeFullUrl($dbPath)
{
    if (empty($dbPath))
        return null;
    if (strpos($dbPath, 'http') === 0)
        return $dbPath;
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/" . ltrim($dbPath, '/');
}

$email = trim($_GET['email'] ?? '');
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
if ($userQ->get_result()->num_rows == 0) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT 
            h.id as history_id, h.visited_at, h.location_type as type, h.location_id,
            r.id as r_id, r.name as r_name, r.room_number, r.floor, r.image_url as r_image, r.floor_layout_url,
            b1.name as r_building_name, b1.latitude as r_lat, b1.longitude as r_lng,
            b2.id as b_id, b2.name as b_name, b2.latitude as b_lat, b2.longitude as b_lng
        FROM history h
        JOIN users u ON h.user_id = u.id
        LEFT JOIN rooms r ON h.location_type = 'Room' AND h.location_id = r.id
        LEFT JOIN buildings b1 ON r.building_id = b1.id
        LEFT JOIN buildings b2 ON h.location_type = 'Building' AND h.location_id = b2.id
        WHERE u.email = ? ORDER BY h.visited_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$history = array();

while ($row = $result->fetch_assoc()) {
    $item = [];
    $isBuilding = ($row['type'] == 'Building');

    $item['id'] = $isBuilding ? $row['b_id'] : $row['r_id'];
    $item['name'] = $isBuilding ? $row['b_name'] : $row['r_name'];
    $item['type'] = $row['type'];
    $item['latitude'] = $isBuilding ? $row['b_lat'] : $row['r_lat'];
    $item['longitude'] = $isBuilding ? $row['b_lng'] : $row['r_lng'];
    $item['department_name'] = $isBuilding ? $row['b_name'] : $row['r_building_name'];

    $item['image_url'] = !$isBuilding ? makeFullUrl($row['r_image']) : null;
    $item['floor_layout_url'] = !$isBuilding ? makeFullUrl($row['floor_layout_url']) : null;
    $item['room_number'] = !$isBuilding ? (string) $row['room_number'] : null;
    $item['floor'] = !$isBuilding ? $row['floor'] : null;

    // Add every single visit to the list!
    if ($item['id'] != null) {
        array_push($history, $item);
    }
}

echo json_encode($history);
?>