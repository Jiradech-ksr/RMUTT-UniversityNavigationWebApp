<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db_config.php';
include 'api_config.php';

$search_query = isset($_GET['q']) ? $_GET['q'] : '';

$results = [];

try {
    $search_term = '%' . $search_query . '%';

    $baseDir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
    
    // Function to ensure URL is full
    function makeFullUrl($path, $baseDir) {
        if (empty($path)) return null;
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $baseDir . '/' . ltrim($path, '/');
    }

    $sql = "
    SELECT 
        id, 
        name_en, 
        name_th, 
        'Building' as type, 
        latitude, 
        longitude, 
        NULL as room_number, 
        NULL as floor,
        image_url, 
        NULL as floor_layout_url,
        name_en as building_name_en,
        name_th as building_name_th,
        image_url as building_image_url
    FROM buildings 
    WHERE name_en LIKE ? OR name_th LIKE ?
    
    UNION
    
    SELECT 
        r.id, 
        r.name_en, 
        r.name_th, 
        'Room' as type, 
        b.latitude, 
        b.longitude, 
        r.room_number, 
        r.floor,
        r.image_url, 
        r.floor_layout_url, 
        b.name_en as building_name_en,
        b.name_th as building_name_th,
        b.image_url as building_image_url
    FROM rooms r
    JOIN buildings b ON r.building_id = b.id
    WHERE r.name_en LIKE ? OR r.name_th LIKE ? OR r.room_number LIKE ?
";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = makeFullUrl($row['image_url'], $baseDir);
        $row['floor_layout_url'] = makeFullUrl($row['floor_layout_url'], $baseDir);
        $row['building_image_url'] = makeFullUrl($row['building_image_url'], $baseDir);
        $results[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    $results = ['error' => 'Database query failed: ' . $e->getMessage()];
}

$conn->close();

// แปลงผลลัพธ์เป็น JSON
echo json_encode($results);
?>