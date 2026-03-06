<?php
error_reporting(0); // Keep 0 to prevent JSON crashes

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

// --- Helper for Dynamic IP ---
function makeFullUrl($dbPath)
{
    if (empty($dbPath))
        return null;
    if (strpos($dbPath, 'http') === 0)
        return $dbPath; // Fallback for old data

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/" . ltrim($dbPath, '/');
}

// FIX: Trim spaces just in case Flutter sends "email@gmail.com "
$email = trim($_GET['email'] ?? '');

// 1. Check User
$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$userResult = $userQ->get_result();

if ($userResult->num_rows == 0) {
    echo json_encode([]);
    exit();
}

// 2. Fetch History 
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
    // Process Images cleanly
    $row['image_url'] = makeFullUrl($row['image_url']);
    $row['floor_layout_url'] = makeFullUrl($row['floor_layout_url']);

    // Ensure room_number is explicitly cast to a string for Flutter
    $row['room_number'] = (string) $row['room_number'];

    array_push($history, $row);
}

echo json_encode($history);
?>