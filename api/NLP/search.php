<?php
header('Content-Type: application/json');

$userInput = isset($_GET['query']) ? trim($_GET['query']) : ''; // Example: "Where is CPE?"

$intent = 'navigation';
$entity = $userInput;
$confidence = 0.0;

// --- STEP 1: Talk to the TensorFlow Python API ---
if (!empty($userInput)) {
    $pythonApiUrl = "http://localhost:8000/predict?q=" . urlencode($userInput);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $pythonApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $apiResponse = @curl_exec($ch);
    curl_close($ch);

    if ($apiResponse) {
        $nlpData = json_decode($apiResponse, true);
        if (is_array($nlpData)) {
            $intent = $nlpData['intent'] ?? 'navigation';
            $entity = $nlpData['entity'] ?? $userInput;
            $confidence = $nlpData['confidence'] ?? 0.0;
        }
    }
}

// --- STEP 2: Database Logic based on ML Intent ---
$host = "localhost";
$db = "universitynavigation_db";
$user = "root";
$pass = "";
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($intent == "navigation") {
    // Search buildings and rooms natively using our specific structure
    $stmt = $pdo->prepare("
        SELECT 
            id, name_en, name_th, 'Building' as type, latitude, longitude, 
            NULL as room_number, NULL as floor, image_url, NULL as floor_layout_url,
            name_en as building_name_en, name_th as building_name_th, image_url as building_image_url, details
        FROM buildings 
        WHERE name_en LIKE ? OR name_th LIKE ?
        UNION
        SELECT 
            r.id, r.name_en, r.name_th, 'Room' as type, b.latitude, b.longitude, 
            r.room_number, r.floor, 
            (SELECT image_url FROM room_images WHERE room_id = r.id ORDER BY sort_order ASC LIMIT 1) as image_url, 
            r.floor_layout_url, b.name_en as building_name_en, b.name_th as building_name_th, b.image_url as building_image_url, r.details
        FROM rooms r
        JOIN buildings b ON r.building_id = b.id
        WHERE r.name_en LIKE ? OR r.name_th LIKE ? OR r.room_number LIKE ?
    ");
    $stmt->execute(["%$entity%", "%$entity%", "%$entity%", "%$entity%", "%$entity%"]);
} else {
    // If ML says it's info, search the strictly assigned 'campus_info' table
    $stmt = $pdo->prepare("SELECT * FROM campus_info WHERE title LIKE ? OR description LIKE ?");
    $stmt->execute(["%$entity%", "%$entity%"]);
}

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($intent == "navigation") {
    $baseDir = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
    function makeFullUrl($path, $baseDir) {
        if (empty($path)) return null;
        if (strpos($path, 'http') === 0) return $path;
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $baseDir . '/' . ltrim($path, '/');
    }
    foreach ($results as &$row) {
        $row['image_url'] = makeFullUrl($row['image_url'], $baseDir);
        $row['floor_layout_url'] = makeFullUrl($row['floor_layout_url'], $baseDir);
        $row['building_image_url'] = makeFullUrl($row['building_image_url'], $baseDir);
    }
}

// --- STEP 3: Return to Flutter ---
echo json_encode([
    "ml_intent" => $intent,
    "confidence" => $confidence,
    "entity" => $entity,
    "data" => $results
]);
?>