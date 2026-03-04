<?php
include '../api/db_connect.php';

// --- CONFIGURATION ---
// Change this to your computer's IP address
$base_url = "http://192.168.100.35";
$message = "";

// 1. FETCH BUILDINGS FOR DROPDOWN
// We need this so the user can select "Building 1" or "Engineering" from a list
$buildings = [];
$sql = "SELECT id, name FROM buildings ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buildings[] = $row;
    }
}

// 2. HELPER FUNCTION TO HANDLE UPLOADS
function uploadFile($fileInputName, $subFolder, $base_url)
{
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != 0) {
        return null; // No file uploaded or error
    }

    $target_dir = "../uploads/" . $subFolder . "/";

    // Create folder if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Rename file to unique timestamp (e.g., room_123456789.jpg)
    $filename = time() . "_" . basename($_FILES[$fileInputName]["name"]);
    $target_file = $target_dir . $filename;

    // Simple check: is it an image?
    $check = getimagesize($_FILES[$fileInputName]["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
            // Return the full URL for the database
            return $base_url . "/uploads/" . $subFolder . "/" . $filename;
        }
    }
    return null; // Upload failed
}

// 3. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $room_num = $_POST['room_number'];
    $floor = $_POST['floor'];
    $building_id = $_POST['building_id'];
    $usage = $_POST['usage_type'];

    // Upload Room Image
    $room_img_url = uploadFile("roomImage", "rooms", $base_url);

    // Upload Floor Layout
    $layout_url = uploadFile("layoutImage", "layouts", $base_url);

    // Note: If you haven't added 'image_url' to your 'rooms' table yet, run this SQL:
    // ALTER TABLE rooms ADD COLUMN image_url VARCHAR(255);

    $stmt = $conn->prepare("INSERT INTO rooms (name, room_number, floor, building_id, usage_type, image_url, floor_layout_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisss", $name, $room_num, $floor, $building_id, $usage, $room_img_url, $layout_url);

    if ($stmt->execute()) {
        $message = "Room '$name' added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5 mb-5">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h3>Add New Room</h3>
            </div>
            <div class="card-body">

                <?php if ($message != ""): ?>
                    <div class="alert alert-info">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="add_room.php" method="post" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Select Building:</label>
                        <select name="building_id" class="form-select" required>
                            <option value="">-- Choose a Building --</option>
                            <?php foreach ($buildings as $b): ?>
                                <option value="<?php echo $b['id']; ?>">
                                    <?php echo $b['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Room Name (e.g. Computer Lab):</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Room Number:</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Floor:</label>
                            <input type="number" name="floor" class="form-control" value="1" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Usage Type:</label>
                        <select name="usage_type" class="form-select">
                            <option value="Classroom">Classroom</option>
                            <option value="Lab">Lab</option>
                            <option value="Office">Office</option>
                            <option value="Meeting Room">Meeting Room</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Room Photo (Front View):</label>
                        <input type="file" name="roomImage" class="form-control">
                        <small class="text-muted">This will be shown in the image gallery.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Floor Layout (Map Image):</label>
                        <input type="file" name="layoutImage" class="form-control">
                        <small class="text-muted">This will be shown in the "Floor Layout" section.</small>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-success">Save Room</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>