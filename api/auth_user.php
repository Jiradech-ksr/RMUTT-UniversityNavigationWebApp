<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!file_exists('db_connect.php'))
        throw new Exception("db_connect.php not found");
    include 'db_connect.php';

    $json = file_get_contents("php://input");
    $data = json_decode($json);

    if (!isset($data->email) || !isset($data->google_id)) {
        throw new Exception("Missing email or google_id");
    }

    $email = $data->email;
    $google_id = $data->google_id;
    $name = $data->display_name ?? 'User';
    $photo = $data->photo_url ?? '';

    // 1. CHECK IF USER EXISTS
    $checkQuery = $conn->prepare("SELECT id, role, status FROM users WHERE email = ?");
    $checkQuery->bind_param("s", $email);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        // User exists
        $user = $result->fetch_assoc();

        // *** BAN CHECK START ***
        if ($user['status'] === 'banned') {
            echo json_encode([
                "status" => "error",
                "message" => "Account suspended. Contact Admin."
            ]);
            exit(); // Stop execution here
        }
        // *** BAN CHECK END ***

        // Update info
        $stmt = $conn->prepare("UPDATE users SET display_name=?, photo_url=?, google_id=? WHERE email=?");
        $stmt->bind_param("ssss", $name, $photo, $google_id, $email);
        $stmt->execute();

        $role = $user['role']; // Keep existing role
    } else {
        // New User -> Insert as 'student' and 'active' by default
        $stmt = $conn->prepare("INSERT INTO users (google_id, email, display_name, photo_url, role, status) VALUES (?, ?, ?, ?, 'student', 'active')");
        $stmt->bind_param("ssss", $google_id, $email, $name, $photo);
        $stmt->execute();
        $role = 'student';
    }

    // Return success with role info (useful if app needs to show different UI)
    echo json_encode([
        "status" => "success",
        "message" => "User authenticated",
        "role" => $role
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>