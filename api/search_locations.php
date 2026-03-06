<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db_config.php';
include 'api_config.php';

$search_query = isset($_GET['q']) ? $_GET['q'] : '';

$results = [];

try {
    $search_term = '%' . $search_query . '%';

    // อัปเดต SQL ให้ส่ง Key ออกมาตรงกับ LocationModel ของ Flutter 100%
    $sql = "
    SELECT 
        id, 
        name, 
        'Building' as type, 
        latitude, 
        longitude, 
        NULL as room_number, 
        NULL as floor,
        NULL as image_url, 
        NULL as floor_layout_url,
        name as building_name
    FROM buildings 
    WHERE name LIKE ?
    
    UNION
    
    SELECT 
        r.id, 
        r.name, 
        'Room' as type, 
        b.latitude, 
        b.longitude, 
        r.room_number, 
        r.floor,
        r.image_url, 
        r.floor_layout_url, 
        b.name as building_name
    FROM rooms r
    JOIN buildings b ON r.building_id = b.id
    WHERE r.name LIKE ? OR r.room_number LIKE ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
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