<?php
// 1. TURN ERRORS ON
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'upload_helper.php';
include 'db_connect.php';

// --- HELPER: Dynamic URL ---
function makeFullUrl($dbPath)
{
    if (empty($dbPath))
        return null;

    // Fallback
    if (strpos($dbPath, 'http') === 0) {
        return $dbPath;
    }

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . "://" . $host . "/" . ltrim($dbPath, '/');
}

$hierarchy = [];

// 2. Fetch Buildings
$sql_buildings = "SELECT * FROM buildings ORDER BY name ASC";
$result_buildings = $conn->query($sql_buildings);

if (!$result_buildings) {
    // If SQL fails, print the error
    die(json_encode(["error" => "Building Query Failed: " . $conn->error]));
}

if ($result_buildings->num_rows > 0) {
    while ($building = $result_buildings->fetch_assoc()) {

        // Use 'isset' checks to prevent crashes if columns are missing
        $b_img = isset($building['image_url']) ? $building['image_url'] : null;

        $buildingNode = [
            "id" => $building['id'],
            "title" => $building['name'],
            "type" => "building",
            "lat" => $building['latitude'],
            "lng" => $building['longitude'],
            "image_url" => makeFullUrl($b_img),
            "children" => []
        ];

        // 3. Fetch Rooms
        $b_id = $building['id'];
        $sql_rooms = "SELECT * FROM rooms WHERE building_id = $b_id ORDER BY room_number ASC";
        $result_rooms = $conn->query($sql_rooms);

        if ($result_rooms) {
            while ($room = $result_rooms->fetch_assoc()) {
                // Safe checks for room columns
                $r_img = isset($room['image_url']) ? $room['image_url'] : null;
                $r_layout = isset($room['floor_layout_url']) ? $room['floor_layout_url'] : null;

                $roomNode = [
                    "id" => $room['id'],
                    "title" => $room['name'],
                    "room_number" => $room['room_number'],
                    "floor" => $room['floor'],
                    "type" => "room",
                    "lat" => $building['latitude'],
                    "lng" => $building['longitude'],
                    "image_url" => makeFullUrl($r_img),
                    "floor_layout_url" => makeFullUrl($r_layout)
                ];
                array_push($buildingNode['children'], $roomNode);
            }
        }

        array_push($hierarchy, $buildingNode);
    }
}

echo json_encode($hierarchy);
?>