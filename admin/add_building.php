<?php
include '../api/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name_en = $_POST['name_en'];
    $name_th = $_POST['name_th'] ?? '';
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $desc = $_POST['description'];

    // NOTE: We do not upload an image here anymore.
    // We just insert the text details.

    $stmt = $conn->prepare("INSERT INTO buildings (name_en, name_th, latitude, longitude, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $name_en, $name_th, $lat, $lng, $desc);

    if ($stmt->execute()) {
        header("Location: add_building.php?msg=" . urlencode("Building '$name_en' added successfully!"));
        exit();
    } else {
        $message = "Database Error: " . $stmt->error;
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Building</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Add New Building</h3>
            </div>
            <div class="card-body">

                <?php if ($message != ""): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>

                <form action="add_building.php" method="post">

                    <div class="mb-3">
                        <label>Building Name (English):</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Building Name (ภาษาไทย):</label>
                        <input type="text" name="name_th" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Latitude:</label>
                            <input type="text" name="latitude" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Longitude:</label>
                            <input type="text" name="longitude" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Description:</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Building</button>
                    <a href="dashboard.php" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>