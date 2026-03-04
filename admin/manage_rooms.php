<?php
require_once 'includes/env_loader.php';
include 'includes/header.php';
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'];
// ==========================================
// 1. จัดการเพิ่มข้อมูล (Add Data)
// ==========================================

// เพิ่มอาคารใหม่
if (isset($_POST['add_building'])) {
    $name = $_POST['building_name'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $conn->prepare("INSERT INTO buildings (name, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $name, $lat, $lng);
    if ($stmt->execute()) {
        $alert = "<div class='alert alert-success'>เพิ่มอาคารเรียบร้อยแล้ว</div>";
    }
}

// เพิ่มห้องใหม่
if (isset($_POST['add_room'])) {
    $building_id = (int) $_POST['building_id'];
    $room_number = $_POST['room_number'];
    $room_name = $_POST['room_name'];
    $floor = $_POST['floor'];

    $stmt = $conn->prepare("INSERT INTO rooms (building_id, room_number, name, floor) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $building_id, $room_number, $room_name, $floor);
    if ($stmt->execute()) {
        $alert = "<div class='alert alert-success'>เพิ่มห้องเรียนเรียบร้อยแล้ว</div>";
    }
}

// ==========================================
// 2. จัดการลบข้อมูล (Delete Data)
// ==========================================
if (isset($_GET['delete_room'])) {
    $id = (int) $_GET['delete_room'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.location='manage_rooms.php';</script>";
}
if (isset($_GET['delete_building'])) {
    $id = (int) $_GET['delete_building'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>window.location='manage_rooms.php';</script>";
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
            $b_name = htmlspecialchars($b['name']);
            // ดึงข้อมูลห้องที่อยู่ในอาคารนี้
            $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
            ?>
            <div class="accordion-item border-0 border-bottom mb-1 rounded">
                <h2 class="accordion-header" id="heading<?= $b_id; ?>">
                    <button class="accordion-button collapsed fw-bold text-indigo" style="background-color: #f8f9fc;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $b_id; ?>">
                        <i class="fas fa-map-marker-alt me-2 text-danger"></i> <?= $b_name; ?>
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
                                    <?php while ($r = $rooms->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-3 fw-bold text-primary"><?= htmlspecialchars($r['room_number']); ?></td>
                                            <td><?= htmlspecialchars($r['name']); ?></td>
                                            <td>ชั้น <?= htmlspecialchars($r['floor']); ?></td>
                                            <td class="text-end px-3">
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
            <?php
        endwhile;
    else:
        ?>
        <div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>
            ยังไม่มีข้อมูลอาคารในระบบ กรุณาเพิ่มอาคารใหม่</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addBuildingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #1A237E;">
                <h5 class="modal-title"><i class="fas fa-map-marked-alt"></i> เพิ่มอาคารใหม่ และระบุพิกัด</h5>
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
                    <div id="mapPicker"
                        style="height: 350px; width: 100%; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; background-color: #f8f9fa;">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ละติจูด (Latitude)</label>
                            <input type="text" id="latInput" name="latitude" class="form-control bg-light" readonly
                                placeholder="คลิกแผนที่เพื่อเลือก">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-muted small">ลองจิจูด (Longitude)</label>
                            <input type="text" id="lngInput" name="longitude" class="form-control bg-light" readonly
                                placeholder="คลิกแผนที่เพื่อเลือก">
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
                <h5 class="modal-title"><i class="fas fa-door-open"></i> เพิ่มห้องเรียน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="building_id" id="modal_building_id">
                    <div class="mb-3">
                        <label class="form-label text-muted">อาคารที่เลือก</label>
                        <input type="text" id="modal_building_name" class="form-control bg-light fw-bold" readonly>
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
                            placeholder="เช่น ห้องปฏิบัติการคอมพิวเตอร์ (ถ้ามี)">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="add_room" class="btn btn-success">บันทึกห้องเรียน</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // จัดการค่า Modal เพิ่มห้อง
    function setRoomModalData(buildingId, buildingName) {
        document.getElementById('modal_building_id').value = buildingId;
        document.getElementById('modal_building_name').value = buildingName;
    }

    // ---------------- Google Maps Logic ----------------
    let map;
    let marker;

    function initMap() {
        // ตั้งค่าจุดเริ่มต้นของแผนที่ (มหาวิทยาลัย RMUTT)
        const initialLocation = { lat: 14.0367, lng: 100.7300 };

        map = new google.maps.Map(document.getElementById("mapPicker"), {
            center: initialLocation,
            zoom: 16, // ซูมเข้ามาให้เห็นตึกชัดๆ
            mapTypeControl: true,
            streetViewControl: false,
        });

        // เมื่อคลิกบนแผนที่ ให้สร้าง/ย้ายหมุด
        map.addListener("click", (mapsMouseEvent) => {
            placeMarker(mapsMouseEvent.latLng);
        });
    }

    function placeMarker(location) {
        // ถ้ามีหมุดอยู่แล้วให้ย้าย ถ้ายังไม่มีให้สร้างใหม่
        if (marker) {
            marker.setPosition(location);
        } else {
            marker = new google.maps.Marker({
                position: location,
                map: map,
                animation: google.maps.Animation.DROP
            });
        }
        // อัปเดตค่าลงในช่อง Input ให้แอดมินเห็น
        document.getElementById("latInput").value = location.lat().toFixed(6);
        document.getElementById("lngInput").value = location.lng().toFixed(6);
    }

    // แก้ไขปัญหาแผนที่โหลดไม่เต็มกรอบเมื่ออยู่ใน Bootstrap Modal
    document.getElementById('addBuildingModal').addEventListener('shown.bs.modal', function () {
        if (map) {
            google.maps.event.trigger(map, 'resize');
            // เลื่อนศูนย์กลางกลับมาที่เดิมหลังขยายกรอบ
            if (marker) {
                map.setCenter(marker.getPosition());
            } else {
                map.setCenter({ lat: 14.0367, lng: 100.7300 });
            }
        }
    });
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= $_ENV['GOOGLE_MAPS_API_KEY'] ?>&callback=initMap" async
    defer></script>
<?php include 'includes/footer.php'; ?>