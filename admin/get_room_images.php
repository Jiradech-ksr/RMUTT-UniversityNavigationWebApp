<?php
require_once '../api/db_connect.php';
$room_id = (int)($_GET['room_id'] ?? 0);
$result = $conn->query("SELECT id, image_url FROM room_images WHERE room_id = $room_id ORDER BY sort_order ASC");
$images = [];
while ($r = $result->fetch_assoc()) {
    $images[] = ['id' => $r['id'], 'image_url' => $r['image_url']];
}
header('Content-Type: application/json');
echo json_encode($images);
