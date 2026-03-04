<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // Debug mode

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

// --- Helper for Dynamic IP ---
function makeUrlDynamic($dbUrl)
{
    if (empty($dbUrl))
        return null;
    if (!isset($_SERVER['HTTP_HOST']))
        return $dbUrl;
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $currentHost = $_SERVER['HTTP_HOST'];
    return preg_replace('#^http(s)?://[^/]+#', "$protocol://$currentHost", $dbUrl);
}

$email = $_GET['email'] ?? '';

// 1. Check User
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
if ($userQ->get_result()->num_rows == 0) {
    echo json_encode([]);
    exit();
}

// 2. Fetch History (Join with Rooms and Buildings)
$sql = "SELECT h.id as history_id, h.visited_at, 
               r.id, r.name, r.room_number, r.floor, r.image_url, 
               r.floor_layout_url, 
               b.name as department_name, 
               b.latitude, b.longitude
        FROM history h
        JOIN users u ON h.user_id = u.id
        JOIN rooms r ON h.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        WHERE u.email = ?
        ORDER BY h.visited_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$history = array();
while ($row = $result->fetch_assoc()) {
    // Process Images
    $row['image_url'] = makeUrlDynamic($row['image_url']);
    $row['floor_layout_url'] = makeUrlDynamic($row['floor_layout_url']);

    // Ensure Department Name is set
    if (empty($row['department_name'])) {
        $row['department_name'] = "Unknown Building";
    }

    $history[] = $row;
}

echo json_encode($history);
?>