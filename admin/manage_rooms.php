<?php
include 'includes/header.php';
require_once '../api/upload_helper.php';
require_once 'includes/env_loader.php';
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'];

// ==========================================
// 1. จัดการเพิ่มข้อมูล (Add Data)
// ==========================================
if (isset($_POST['add_building'])) {
    $name = $_POST['building_name'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $conn->prepare("INSERT INTO buildings (name, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $name, $lat, $lng);
    if ($stmt->execute())
        $alert = "<div class='alert alert-success'>เพิ่มอาคารเรียบร้อยแล้ว</div>";
}

if (isset($_POST['add_room'])) {
    $building_id = (int) $_POST['building_id'];
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name'];
    $floor = $_POST['floor'];
    $floor_layout_url = null;
    $image_url = null;

    // 1. อัปโหลดแผนผังห้อง (Layout)
    $floor_layout_url = uploadFileSafely($_FILES['room_layout'], "layouts", "../");

    if ($floor_layout_url) {
        // It uploaded successfully! Proceed to update the database.
        $stmt_ly = $conn->prepare("UPDATE rooms SET floor_layout_url=? WHERE id=?");
        $stmt_ly->bind_param("si", $floor_layout_url, $id);
        $stmt_ly->execute();
    }

    // 2. อัปโหลดรูปภาพสถานที่จริง (Image)
    $image_url = uploadFileSafely($_FILES['room_image'], "images", "../");

    if ($image_url) {
        // It uploaded successfully! Proceed to update the database.
        $stmt_img = $conn->prepare("UPDATE rooms SET image_url=? WHERE id=?");
        $stmt_img->bind_param("si", $image_url, $id);
        $stmt_img->execute();
    }

    $stmt = $conn->prepare("INSERT INTO rooms (building_id, room_number, name, floor, floor_layout_url, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $building_id, $room_number, $room_name, $floor, $floor_layout_url, $image_url);
    if ($stmt->execute())
        $alert = "<div class='alert alert-success'>เพิ่มห้องเรียนและรูปภาพเรียบร้อยแล้ว</div>";
}

// ==========================================
// 2. จัดการแก้ไขข้อมูล (Edit Data)
// ==========================================
if (isset($_POST['edit_building'])) {
    $id = (int) $_POST['building_id'];
    $name = $_POST['building_name'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $conn->prepare("UPDATE buildings SET name=?, latitude=?, longitude=? WHERE id=?");
    $stmt->bind_param("sddi", $name, $lat, $lng, $id);
    if ($stmt->execute())
        $alert = "<div class='alert alert-success'>แก้ไขข้อมูลอาคารเรียบร้อยแล้ว</div>";
}

if (isset($_POST['edit_room'])) {
    $id = (int) $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name'];
    $floor = $_POST['floor'];

    // อัปเดตข้อมูลทั่วไป
    $stmt = $conn->prepare("UPDATE rooms SET room_number=?, name=?, floor=? WHERE id=?");
    $stmt->bind_param("sssi", $room_number, $room_name, $floor, $id);
    $stmt->execute();

    // 1. อัปเดตแผนผังห้อง (Layout)
    if (isset($_FILES['room_layout']) && $_FILES['room_layout']['error'] == 0) {
        $file_name = time() . "_layout_" . basename($_FILES["room_layout"]["name"]);
        $target_file = "../uploads/layouts/" . $file_name;
        if (move_uploaded_file($_FILES["room_layout"]["tmp_name"], $target_file)) {
            $floor_layout_url = "uploads/layouts/" . $file_name;
            $stmt_ly = $conn->prepare("UPDATE rooms SET floor_layout_url=? WHERE id=?");
            $stmt_ly->bind_param("si", $floor_layout_url, $id);
            $stmt_ly->execute();
        }
    }

    // 2. อัปเดตรูปภาพสถานที่จริง (Image)
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $file_name = time() . "_img_" . basename($_FILES["room_image"]["name"]);
        $target_file = "../uploads/images/" . $file_name;
        if (move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
            $image_url = "uploads/images/" . $file_name;
            $stmt_img = $conn->prepare("UPDATE rooms SET image_url=? WHERE id=?");
            $stmt_img->bind_param("si", $image_url, $id);
            $stmt_img->execute();
        }
    }
    $alert = "<div class='alert alert-success'>แก้ไขข้อมูลห้องเรียนเรียบร้อยแล้ว</div>";
}

// ==========================================
// 3. จัดการลบข้อมูล (Delete Data)
// ==========================================
if (isset($_GET['delete_room'])) {
    $id = (int) $_GET['delete_room'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>window.location='manage_rooms.php';</script>";
    }
}

if (isset($_GET['delete_building'])) {
    $id = (int) $_GET['delete_building'];
    $stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>window.location='manage_rooms.php';</script>";
    }
}

// ดึงข้อมูลอาคารทั้งหมด
$buildings = $conn->query("SELECT * FROM buildings ORDER BY name ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark"><i class="fas fa-building text-primary me-2"></i> จัดการอาคารและห้องเรียน</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
        <i class="fas fa-plus"></i> เพิ่มอาคารใหม่
    </button>
</div>

<?php if (isset($alert))
    echo $alert; ?>

<div class="accordion shadow-sm" id="buildingAccordion">
    <?php
    if ($buildings->num_rows > 0):
        while ($b = $buildings->fetch_assoc()):
            $b_id = $b['id'];
            $b_name = htmlspecialchars($b['name'], ENT_QUOTES);
            $b_lat = $b['latitude'] ?? '';
            $b_lng = $b['longitude'] ?? '';

            $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
            ?>
            <div class="accordion-item border-0 border-bottom mb-1 rounded">
                <h2 class="accordion-header" id="heading<?= $b_id; ?>">
                    <button class="accordion-button collapsed fw-semi bold text-indigo" style="background-color: #f8f9fc;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $b_id; ?>">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i> <?= htmlspecialchars($b['name']); ?>
                        <span class="badge bg-secondary ms-3"><?= $rooms->num_rows; ?> ห้อง</span>
                    </button>
                </h2>
                <div id="collapse<?= $b_id; ?>" class="accordion-collapse collapse" data-bs-parent="#buildingAccordion">
                    <div class="accordion-body bg-white">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#addRoomModal"
                                onclick="setRoomModalData(<?= $b_id; ?>, '<?= $b_name; ?>')">
                                <i class="fas fa-plus"></i> เพิ่มห้อง
                            </button>
                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal"
                                data-bs-target="#editBuildingModal"
                                onclick="editBuildingData(<?= $b_id; ?>, '<?= $b_name; ?>', '<?= $b_lat; ?>', '<?= $b_lng; ?>')">
                                <i class="fas fa-edit"></i> แก้ไขอาคารพิกัด
                            </button>
                            <a href="?delete_building=<?= $b_id; ?>" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('ยืนยันการลบอาคารนี้ (ห้องทั้งหมดในอาคารจะถูกลบด้วย)?');">
                                <i class="fas fa-trash"></i> ลบอาคาร
                            </a>
                        </div>

                        <?php if ($rooms->num_rows > 0): ?>
                            <table class="table table-sm table-hover mt-2 border rounded overflow-hidden">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3">เลขห้อง</th>
                                        <th>ชื่อห้อง/รายละเอียด</th>
                                        <th>ชั้น</th>
                                        <th class="text-end px-3">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $rooms->fetch_assoc()):
                                        $r_name_safe = htmlspecialchars($r['name'], ENT_QUOTES);
                                        ?>
                                        <tr>
                                            <td class="px-3 fw-semi bold text-primary"><?= htmlspecialchars($r['room_number']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($r['name']); ?>
                                                <?php if (!empty($r['image_url']) || !empty($r['floor_layout_url'])): ?>
                                                    <span class="badge bg-info ms-2"><i class="fas fa-image"></i> มีรูปภาพ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>ชั้น <?= htmlspecialchars($r['floor']); ?></td>
                                            <td class="text-end px-3">
                                                <button class="btn btn-sm btn-warning py-0 me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editRoomModal"
                                                    onclick="editRoomData(<?= $r['id']; ?>, '<?= htmlspecialchars($r['room_number'], ENT_QUOTES); ?>', '<?= $r_name_safe; ?>', '<?= htmlspecialchars($r['floor'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-edit"></i> แก้ไข
                                                </button>
                                                <a href="?delete_room=<?= $r['id']; ?>" class="btn btn-sm btn-danger py-0"
                                                    onclick="return confirm('ยืนยันการลบห้อง <?= htmlspecialchars($r['room_number']); ?>?');">
                                                    <i class="fas fa-trash-alt"></i> ลบ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted text-center my-4 py-3 bg-light rounded"><i class="fas fa-info-circle me-2"></i>
                                ยังไม่มีข้อมูลห้องในอาคารนี้</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
        <div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>
            ยังไม่มีข้อมูลอาคารในระบบ</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addBuildingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #1A237E;">
                <h5 class="modal-title"><i class="fas fa-building"></i> เพิ่มอาคารใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่ออาคาร <span class="text-danger">*</span></label>
                        <input type="text" name="building_name" class="form-control" required
                            placeholder="เช่น ตึกวิศวกรรมคอมพิวเตอร์">
                    </div>
                    <label class="form-label fw-bold">ระบุพิกัดตำแหน่งอาคาร (คลิกบนแผนที่)</label>
                    <div id="addMapPicker"
                        style="height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; background-color: #f8f9fa;">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ละติจูด (Latitude)</label>
                            <input type="text" id="add_latInput" name="latitude" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ลองจิจูด (Longitude)</label>
                            <input type="text" id="add_lngInput" name="longitude" class="form-control bg-light"
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="add_building" class="btn btn-primary">บันทึกอาคาร</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-door-open"></i> เพิ่มห้องเรียนและรูปภาพ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="building_id" id="add_modal_building_id">
                    <div class="mb-3">
                        <label class="form-label text-muted">อาคารที่เลือก</label>
                        <input type="text" id="add_modal_building_name" class="form-control bg-light fw-bold" readonly>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">เลขห้อง <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" required placeholder="เช่น 4401">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">ชั้น <span class="text-danger">*</span></label>
                            <input type="text" name="floor" class="form-control" required placeholder="เช่น 4">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อห้อง / รายละเอียด</label>
                        <input type="text" name="room_name" class="form-control"
                            placeholder="เช่น ห้องปฏิบัติการคอมพิวเตอร์">
                    </div>

                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-image me-1"></i> 1.
                            รูปภาพสถานที่จริง (Room Image)</label>
                        <input type="file" name="room_image" class="form-control mb-3"
                            accept="image/jpeg, image/png, image/jpg">

                        <label class="form-label fw-bold text-success"><i class="fas fa-map me-1"></i> 2. รูปแผนผังห้อง
                            (Room Layout)</label>
                        <input type="file" name="room_layout" class="form-control"
                            accept="image/jpeg, image/png, image/jpg">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="add_room" class="btn btn-success">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editBuildingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> แก้ไขข้อมูลและพิกัดอาคาร</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="building_id" id="edit_building_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่ออาคาร <span class="text-danger">*</span></label>
                        <input type="text" name="building_name" id="edit_building_name" class="form-control" required>
                    </div>
                    <label class="form-label fw-bold">แก้ไขพิกัดตำแหน่งอาคาร (คลิกย้ายหมุดบนแผนที่)</label>
                    <div id="editMapPicker"
                        style="height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; background-color: #f8f9fa;">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ละติจูด (Latitude)</label>
                            <input type="text" id="edit_latInput" name="latitude" class="form-control bg-light"
                                readonly>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ลองจิจูด (Longitude)</label>
                            <input type="text" id="edit_lngInput" name="longitude" class="form-control bg-light"
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="edit_building" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> แก้ไขข้อมูลห้องเรียนและรูปภาพ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="room_id" id="edit_room_id">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">เลขห้อง <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">ชั้น <span class="text-danger">*</span></label>
                            <input type="text" name="floor" id="edit_room_floor" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อห้อง / รายละเอียด</label>
                        <input type="text" name="room_name" id="edit_room_name" class="form-control">
                    </div>

                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-semi bold text-primary"><i class="fas fa-image me-1"></i> 1.
                            เปลี่ยนรูปภาพสถานที่จริง</label>
                        <input type="file" name="room_image" class="form-control mb-1"
                            accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted d-block mb-3">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน</small>

                        <label class="form-label fw-semi bold text-success"><i class="fas fa-map me-1"></i> 2.
                            เปลี่ยนรูปแผนผังห้อง</label>
                        <input type="file" name="room_layout" class="form-control mb-1"
                            accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted d-block">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="edit_room" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- การโยนข้อมูลเข้า Modal เพิ่มห้อง ---
    function setRoomModalData(buildingId, buildingName) {
        document.getElementById('add_modal_building_id').value = buildingId;
        document.getElementById('add_modal_building_name').value = buildingName;
    }

    // --- การโยนข้อมูลเข้า Modal แก้ไขห้อง ---
    function editRoomData(id, number, name, floor) {
        document.getElementById('edit_room_id').value = id;
        document.getElementById('edit_room_number').value = number;
        document.getElementById('edit_room_name').value = name;
        document.getElementById('edit_room_floor').value = floor;
    }

    // --- การโยนข้อมูลเข้า Modal แก้ไขอาคาร ---
    function editBuildingData(id, name, lat, lng) {
        document.getElementById('edit_building_id').value = id;
        document.getElementById('edit_building_name').value = name;
        document.getElementById('edit_latInput').value = lat;
        document.getElementById('edit_lngInput').value = lng;

        // อัปเดตแผนที่ตอนแก้ไขให้เลื่อนไปจุดเดิม
        if (editMap) {
            let loc = {
                lat: parseFloat(lat) || 14.0367,
                lng: parseFloat(lng) || 100.7300
            };
            editMap.setCenter(loc);
            if (editMarker) {
                editMarker.setPosition(loc);
            } else if (lat && lng) { // สร้างหมุดใหม่ถ้ามีพิกัด
                editMarker = new google.maps.Marker({ position: loc, map: editMap });
            }
        }
    }

    // ---------------- Google Maps Logic ----------------
    let addMap, editMap;
    let addMarker, editMarker;
    const initialLocation = { lat: 14.0367, lng: 100.7300 }; // ศูนย์กลาง RMUTT

    function initMap() {
        // 1. แผนที่สำหรับ "เพิ่มอาคาร"
        addMap = new google.maps.Map(document.getElementById("addMapPicker"), {
            center: initialLocation, zoom: 16, mapTypeControl: true, streetViewControl: false
        });
        addMap.addListener("click", (e) => {
            if (addMarker) addMarker.setPosition(e.latLng);
            else addMarker = new google.maps.Marker({ position: e.latLng, map: addMap, animation: google.maps.Animation.DROP });
            document.getElementById("add_latInput").value = e.latLng.lat().toFixed(6);
            document.getElementById("add_lngInput").value = e.latLng.lng().toFixed(6);
        });

        // 2. แผนที่สำหรับ "แก้ไขอาคาร"
        editMap = new google.maps.Map(document.getElementById("editMapPicker"), {
            center: initialLocation, zoom: 16, mapTypeControl: true, streetViewControl: false
        });
        editMap.addListener("click", (e) => {
            if (editMarker) editMarker.setPosition(e.latLng);
            else editMarker = new google.maps.Marker({ position: e.latLng, map: editMap, animation: google.maps.Animation.DROP });
            document.getElementById("edit_latInput").value = e.latLng.lat().toFixed(6);
            document.getElementById("edit_lngInput").value = e.latLng.lng().toFixed(6);
        });
    }

    // แก้บั๊กแผนที่โหลดไม่เต็มเมื่ออยู่ใน Modal
    document.getElementById('addBuildingModal').addEventListener('shown.bs.modal', function () {
        if (addMap) {
            google.maps.event.trigger(addMap, 'resize');
            addMap.setCenter(addMarker ? addMarker.getPosition() : initialLocation);
        }
    });
    document.getElementById('editBuildingModal').addEventListener('shown.bs.modal', function () {
        if (editMap) {
            google.maps.event.trigger(editMap, 'resize');
            let currentLat = document.getElementById('edit_latInput').value;
            let currentLng = document.getElementById('edit_lngInput').value;
            if (currentLat && currentLng) {
                editMap.setCenter({ lat: parseFloat(currentLat), lng: parseFloat(currentLng) });
            } else {
                editMap.setCenter(initialLocation);
            }
        }
    });
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&callback=initMap" async defer></script>

<?php include 'includes/footer.php'; ?>