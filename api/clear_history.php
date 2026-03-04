<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db_connect.php';

$email = $_POST['email'] ?? '';

$sql = "DELETE h FROM history h 
        JOIN users u ON h.user_id = u.id 
        WHERE u.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

echo json_encode(["status" => "success"]);
?>