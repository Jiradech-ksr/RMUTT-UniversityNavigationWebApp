<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db_config.php';
include 'api_config.php';


$search_query = isset($_GET['q']) ? $_GET['q'] : '';

$results = [];

try {
    $search_term = '%' . $search_query . '%';

    $sql = "
    SELECT id, name, 'Building' as type, latitude, longitude, NULL as room_number 
    FROM buildings WHERE name LIKE ?
    UNION
    SELECT id, name, 'Room' as type, latitude, longitude, room_number 
    FROM rooms WHERE name LIKE ? OR room_number LIKE ?
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

// Encode the final results array into JSON format and send it as the response.
echo json_encode($results);
?>