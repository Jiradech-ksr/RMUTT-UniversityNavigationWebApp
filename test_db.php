<?php
include 'api/db_connect.php';
$res = $conn->query('SELECT id, user_id, room_id, issue_type, image_url FROM reports ORDER BY id DESC LIMIT 10');
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>