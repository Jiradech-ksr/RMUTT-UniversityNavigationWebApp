<?php
include 'includes/header.php';
require_once '../api/upload_helper.php';
$apiKey = $_ENV['GOOGLE_MAPS_API_KEY'];
// ==========================================
// 1. จัดการเพิ่มข้อมูล (Add Data)
// ==========================================
$dept_query = $conn->query("
    SELECT d.id, d.name_en as dept_name_en, d.name_th as dept_name_th, f.name_en as faculty_name_en, f.name_th as faculty_name_th
    FROM departments d
    LEFT JOIN faculties f ON f.id = d.faculty_id
    ORDER BY f.name_en ASC, d.name_en ASC
");
$departments_list = [];
while ($row = $dept_query->fetch_assoc()) {
    $departments_list[] = $row;
}
$fac_query = $conn->query("SELECT * FROM faculties ORDER BY name_en ASC");
$faculties_list = [];
while ($row = $fac_query->fetch_assoc()) {
    $faculties_list[] = $row;
}

if (isset($_POST['add_building'])) {
    $location_assign = $_POST['location_assign'] ?? '';
    $department_id = null;
    $faculty_id = null;
    if (str_starts_with($location_assign, 'F_')) {
        $faculty_id = (int)substr($location_assign, 2);
    } elseif (str_starts_with($location_assign, 'D_')) {
        $department_id = (int)substr($location_assign, 2);
        $d_lookup = $conn->query("SELECT faculty_id FROM departments WHERE id = $department_id");
        if ($d_lookup && $d_lookup->num_rows > 0) $faculty_id = (int)$d_lookup->fetch_assoc()['faculty_id'];
    }
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $image_url = null;
    $upload_error = "";

    if (isset($_FILES['building_image']) && $_FILES['building_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $image_url = uploadFileSafely($_FILES['building_image'], "buildings", "../");
        if ($image_url === null) {
            $upload_error = " (แต่รูปภาพอัปโหลดไม่สำเร็จ กรุณาตรวจสอบขนาดหรือนามสกุลไฟล์)";
        }
    }

    $name_en = $_POST['building_name_en'];
    $name_th = $_POST['building_name_th'];
    $details = trim($_POST['building_details'] ?? '') ?: null;
    $responsible_email = trim($_POST['responsible_email'] ?? '');
    $responsible_email = $responsible_email ?: null;

    $stmt = $conn->prepare("INSERT INTO buildings (name_en, name_th, department_id, faculty_id, latitude, longitude, image_url, responsible_email, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiddsss", $name_en, $name_th, $department_id, $faculty_id, $lat, $lng, $image_url, $responsible_email, $details);

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
    $room_name_en = $_POST['room_name_en'] ?? '';
    $room_name_th = $_POST['room_name_th'] ?? '';
    $floor = $_POST['floor'];
    $details = trim($_POST['room_details'] ?? '') ?: null;
    $floor_layout_url = uploadFileSafely($_FILES['room_layout'], "layouts", "../");

    // Insert room (image_url kept NULL; images stored in room_images table)
    $stmt = $conn->prepare("INSERT INTO rooms (building_id, room_number, name_en, name_th, floor, details, floor_layout_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $building_id, $room_number, $room_name_en, $room_name_th, $floor, $details, $floor_layout_url);

    if ($stmt->execute()) {
        $new_room_id = $conn->insert_id;
        $img_errors = [];
        $img_count = 0;

        // Handle up to 4 images from room_images[] file array
        if (!empty($_FILES['room_images']['name'][0])) {
            $files = $_FILES['room_images'];
            $total = min(count($files['name']), 4);
            for ($i = 0; $i < $total; $i++) {
                $single = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $result = uploadRoomImageValidated($single, "images", "../");
                if ($result === null)
                    continue;
                if (str_starts_with($result, 'ERR:')) {
                    $img_errors[] = substr($result, 4);
                } else {
                    $si = $conn->prepare("INSERT INTO room_images (room_id, image_url, sort_order) VALUES (?, ?, ?)");
                    $si->bind_param("isi", $new_room_id, $result, $img_count);
                    $si->execute();
                    $img_count++;
                }
            }
        }

        $warn = $img_errors ? ' <br><small class="text-danger">⚠️ ' . implode(', ', $img_errors) . '</small>' : '';
        $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> เพิ่มห้องเรียนเรียบร้อยแล้ว ($img_count รูปภาพ)$warn</div>";
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
    $name_th = $_POST['building_name_th'] ?? '';
    $location_assign = $_POST['location_assign'] ?? '';
    $department_id = null;
    $faculty_id = null;
    if (str_starts_with($location_assign, 'F_')) {
        $faculty_id = (int)substr($location_assign, 2);
    } elseif (str_starts_with($location_assign, 'D_')) {
        $department_id = (int)substr($location_assign, 2);
        $d_lookup = $conn->query("SELECT faculty_id FROM departments WHERE id = $department_id");
        if ($d_lookup && $d_lookup->num_rows > 0) $faculty_id = (int)$d_lookup->fetch_assoc()['faculty_id'];
    }
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $details = trim($_POST['building_details'] ?? '') ?: null;
    $responsible_email = trim($_POST['responsible_email'] ?? '');
    $responsible_email = $responsible_email ?: null;

    $stmt = $conn->prepare("UPDATE buildings SET name_en=?, name_th=?, department_id=?, faculty_id=?, latitude=?, longitude=?, responsible_email=?, details=? WHERE id=?");
    $stmt->bind_param("ssiiddssi", $name_en, $name_th, $department_id, $faculty_id, $lat, $lng, $responsible_email, $details, $id);

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
    $details = trim($_POST['room_details'] ?? '') ?: null;

    $stmt = $conn->prepare("UPDATE rooms SET room_number=?, name_en=?, name_th=?, floor=?, details=? WHERE id=?");
    $stmt->bind_param("sssssi", $room_number, $room_name_en, $room_name_th, $floor, $details, $id);

    if ($stmt->execute()) {
        // Layout image
        if (isset($_FILES['room_layout']) && $_FILES['room_layout']['error'] == UPLOAD_ERR_OK) {
            $floor_layout_url = uploadFileSafely($_FILES['room_layout'], "layouts", "../");
            if ($floor_layout_url) {
                $conn->prepare("UPDATE rooms SET floor_layout_url=? WHERE id=?")
                    ->bind_param("si", $floor_layout_url, $id) || true;
                $sl = $conn->prepare("UPDATE rooms SET floor_layout_url=? WHERE id=?");
                $sl->bind_param("si", $floor_layout_url, $id);
                $sl->execute();
            }
        }

        // New room images (appended to existing)
        $img_errors = [];
        $img_count = 0;
        if (!empty($_FILES['room_images']['name'][0])) {
            // Count how many already exist for sort_order
            $existing = $conn->query("SELECT COUNT(*) as c FROM room_images WHERE room_id = $id")->fetch_assoc()['c'];
            $files = $_FILES['room_images'];
            $total = min(count($files['name']), 4);
            for ($i = 0; $i < $total; $i++) {
                // Enforce total cap of 4
                if ($existing + $img_count >= 4)
                    break;
                $single = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $result = uploadRoomImageValidated($single, "images", "../");
                if ($result === null)
                    continue;
                if (str_starts_with($result, 'ERR:')) {
                    $img_errors[] = substr($result, 4);
                } else {
                    $order = $existing + $img_count;
                    $si = $conn->prepare("INSERT INTO room_images (room_id, image_url, sort_order) VALUES (?, ?, ?)");
                    $si->bind_param("isi", $id, $result, $order);
                    $si->execute();
                    $img_count++;
                }
            }
        }

        // Delete individual images if requested
        if (!empty($_POST['delete_image_ids'])) {
            foreach ((array) $_POST['delete_image_ids'] as $del_id) {
                $del_id = (int) $del_id;
                $conn->query("DELETE FROM room_images WHERE id = $del_id AND room_id = $id");
            }
        }

        $warn = $img_errors ? ' <br><small class="text-danger">⚠️ ' . implode(', ', $img_errors) . '</small>' : '';
        $_SESSION['alert'] = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> แก้ไขข้อมูลห้องเรียนเรียบร้อยแล้ว$warn</div>";
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

// Fetch all faculties for the tree
$faculties_tree = $conn->query("SELECT * FROM faculties ORDER BY name_en ASC"); ?>

<style>
    .accordion-button:focus, .form-control:focus { box-shadow: none !important; }
    #searchInput:focus { border-color: #dee2e6 !important; }
</style>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="text-dark mb-0"><i class="fas fa-sitemap text-primary me-2"></i> จัดการอาคารและห้องเรียน (ตามโครงสร้าง)</h3>
    <div class="d-flex gap-2">
        <div class="input-group" style="width: 320px;">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="ค้นหาคณะ, ภาควิชา, อาคาร, ห้อง...">
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
            <i class="fas fa-plus"></i> เพิ่มอาคารใหม่
        </button>
    </div>
</div>

<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
if (isset($alert))
    echo $alert;
?>

<!-- TREE VIEW: Faculty > Department > Building > Room (old accordion style) -->
<div class="accordion shadow-sm" id="facultyAccordion">
    <?php if ($faculties_tree && $faculties_tree->num_rows > 0):
        while ($faculty = $faculties_tree->fetch_assoc()):
            $fid = (int) $faculty['id'];
            $faculty_name_safe = htmlspecialchars($faculty['name_th'] . ' / ' . $faculty['name_en']);
            $total_bldg = $conn->query("
            SELECT COUNT(id) as cnt FROM buildings 
            WHERE faculty_id = $fid
        ")->fetch_assoc()['cnt'];
            ?>
            <!-- LEVEL 1: FACULTY -->
            <div class="accordion-item border-0 border-bottom mb-1 rounded">
                <h2 class="accordion-header" id="fac-h<?= $fid ?>">
                    <button class="accordion-button collapsed fw-semibold text-indigo" style="background-color:#f0f0f8;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#fac-c<?= $fid ?>">
                        <i class="fas fa-university me-2 text-primary"></i>
                        <?= $faculty_name_safe ?>
                        <span class="badge bg-primary ms-3"><?= $total_bldg ?> อาคาร</span>
                    </button>
                </h2>
                <div id="fac-c<?= $fid ?>" class="accordion-collapse collapse" data-bs-parent="#facultyAccordion">
                    <div class="accordion-body ps-4 pt-2 pb-2 bg-white border-start border-3 border-primary ms-3 rounded-bottom">

                        <?php
                        $depts = $conn->query("SELECT * FROM departments WHERE faculty_id = $fid ORDER BY name_en ASC");
                        if ($depts && $depts->num_rows > 0):
                            while ($dept = $depts->fetch_assoc()):
                                $did = (int) $dept['id'];
                                $dept_name_safe = htmlspecialchars($dept['name_th'] . ' / ' . $dept['name_en']);
                                $dept_bldgs_count = $conn->query("SELECT COUNT(*) as cnt FROM buildings WHERE department_id = $did")->fetch_assoc()['cnt'];
                                ?>
                                <!-- LEVEL 2: DEPARTMENT -->
                                <div class="accordion mb-1" id="dept-ac<?= $did ?>">
                                    <div class="accordion-item border-0 border-bottom rounded">
                                        <h2 class="accordion-header" id="dept-h<?= $did ?>">
                                            <button class="accordion-button collapsed fw-semibold text-indigo"
                                                style="background-color:#f8f9fc;" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#dept-c<?= $did ?>">
                                                <i class="fas fa-sitemap me-2 text-secondary"></i>
                                                <?= $dept_name_safe ?>
                                                <span class="badge bg-secondary ms-3"><?= $dept_bldgs_count ?> อาคาร</span>
                                                <button class="btn btn-sm btn-outline-primary ms-auto py-0 px-2"
                                                    style="font-size:0.7rem;" data-bs-toggle="modal" data-bs-target="#addBuildingModal"
                                                    onclick="event.stopPropagation(); preSelectDept(<?= $did ?>)">
                                                    <i class="fas fa-plus"></i> เพิ่มอาคาร
                                                </button>
                                            </button>
                                        </h2>
                                        <div id="dept-c<?= $did ?>" class="accordion-collapse collapse">
                                            <div class="accordion-body ps-4 pt-2 pb-1 bg-white border-start border-3 border-secondary ms-3 rounded-bottom">

                                                <?php
                                                $buildings = $conn->query("SELECT * FROM buildings WHERE department_id = $did ORDER BY name_en ASC");
                                                if ($buildings && $buildings->num_rows > 0):
                                                    while ($b = $buildings->fetch_assoc()):
                                                        $b_id = (int) $b['id'];
                                                        $b_name = htmlspecialchars($b['name_en'], ENT_QUOTES);
                                                        $b_lat = $b['latitude'] ?? '';
                                                        $b_lng = $b['longitude'] ?? '';
                                                        $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
                                                        ?>
                                                        <!-- LEVEL 3: BUILDING -->
                                                        <div class="accordion mb-1" id="bldg-ac<?= $b_id ?>">
                                                            <div class="accordion-item border-0 border-bottom rounded">
                                                                <h2 class="accordion-header" id="bldg-h<?= $b_id ?>">
                                                                    <button class="accordion-button collapsed fw-semi bold text-indigo"
                                                                        style="background-color:#f8f9fc;" type="button"
                                                                        data-bs-toggle="collapse" data-bs-target="#bldg-c<?= $b_id ?>">
                                                                        <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                                                        <?= htmlspecialchars($b['name_en']) ?>
                                                                        <span class="badge bg-secondary ms-3"><?= $rooms->num_rows ?>
                                                                            ห้อง</span>
                                                                    </button>
                                                                </h2>
                                                                <div id="bldg-c<?= $b_id ?>" class="accordion-collapse collapse">
                                                                    <div class="accordion-body ps-4 bg-white border-start border-3 border-danger ms-3 rounded-bottom">
                                                                        <div class="d-flex justify-content-end mb-3">
                                                                            <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal"
                                                                                data-bs-target="#addRoomModal"
                                                                                onclick="setRoomModalData(<?= $b_id ?>, '<?= $b_name ?>')">
                                                                                <i class="fas fa-plus"></i> เพิ่มห้อง
                                                                            </button>
                                                                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal"
                                                                                data-bs-target="#editBuildingModal"
                                                                                onclick="editBuildingData(<?= $b_id ?>, '<?= htmlspecialchars($b['name_en'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['name_th'] ?? '', ENT_QUOTES) ?>', '<?= $b_lat ?>', '<?= $b_lng ?>', 'D_<?= $did ?>', '<?= htmlspecialchars($b['responsible_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($b['details'] ?? '', ENT_QUOTES) ?>')">
                                                                                <i class="fas fa-edit"></i> แก้ไขพิกัด/ข้อมูล
                                                                            </button>
                                                                            <a href="?delete_building=<?= $b_id ?>"
                                                                                class="btn btn-sm btn-outline-danger"
                                                                                onclick="return confirm('ยืนยันการลบอาคารนี้ (ห้องทั้งหมดในอาคารจะถูกลบด้วย)?');">
                                                                                <i class="fas fa-trash"></i> ลบอาคาร
                                                                            </a>
                                                                        </div>

                                                                        <?php if ($rooms->num_rows > 0): ?>
                                                                            <table
                                                                                class="table table-sm table-hover mt-2 border rounded overflow-hidden">
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
                                                                                            <td class="px-3 fw-semi bold text-primary">
                                                                                                <?= htmlspecialchars($r['room_number']) ?></td>
                                                                                            <td>
                                                                                                <?= htmlspecialchars($r['name_en'] ?? '') ?>
                                                                                                <?php if (!empty($r['image_url']) || !empty($r['floor_layout_url'])): ?>
                                                                                                    <span class="badge bg-info ms-2"><i
                                                                                                            class="fas fa-image"></i> มีรูปภาพ</span>
                                                                                                <?php endif; ?>
                                                                                            </td>
                                                                                            <td>ชั้น <?= htmlspecialchars($r['floor']) ?></td>
                                                                                            <td class="text-end px-3">
                                                                                                <button class="btn btn-sm btn-warning py-0 me-1"
                                                                                                    data-bs-toggle="modal"
                                                                                                    data-bs-target="#editRoomModal"
                                                                                                    onclick="editRoomData(<?= $r['id'] ?>, '<?= htmlspecialchars($r['room_number'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['name_en'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['name_th'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['floor'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['details'] ?? '', ENT_QUOTES) ?>')">
                                                                                                    <i class="fas fa-edit"></i> แก้ไข
                                                                                                </button>
                                                                                                <a href="?delete_room=<?= $r['id'] ?>"
                                                                                                    class="btn btn-sm btn-danger py-0"
                                                                                                    onclick="return confirm('ยืนยันการลบห้อง <?= htmlspecialchars($r['room_number']) ?>?');">
                                                                                                    <i class="fas fa-trash-alt"></i> ลบ
                                                                                                </a>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php endwhile; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        <?php else: ?>
                                                                            <p class="text-muted text-center my-3 py-3 bg-light rounded">
                                                                                <i class="fas fa-info-circle me-2"></i> ยังไม่มีข้อมูลห้องในอาคารนี้
                                                                            </p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- /LEVEL 3: BUILDING -->

                                                    <?php endwhile; else: ?>
                                                    <p class="text-muted text-center py-3"><i class="fas fa-info-circle me-1"></i>
                                                        ยังไม่มีอาคารในภาควิชานี้</p>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /LEVEL 2: DEPARTMENT -->

                            <?php endwhile; else: ?>
                            <p class="text-muted text-center py-3">ยังไม่มีภาควิชาในคณะนี้</p>
                        <?php endif; ?>

                        <?php
                        $direct_bldgs = $conn->query("SELECT * FROM buildings WHERE faculty_id = $fid AND department_id IS NULL ORDER BY name_en ASC");
                        if ($direct_bldgs && $direct_bldgs->num_rows > 0):
                        ?>
                            <div class="mt-3 mb-2 border-bottom pb-1">
                                <strong class="text-success"><i class="fas fa-building"></i> อาคารสังกัดคณะโดยตรง (<?= $direct_bldgs->num_rows ?>)</strong>
                            </div>
                        <?php
                            while ($b = $direct_bldgs->fetch_assoc()):
                                $b_id = (int) $b['id'];
                                $b_name = htmlspecialchars($b['name_en'], ENT_QUOTES);
                                $b_lat = $b['latitude'] ?? '';
                                $b_lng = $b['longitude'] ?? '';
                                $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
                        ?>
                        <div class="accordion mb-1" id="bldg-ac-direct-<?= $b_id ?>">
                            <div class="accordion-item border-0 border-bottom rounded">
                                <h2 class="accordion-header" id="bldg-h-direct-<?= $b_id ?>">
                                    <button class="accordion-button collapsed fw-semi bold text-indigo"
                                        style="background-color:#e8f5e9;" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#bldg-c-direct-<?= $b_id ?>">
                                        <i class="fas fa-map-marker-alt me-2 text-success"></i>
                                        <?= htmlspecialchars($b['name_en']) ?>
                                        <span class="badge bg-secondary ms-3"><?= $rooms->num_rows ?> ห้อง</span>
                                    </button>
                                </h2>
                                <div id="bldg-c-direct-<?= $b_id ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body ps-4 bg-white border-start border-3 border-success ms-3 rounded-bottom">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal"
                                                data-bs-target="#addRoomModal"
                                                onclick="setRoomModalData(<?= $b_id ?>, '<?= $b_name ?>')">
                                                <i class="fas fa-plus"></i> เพิ่มห้อง
                                            </button>
                                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal"
                                                data-bs-target="#editBuildingModal"
                                                onclick="editBuildingData(<?= $b_id ?>, '<?= htmlspecialchars($b['name_en'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['name_th'] ?? '', ENT_QUOTES) ?>', '<?= $b_lat ?>', '<?= $b_lng ?>', 'F_<?= $fid ?>', '<?= htmlspecialchars($b['responsible_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($b['details'] ?? '', ENT_QUOTES) ?>')">
                                                <i class="fas fa-edit"></i> แก้ไขข้อมูล
                                            </button>
                                            <a href="?delete_building=<?= $b_id ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('ยืนยันลบอาคาร?');">
                                                <i class="fas fa-trash"></i> ลบอาคาร
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; endif; ?>

                    </div>
                </div>
            </div>
            <!-- /LEVEL 1: FACULTY -->

        <?php endwhile; else: ?>
        <div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i> ยังไม่มีข้อมูลคณะในระบบ
        </div>
    <?php endif; ?>
</div>

<!-- Buildings not yet assigned to a department or faculty -->
<?php
$unassigned = $conn->query("SELECT * FROM buildings WHERE department_id IS NULL AND faculty_id IS NULL ORDER BY name_en ASC");
if ($unassigned && $unassigned->num_rows > 0): ?>
    <div class="alert alert-warning shadow-sm mt-3 mb-1">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>อาคารที่ยังไม่ระบุสังกัด (<?= $unassigned->num_rows ?> อาคาร)</strong> — คลิกแก้ไขเพื่อกำหนดสังกัด
    </div>
    <div class="accordion shadow-sm mb-3" id="unassignedAccordion">
        <?php while ($b = $unassigned->fetch_assoc()):
            $b_id = (int) $b['id'];
            $b_name = htmlspecialchars($b['name_en'], ENT_QUOTES);
            $b_lat = $b['latitude'] ?? '';
            $b_lng = $b['longitude'] ?? '';
            $rooms = $conn->query("SELECT * FROM rooms WHERE building_id = $b_id ORDER BY floor ASC, room_number ASC");
            ?>
            <div class="accordion-item border-0 border-bottom mb-1 rounded">
                <h2 class="accordion-header" id="u-h<?= $b_id ?>">
                    <button class="accordion-button collapsed fw-semi bold text-indigo" style="background-color:#fff8e1;"
                        type="button" data-bs-toggle="collapse" data-bs-target="#u-c<?= $b_id ?>">
                        <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                        <?= htmlspecialchars($b['name_en']) ?>
                        <span class="badge bg-warning text-dark ms-3"><?= $rooms->num_rows ?> ห้อง</span>
                    </button>
                </h2>
                <div id="u-c<?= $b_id ?>" class="accordion-collapse collapse" data-bs-parent="#unassignedAccordion">
                    <div class="accordion-body bg-white">
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#addRoomModal"
                                onclick="setRoomModalData(<?= $b_id ?>, '<?= $b_name ?>')">
                                <i class="fas fa-plus"></i> เพิ่มห้อง
                            </button>
                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal"
                                                data-bs-target="#editBuildingModal"
                                                onclick="editBuildingData(<?= $b_id ?>, '<?= htmlspecialchars($b['name_en'], ENT_QUOTES) ?>', '<?= htmlspecialchars($b['name_th'] ?? '', ENT_QUOTES) ?>', '<?= $b_lat ?>', '<?= $b_lng ?>', '', '<?= htmlspecialchars($b['responsible_email'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($b['details'] ?? '', ENT_QUOTES) ?>')">
                                                <i class="fas fa-edit"></i> กำหนดสังกัด
                            </button>
                            <a href="?delete_building=<?= $b_id ?>" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('ยืนยันการลบอาคารนี้?');">
                                <i class="fas fa-trash"></i> ลบ
                            </a>
                        </div>
                        <?php if ($rooms->num_rows > 0): ?>
                            <table class="table table-sm table-hover mt-2 border rounded overflow-hidden">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3">เลขห้อง</th>
                                        <th>ชื่อห้อง</th>
                                        <th>ชั้น</th>
                                        <th class="text-end px-3">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $rooms->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-3 fw-semi bold text-primary"><?= htmlspecialchars($r['room_number']) ?></td>
                                            <td><?= htmlspecialchars($r['name_en'] ?? '') ?></td>
                                            <td>ชั้น <?= htmlspecialchars($r['floor']) ?></td>
                                            <td class="text-end px-3">
                                                <button class="btn btn-sm btn-warning py-0 me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editRoomModal"
                                                    onclick="editRoomData(<?= $r['id'] ?>, '<?= htmlspecialchars($r['room_number'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['name_en'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['name_th'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['floor'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-edit"></i> แก้ไข
                                                </button>
                                                <a href="?delete_room=<?= $r['id'] ?>" class="btn btn-sm btn-danger py-0"
                                                    onclick="return confirm('ยืนยันการลบห้อง <?= htmlspecialchars($r['room_number']) ?>?');">
                                                    <i class="fas fa-trash-alt"></i> ลบ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted text-center my-3 py-3 bg-light rounded">
                                <i class="fas fa-info-circle me-2"></i> ยังไม่มีข้อมูลห้องในอาคารนี้
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>




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
                        <label class="form-label fw-bold mt-2">รายละเอียด (Details)</label>
                        <textarea name="building_details" class="form-control" rows="2"
                            placeholder="เช่น อาคารศูนย์ฝึกปฏิบัติการ"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ภาควิชา / สำนัก (Department) <span
                                class="text-danger">*</span></label>
                        <select name="location_assign" class="form-select" required>
                            <option value="">-- เลือกภาควิชาหรือสังกัด --</option>
                            <optgroup label="ระดับคณะ (สังกัดคณะโดยตรง)">
                                <?php foreach ($faculties_list as $fac): ?>
                                    <option value="F_<?= $fac['id']; ?>">
                                        <?= htmlspecialchars($fac['name_th'] . ' / ' . $fac['name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="ระดับภาควิชา (สังกัดย่อย)">
                                <?php foreach ($departments_list as $dept): ?>
                                    <option value="D_<?= $dept['id']; ?>">
                                        <?= htmlspecialchars($dept['faculty_name_th'] . ' › ' . $dept['dept_name_th']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-bold text-primary"><i class="fas fa-image me-1"></i> รูปภาพอาคาร
                            (Building Image)</label>
                        <input type="file" name="building_image" class="form-control"
                            accept="image/jpeg, image/png, image/jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-envelope me-1 text-secondary"></i> E-mail
                            ผู้รับผิดชอบห้อง</label>
                        <input type="email" name="responsible_email" class="form-control"
                            placeholder="เช่น engineer@rmutt.ac.th">
                        <small class="text-muted">แสดงในแอปผู้ใช้งานสำหรับทุกห้องในอาคารนี้</small>
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

                        <label class="form-label fw-bold mt-2">รายละเอียดการใช้งาน (Details)</label>
                        <textarea name="room_details" class="form-control" rows="2"
                            placeholder="เช่น ห้องปฏิบัติการสำหรับนักศึกษาชั้นปีที่ 2"></textarea>
                    </div>

                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-bold text-primary">
                            <i class="fas fa-images me-1"></i> รูปภาพสถานที่จริง (สูงสุด 4 รูป)
                        </label>
                        <input type="file" name="room_images[]" id="addRoomImages" class="form-control mb-1"
                            accept="image/jpeg,image/png" multiple>
                        <small class="text-muted">JPG/PNG เท่านั้น · ไม่เกิน 5MB ต่อรูป · สูงสุด 4 รูป</small>
                        <div id="addImgPreview" class="d-flex flex-wrap gap-2 mt-2"></div>

                        <label class="form-label fw-bold text-success mt-3">
                            <i class="fas fa-map me-1"></i> รูปแผนผังห้อง (Room Layout)
                        </label>
                        <input type="file" name="room_layout" class="form-control" accept="image/jpeg,image/png">
                        <small class="text-muted">JPG/PNG เท่านั้น · ไม่เกิน 5MB</small>
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
                        <input type="text" name="building_name_en" id="edit_building_name_en" class="form-control"
                            required>
                        <label class="form-label fw-bold mt-2">ชื่ออาคาร (ภาษาไทย)</label>
                        <input type="text" name="building_name_th" id="edit_building_name_th" class="form-control">
                        <label class="form-label fw-bold mt-2">รายละเอียด (Details)</label>
                        <textarea name="building_details" id="edit_building_details" class="form-control" rows="2"
                            placeholder="เช่น อาคารศูนย์ฝึกปฏิบัติการ"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ภาควิชา / สำนัก (Department) <span
                                class="text-danger">*</span></label>
                        <select name="location_assign" id="edit_department_id" class="form-select" required>
                            <option value="">-- เลือกภาควิชาหรือสังกัด --</option>
                            <optgroup label="ระดับคณะ (สังกัดคณะโดยตรง)">
                                <?php foreach ($faculties_list as $fac): ?>
                                    <option value="F_<?= $fac['id']; ?>">
                                        <?= htmlspecialchars($fac['name_th'] . ' / ' . $fac['name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="ระดับภาควิชา (สังกัดย่อย)">
                                <?php foreach ($departments_list as $dept): ?>
                                    <option value="D_<?= $dept['id']; ?>">
                                        <?= htmlspecialchars($dept['faculty_name_th'] . ' › ' . $dept['dept_name_th']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3 border rounded p-3 bg-light">
                        <label class="form-label fw-semi bold text-primary"><i class="fas fa-image me-1"></i>
                            เปลี่ยนรูปภาพอาคาร</label>
                        <input type="file" name="building_image" class="form-control mb-1"
                            accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted d-block">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-envelope me-1 text-secondary"></i> E-mail
                            ผู้รับผิดชอบห้อง</label>
                        <input type="email" name="responsible_email" id="edit_building_email" class="form-control"
                            placeholder="เช่น engineer@rmutt.ac.th">
                        <small class="text-muted">ปล่อยว่างหากไม่มี</small>
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

                        <label class="form-label fw-bold mt-2">รายละเอียดการใช้งาน (Details)</label>
                        <textarea name="room_details" id="edit_room_details" class="form-control" rows="2"
                            placeholder="เช่น ห้องปฏิบัติการสำหรับนักศึกษาชั้นปีที่ 2"></textarea>
                    </div>

                    <div class="mb-3 border rounded p-3 bg-light">
                        <!-- Existing images -->
                        <label class="form-label fw-semibold text-primary">
                            <i class="fas fa-images me-1"></i> รูปภาพปัจจุบัน
                        </label>
                        <div id="editExistingImages" class="d-flex flex-wrap gap-2 mb-2"></div>

                        <!-- Add new images -->
                        <label class="form-label fw-semibold text-primary mt-2">
                            <i class="fas fa-plus-circle me-1"></i> เพิ่มรูปภาพใหม่ (รวมไม่เกิน 4 รูป)
                        </label>
                        <input type="file" name="room_images[]" id="editRoomImages" class="form-control mb-1"
                            accept="image/jpeg,image/png" multiple>
                        <small class="text-muted">JPG/PNG เท่านั้น · ไม่เกิน 5MB ต่อรูป</small>
                        <div id="editImgPreview" class="d-flex flex-wrap gap-2 mt-2"></div>

                        <!-- Layout -->
                        <label class="form-label fw-semibold text-success mt-3">
                            <i class="fas fa-map me-1"></i> เปลี่ยนรูปแผนผังห้อง
                        </label>
                        <input type="file" name="room_layout" class="form-control mb-1" accept="image/jpeg,image/png">
                        <small class="text-muted">JPG/PNG เท่านั้น · ไม่เกิน 5MB · ปล่อยว่างหากไม่ต้องการเปลี่ยน</small>
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
    // --- Pre-select department in Add Building modal ---
    function preSelectDept(deptId) {
        var sel = document.querySelector('#addBuildingModal select[name="department_id"]');
        if (sel) sel.value = deptId;
    }

    // --- การโยนข้อมูลเข้า Modal เพิ่มห้อง ---
    function setRoomModalData(buildingId, buildingName) {
        document.getElementById('add_modal_building_id').value = buildingId;
        document.getElementById('add_modal_building_name').value = buildingName;
    }

    // --- การโยนข้อมูลเข้า Modal แก้ไขห้อง ---
    function editRoomData(id, number, name_en, name_th, floor, details) {
        document.getElementById('edit_room_id').value = id;
        document.getElementById('edit_room_number').value = number;
        document.getElementById('edit_room_name_en').value = name_en;
        document.getElementById('edit_room_name_th').value = name_th;
        document.getElementById('edit_room_floor').value = floor;
        document.getElementById('edit_room_details').value = details ?? '';

        // Load existing images via AJAX
        var container = document.getElementById('editExistingImages');
        container.innerHTML = '<span class="text-muted small">กำลังโหลด...</span>';
        fetch('get_room_images.php?room_id=' + id)
            .then(r => r.json())
            .then(imgs => {
                container.innerHTML = '';
                if (!imgs.length) {
                    container.innerHTML = '<span class="text-muted small">ยังไม่มีรูปภาพ</span>';
                    return;
                }
                imgs.forEach(img => {
                    var wrap = document.createElement('div');
                    wrap.className = 'position-relative';
                    wrap.innerHTML = `
                        <img src="${img.image_url}" style="height:70px;width:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                        <label class="position-absolute top-0 end-0 m-1" title="ลบรูปนี้">
                            <input type="checkbox" name="delete_image_ids[]" value="${img.id}" class="form-check-input bg-danger border-danger">
                        </label>`;
                    container.appendChild(wrap);
                });
                var hint = document.createElement('p');
                hint.className = 'text-muted small mt-1 w-100';
                hint.textContent = 'เลือก ✓ ที่รูปเพื่อลบออก';
                container.appendChild(hint);
            })
            .catch(() => { container.innerHTML = '<span class="text-muted small text-danger">โหลดรูปไม่สำเร็จ</span>'; });
    }

    // ---- Image preview for Add Modal ----
    document.getElementById('addRoomImages').addEventListener('change', function () {
        buildPreview(this.files, 'addImgPreview', 4);
    });
    // ---- Image preview for Edit Modal ----
    document.getElementById('editRoomImages').addEventListener('change', function () {
        buildPreview(this.files, 'editImgPreview', 4);
    });

    function buildPreview(files, containerId, maxFiles) {
        var container = document.getElementById(containerId);
        container.innerHTML = '';
        var allowed = ['image/jpeg', 'image/png'];
        var maxSize = 5 * 1024 * 1024;
        var count = Math.min(files.length, maxFiles);
        for (var i = 0; i < count; i++) {
            var f = files[i];
            var wrap = document.createElement('div');
            wrap.className = 'position-relative';
            if (!allowed.includes(f.type)) {
                wrap.innerHTML = `<div class="bg-danger text-white rounded p-1" style="font-size:0.7rem;max-width:80px;">${f.name}<br>❌ ไม่ใช่ JPG/PNG</div>`;
            } else if (f.size > maxSize) {
                wrap.innerHTML = `<div class="bg-warning text-dark rounded p-1" style="font-size:0.7rem;max-width:80px;">${f.name}<br>❌ เกิน 5MB</div>`;
            } else {
                var img = document.createElement('img');
                img.style = 'height:70px;width:70px;object-fit:cover;border-radius:6px;border:1px solid #ccc;';
                var reader = new FileReader();
                reader.onload = (function (image) { return function (e) { image.src = e.target.result; }; })(img);
                reader.readAsDataURL(f);
                wrap.appendChild(img);
            }
            container.appendChild(wrap);
        }
        if (files.length > maxFiles) {
            var warn = document.createElement('p');
            warn.className = 'text-danger small mt-1 w-100';
            warn.textContent = '⚠️ อัปโหลดได้สูงสุด ' + maxFiles + ' รูป ระบบจะใช้เฉพาะ ' + maxFiles + ' รูปแรก';
            container.appendChild(warn);
        }
    }


    // --- การโยนข้อมูลเข้า Modal แก้ไขอาคาร ---
    function editBuildingData(id, name_en, name_th, lat, lng, dept_id, email, details) {
        document.getElementById('edit_building_id').value = id;
        document.getElementById('edit_building_name_en').value = name_en;
        document.getElementById('edit_building_name_th').value = name_th;
        document.getElementById('edit_latInput').value = lat;
        document.getElementById('edit_lngInput').value = lng;
        document.getElementById('edit_building_email').value = email ?? '';
        document.getElementById('edit_building_details').value = details ?? '';
        if (dept_id) document.getElementById('edit_department_id').value = dept_id;

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function() {
        const term = this.value.toLowerCase().trim();
        const allAccordionItems = document.querySelectorAll('.accordion-item');

        if (term === '') {
            allAccordionItems.forEach(item => {
                item.style.display = '';
                const rows = item.querySelectorAll('.accordion-collapse > .accordion-body > table tbody tr');
                rows.forEach(r => r.style.display = '');
            });
            return;
        }

        allAccordionItems.forEach(item => item.style.display = 'none');

        allAccordionItems.forEach(item => {
            let headerText = '';
            const header = item.querySelector('.accordion-header');
            if (header) headerText = header.textContent.toLowerCase();

            const rows = item.querySelectorAll('.accordion-collapse > .accordion-body > table tbody tr');
            let anyRowMatched = false;
            if (rows.length > 0) {
                rows.forEach(row => {
                    if (row.textContent.toLowerCase().includes(term)) {
                        row.style.display = '';
                        anyRowMatched = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (headerText.includes(term) || anyRowMatched) {
                item.style.display = '';
                const collapse = item.querySelector('.accordion-collapse');
                if (collapse) collapse.classList.add('show');

                let parent = item.parentElement.closest('.accordion-item');
                while (parent) {
                    parent.style.display = '';
                    const pCollapse = parent.querySelector('.accordion-collapse');
                    if (pCollapse) pCollapse.classList.add('show');
                    parent = parent.parentElement.closest('.accordion-item');
                }
            }
        });
    });
});
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&callback=initMap" async defer></script>

<?php include 'includes/footer.php'; ?>