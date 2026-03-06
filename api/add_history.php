<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'db_connect.php';

$email = $_POST['email'] ?? '';
$location_id = $_POST['location_id'] ?? '';
$location_type = $_POST['location_type'] ?? 'Room';

if (empty($email) || empty($location_id)) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit();
}

$userQ = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQ->bind_param("s", $email);
$userQ->execute();
$res = $userQ->get_result();
if ($res->num_rows == 0)
    exit();
$user_id = $res->fetch_assoc()['id'];

// ALWAYS INSERT: We want a new row every time so the Stats Screen tracks the exact visit count
$insertQ = $conn->prepare("INSERT INTO history (user_id, location_id, location_type) VALUES (?, ?, ?)");
$insertQ->bind_param("iis", $user_id, $location_id, $location_type);
echo json_encode(["status" => $insertQ->execute() ? "success" : "error"]);
?>