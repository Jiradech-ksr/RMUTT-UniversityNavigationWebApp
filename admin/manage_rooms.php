<?php
include 'includes/header.php';
require_once '../api/upload_helper.php';
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'];
// ==========================================
// 1. จัดการเพิ่มข้อมูล (Add Data)
// ==========================================
$faculty_query = $conn->query("SELECT * FROM faculties ORDER BY id ASC");
$faculties_list = [];
while ($row = $faculty_query->fetch_assoc()) {
    $faculties_list[] = $row;
}

if (isset($_POST['add_building'])) {
    $name = $_POST['building_name'];
    $faculty_id = (int) $_POST['faculty_id'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $image_url = null;
    $upload_error = "";

    // Check if a file was actually uploaded by the user
    if (isset($_FILES['building_image']) && $_FILES['building_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_url = uploadFileSafely($_FILES['building_image'], "buildings", "../");
        if ($image_url === null) {
            $upload_error = " (แต่รูปภาพอัปโหลดไม่สำเร็จ กรุณาตรวจสอบขนาดหรือนามสกุลไฟล์)";
        }
    }

    $name_en = $_POST['building_name_en'];
    $name_th = $_POST['building_name_th'];

    $stmt = $conn->prepare("INSERT INTO buildings (name_en, name_th, latitude, longitude, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $name_en, $name_th, $lat, $lng, $image_url);

    if ($stmt->execute()) {
        if ($upload_error) {
            $_SESSION['alert'] = "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> เพิ่มอาคารแล้ว $upload_error</div>";
        } else {
            $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> เพิ่มอาคารเรียบร้อยแล้ว</div>";
        }
        header("Location: manage_rooms.php");
        exit();
    } else {
        $alert = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
}

if (isset($_POST['add_room'])) {
    $building_id = (int) $_POST['building_id'];
    $room_number = $_POST['room_number'];
    $room_name_en = $_POST['room_name_en'] ?? ''; // Added room_name_en
    $room_name_th = $_POST['room_name_th'] ?? ''; // Added room_name_th
    $floor = $_POST['floor'];

    // รูปภาพ (ไม่บังคับ)
    $image_url = uploadFileSafely($_FILES['room_image'], "images", "../");
    $floor_layout_url = uploadFileSafely($_FILES['room_layout'], "layouts", "../");

    $stmt = $conn->prepare("INSERT INTO rooms (building_id, room_number, name_en, name_th, floor, floor_layout_url, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $building_id, $room_number, $room_name_en, $room_name_th, $floor, $floor_layout_url, $image_url);

    if ($stmt->execute()) {
        $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> เพิ่มห้องเรียนและรูปภาพเรียบร้อยแล้ว</div>";
        header("Location: manage_rooms.php");
        exit();
    } else {
        $alert = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
}

// ==========================================
// 2. จัดการแก้ไขข้อมูล (Edit Data)
// ==========================================
if (isset($_POST['edit_building'])) {
    $id = (int) $_POST['building_id'];
    $name_en = $_POST['building_name_en'];
    $name_th = $_POST['building_name_th'] ?? ''; // Added name_th
    $faculty_id = (int) $_POST['faculty_id'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $conn->prepare("UPDATE buildings SET name_en=?, name_th=?, department_id=?, latitude=?, longitude=? WHERE id=?");
    $stmt->bind_param("ssiddi", $name_en, $name_th, $faculty_id, $lat, $lng, $id);

    if ($stmt->execute()) {
        // Update Building Image if a new one is uploaded
        if (isset($_FILES['building_image']) && $_FILES['building_image']['error'] == 0) {
            $image_url = uploadFileSafely($_FILES['building_image'], "buildings", "../");
            if ($image_url) {
                $stmt_img = $conn->prepare("UPDATE buildings SET image_url=? WHERE id=?");
                $stmt_img->bind_param("si", $image_url, $id);
                $stmt_img->execute();
            }
        }
        $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> แก้ไขข้อมูลอาคารเรียบร้อยแล้ว</div>";
        header("Location: manage_rooms.php");
        exit();
    } else {
        $alert = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
}

if (isset($_POST['edit_room'])) {
    $id = (int) $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $room_name_en = $_POST['room_name_en'] ?? '';
    $room_name_th = $_POST['room_name_th'] ?? '';
    $floor = $_POST['floor'];

    // อัปเดตข้อมูลทั่วไป
    $stmt = $conn->prepare("UPDATE rooms SET room_number=?, name_en=?, name_th=?, floor=? WHERE id=?");
    $stmt->bind_param("ssssi", $room_number, $room_name_en, $room_name_th, $floor, $id);

    if ($stmt->execute()) {
        // 1. อัปเดตแผนผังห้อง (Layout)
        if (isset($_FILES['room_layout']) && $_FILES['room_layout']['error'] == 0) {
            $floor_layout_url = uploadFileSafely($_FILES['room_layout'], "layouts", "../");
            if ($floor_layout_url) {
                $stmt_ly = $conn->prepare("UPDATE rooms SET floor_layout_url=? WHERE id=?");
                $stmt_ly->bind_param("si", $floor_layout_url, $id);
                $stmt_ly->execute();
            }
        }

        // 2. อัปเดตรูปภาพสถานที่จริง (Image)
        if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
            $image_url = uploadFileSafely($_FILES['room_image'], "images", "../");
            if ($image_url) {
                $stmt_img = $conn->prepare("UPDATE rooms SET image_url=? WHERE id=?");
                $stmt_img->bind_param("si", $image_url, $id);
                $stmt_img->execute();
            }
        }
        
        $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> แก้ไขข้อมูลห้องเรียนเรียบร้อยแล้ว</div>";
        header("Location: manage_rooms.php");
        exit();
    } else {
        $alert = "<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
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
    } else {
        $alert = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Error: " . htmlspecialchars($stmt->error) . "</div>";
    }
}

if (isset($_GET['delete_building'])) {
    $id = (int) $_GET['delete_building'];
    $stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirect with a success flag in the URL
        echo "<script>window.location='manage_rooms.php?msg=deleted';</script>";
    } else {
        // Usually fails because there are still rooms in this building
        $alert = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> ไม่สามารถลบอาคารได้: กรุณาลบห้องเรียนในอาคารนี้ให้หมดก่อน (Error: " . htmlspecialchars($stmt->error) . ")</div>";
    }
}

// ดึงข้อมูลอาคารทั้งหมด
$buildings = $conn->query("SELECT * FROM buildings ORDER BY name_en ASC"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark"><i class="fas fa-building text-primary me-2"></i> จัดการอาคารและห้องเรียน</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
        <i class="fas fa-plus"></i> เพิ่มอาคารใหม่
    </button>
</div>

<?php 
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
if (isset($alert)) echo $alert; 
?>

<div class="accordion shadow-sm" id="buildingAccordion">
    <?php
    if ($buildings->num_rows > 0):
        while ($b = $buildings->fetch_assoc()):
            $b_id = $b['id'];
            $b_name = htmlspecialchars($b['name_en'], ENT_QUOTES);
            $b_lat = $b['latitude'] ?? '';
            $b_lng = $b['longitude'] ?? '';

            $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
            ?>
            <div class="accordion-item border-0 border-bottom mb-1 rounded">
                <h2 class="accordion-header" id="heading<?= $b_id; ?>">
                    <button class="accordion-button collapsed fw-semi bold text-indigo" style="background-color: #f8f9fc;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $b_id; ?>">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i> <?= htmlspecialchars($b['name_en']); ?>
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
                                onclick="editBuildingData(<?= $b_id; ?>, '<?= htmlspecialchars($b['name_en'], ENT_QUOTES); ?>', '<?= htmlspecialchars($b['name_th'] ?? '', ENT_QUOTES); ?>', '<?= $b_lat; ?>', '<?= $b_lng; ?>')">
                                <i class="fas fa-edit"></i> แก้ไขพิกัด/ข้อมูล
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
                                        $r_name_safe = htmlspecialchars($r['name_en'] ?? '', ENT_QUOTES);
                                        ?>
                                        <tr>
                                            <td class="px-3 fw-semi bold text-primary"><?= htmlspecialchars($r['room_number']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($r['name_en'] ?? ''); ?>
                                                <?php if (!empty($r['image_url']) || !empty($r['floor_layout_url'])): ?>
                                                    <span class="badge bg-info ms-2"><i class="fas fa-image"></i> มีรูปภาพ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>ชั้น <?= htmlspecialchars($r['floor']); ?></td>
                                            <td class="text-end px-3">
                                                <button class="btn btn-sm btn-warning py-0 me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editRoomModal"
                                                    onclick="editRoomData(<?= $r['id']; ?>, '<?= htmlspecialchars($r['room_number'], ENT_QUOTES); ?>', '<?= htmlspecialchars($r['name_en'] ?? '', ENT_QUOTES); ?>', '<?= htmlspecialchars($r['name_th'] ?? '', ENT_QUOTES); ?>', '<?= htmlspecialchars($r['floor'], ENT_QUOTES); ?>')">
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
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>ชื่ออาคาร (English)</label>
                        <input type="text" name="building_name_en" class="form-control" required>
                        <label>ชื่ออาคาร (ภาษาไทย)</label>
                        <input type="text" name="building_name_th" class="form-control"
                            placeholder="เช่น ตึกวิศวกรรมคอมพิวเตอร์">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">คณะ / หน่วยงาน (Faculty) <span
                                class="text-danger">*</span></label>
                        <select name="faculty_id" class="form-select" required>
                            <?php foreach ($faculties_list as $fac): ?>
                                <option value="<?= $fac['id']; ?>">
                                    <?= htmlspecialchars($fac['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-image me-1"></i> รูปภาพอาคาร
                            (Building Image)</label>
                        <input type="file" name="building_image" class="form-control"
                            accept="image/jpeg, image/png, image/jpg">
                    </div>
                    <label class="form-label fw-bold">ระบุพิกัดตำแหน่งอาคาร (คลิกบนแผนที่)</label>
                    <div id="addMapPicker"
                        style="height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; background-color: #f8f9fa;">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ละติจูด (Latitude)</label>
                            <input type="text" id="add_latInput" name="latitude" class="form-control bg-light" readonly
                                required>
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
                        <label class="form-label fw-bold">ชื่อห้อง (English)</label>
                        <input type="text" name="room_name_en" class="form-control"
                            placeholder="เช่น Computer Programming Lab">
                            
                        <label class="form-label fw-bold mt-2">ชื่อห้อง (ภาษาไทย)</label>
                        <input type="text" name="room_name_th" class="form-control"
                            placeholder="เช่น ห้องปฏิบัติการเขียนโปรแกรมคอมพิวเตอร์">
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
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="building_id" id="edit_building_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่ออาคาร (English) <span class="text-danger">*</span></label>
                        <input type="text" name="building_name_en" id="edit_building_name_en" class="form-control" required>
                        <label class="form-label fw-bold mt-2">ชื่ออาคาร (ภาษาไทย)</label>
                        <input type="text" name="building_name_th" id="edit_building_name_th" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">คณะ / หน่วยงาน (Faculty) <span
                                class="text-danger">*</span></label>
                        <select name="faculty_id" id="edit_faculty_id" class="form-select" required>
                            <?php foreach ($faculties_list as $fac): ?>
                                <option value="<?= $fac['id']; ?>">
                                    <?= htmlspecialchars($fac['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-semi bold text-primary"><i class="fas fa-image me-1"></i>
                            เปลี่ยนรูปภาพอาคาร</label>
                        <input type="file" name="building_image" class="form-control mb-1"
                            accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted d-block">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน</small>
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
                        <label class="form-label fw-bold">ชื่อห้อง (English)</label>
                        <input type="text" name="room_name_en" id="edit_room_name_en" class="form-control">
                        
                        <label class="form-label fw-bold mt-2">ชื่อห้อง (ภาษาไทย)</label>
                        <input type="text" name="room_name_th" id="edit_room_name_th" class="form-control">
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
    function editRoomData(id, number, name_en, name_th, floor) {
        document.getElementById('edit_room_id').value = id;
        document.getElementById('edit_room_number').value = number;
        document.getElementById('edit_room_name_en').value = name_en;
        document.getElementById('edit_room_name_th').value = name_th;
        document.getElementById('edit_room_floor').value = floor;
    }

    // --- การโยนข้อมูลเข้า Modal แก้ไขอาคาร ---
    function editBuildingData(id, name_en, name_th, lat, lng) {
        document.getElementById('edit_building_id').value = id;
        document.getElementById('edit_building_name_en').value = name_en;
        document.getElementById('edit_building_name_th').value = name_th;
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