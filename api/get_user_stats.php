<?php
// 1. Enable Debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_GET['email'] ?? '';

// Default empty response
$response = [
    "total_searches" => 0,
    "unique_rooms" => 0,
    "unique_buildings" => 0,
    "top_rooms" => [],
    "top_buildings" => []
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
$response['total_searches'] = $stmt->get_result()->fetch_assoc()['c'];

// 3.1 Get Unique Rooms Visited
$uq_rooms_stmt = $conn->prepare("SELECT COUNT(DISTINCT location_id) as c FROM history WHERE user_id = ? AND location_type = 'Room'");
$uq_rooms_stmt->bind_param("i", $user_id);
$uq_rooms_stmt->execute();
$response['unique_rooms'] = $uq_rooms_stmt->get_result()->fetch_assoc()['c'];

// 3.2 Get Unique Buildings Visited
$uq_bld_stmt = $conn->prepare("SELECT COUNT(DISTINCT location_id) as c FROM history WHERE user_id = ? AND location_type = 'Building'");
$uq_bld_stmt->bind_param("i", $user_id);
$uq_bld_stmt->execute();
$response['unique_buildings'] = $uq_bld_stmt->get_result()->fetch_assoc()['c'];


// 4. Get Top 5 Rooms
$sql = "SELECT r.name_en as name, r.room_number, COUNT(h.id) as visit_count FROM history h JOIN rooms r ON h.location_id = r.id AND h.location_type = 'Room'
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


// 5. Get Top 5 Buildings
$sql_bld = "SELECT b.name_en as name, b.name_th, COUNT(h.id) as visit_count FROM history h JOIN buildings b ON h.location_id = b.id AND h.location_type = 'Building'
        WHERE h.user_id = ? 
        GROUP BY b.id 
        ORDER BY visit_count DESC 
        LIMIT 5";
$stmt_bld = $conn->prepare($sql_bld);
$stmt_bld->bind_param("i", $user_id);
$stmt_bld->execute();
$res_bld = $stmt_bld->get_result();
while ($row = $res_bld->fetch_assoc()) {
    $response['top_buildings'][] = $row;
}

echo json_encode($response);
?>