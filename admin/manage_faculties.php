<?php include 'includes/header.php'; ?>

<?php
// ==========================================
// Handle Faculty POST actions
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- FACULTY ---
    if ($action === 'add_faculty') {
        $name = trim($_POST['name']);
        if ($name) {
            $stmt = $conn->prepare("INSERT INTO faculties (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
        header("Location: manage_faculties.php?msg_ok=เพิ่มคณะสำเร็จ"); exit();

    } elseif ($action === 'edit_faculty') {
        $id   = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $stmt = $conn->prepare("UPDATE faculties SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        header("Location: manage_faculties.php?msg_ok=แก้ไขคณะสำเร็จ"); exit();

    } elseif ($action === 'delete_faculty') {
        $id = (int)$_POST['id'];
        // Check if any departments exist
        $check = $conn->query("SELECT COUNT(*) as cnt FROM departments WHERE faculty_id = $id")->fetch_assoc()['cnt'];
        if ($check > 0) {
            $_SESSION['alert'] = "<div class='alert alert-danger'>ไม่สามารถลบคณะได้ เนื่องจากยังมีภาควิชาอยู่ภายใน</div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM faculties WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        header("Location: manage_faculties.php"); exit();

    // --- DEPARTMENT ---
    } elseif ($action === 'add_dept') {
        $faculty_id = (int)$_POST['faculty_id'];
        $name       = trim($_POST['name']);
        if ($name && $faculty_id) {
            $stmt = $conn->prepare("INSERT INTO departments (name, faculty_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $faculty_id);
            $stmt->execute();
        }
        header("Location: manage_faculties.php?open_fac=$faculty_id&msg_ok=เพิ่มภาควิชาสำเร็จ"); exit();

    } elseif ($action === 'edit_dept') {
        $id         = (int)$_POST['id'];
        $faculty_id = (int)$_POST['faculty_id'];
        $name       = trim($_POST['name']);
        $stmt = $conn->prepare("UPDATE departments SET name = ?, faculty_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $faculty_id, $id);
        $stmt->execute();
        header("Location: manage_faculties.php?open_fac=$faculty_id&msg_ok=แก้ไขภาควิชาสำเร็จ"); exit();

    } elseif ($action === 'delete_dept') {
        $id         = (int)$_POST['id'];
        $faculty_id = (int)$_POST['faculty_id'];
        $check = $conn->query("SELECT COUNT(*) as cnt FROM buildings WHERE department_id = $id")->fetch_assoc()['cnt'];
        if ($check > 0) {
            $_SESSION['alert'] = "<div class='alert alert-danger'>ไม่สามารถลบภาควิชาได้ เนื่องจากยังมีอาคารอยู่ภายใน</div>";
        } else {
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        header("Location: manage_faculties.php?open_fac=$faculty_id"); exit();
    }
}

// Flash messages
$msg_ok = isset($_GET['msg_ok']) ? htmlspecialchars($_GET['msg_ok']) : '';
$open_fac = isset($_GET['open_fac']) ? (int)$_GET['open_fac'] : 0;
if (isset($_SESSION['alert'])) { $alert_html = $_SESSION['alert']; unset($_SESSION['alert']); }

// Fetch all faculties
$faculties = $conn->query("SELECT * FROM faculties ORDER BY name ASC");
// Fetch all faculties for dropdowns in dept edit modal
$fac_list = $conn->query("SELECT id, name FROM faculties ORDER BY name ASC");
$fac_dropdown = [];
while ($fr = $fac_list->fetch_assoc()) $fac_dropdown[] = $fr;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="text-dark mb-0">
        <i class="fas fa-university text-primary me-2"></i> จัดการคณะ / ภาควิชา
    </h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
        <i class="fas fa-plus me-1"></i> เพิ่มคณะใหม่
    </button>
</div>

<?php if ($msg_ok): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <i class="fas fa-check-circle me-2"></i><?= $msg_ok ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($alert_html)) echo $alert_html; ?>

<!-- =====================================================
     TREE: Faculty > Department  (old accordion style)
     ===================================================== -->
<div class="accordion shadow-sm" id="facultyAccordion">
<?php if ($faculties && $faculties->num_rows > 0):
    while ($f = $faculties->fetch_assoc()):
        $fid = (int)$f['id'];
        $depts = $conn->query("SELECT * FROM departments WHERE faculty_id = $fid ORDER BY name ASC");
        $dept_count = $depts->num_rows;
        $should_open = ($open_fac === $fid) ? '' : 'collapsed';
        $should_show = ($open_fac === $fid) ? 'show' : '';
?>
    <!-- FACULTY ROW -->
    <div class="accordion-item border-0 border-bottom mb-1 rounded">
        <h2 class="accordion-header" id="fac-h<?= $fid ?>">
            <button class="accordion-button <?= $should_open ?> fw-semibold text-indigo"
                    style="background-color:#f0f0f8;"
                    type="button" data-bs-toggle="collapse" data-bs-target="#fac-c<?= $fid ?>">
                <i class="fas fa-university me-2 text-primary"></i>
                <?= htmlspecialchars($f['name']) ?>
                <span class="badge bg-primary ms-3"><?= $dept_count ?> ภาควิชา</span>
            </button>
        </h2>
        <div id="fac-c<?= $fid ?>" class="accordion-collapse collapse <?= $should_show ?>"
             data-bs-parent="#facultyAccordion">
            <div class="accordion-body bg-white ps-4">

                <!-- Faculty action buttons -->
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-sm btn-success"
                            data-bs-toggle="modal" data-bs-target="#addDeptModal"
                            onclick="setAddDeptFaculty(<?= $fid ?>, '<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>')">
                        <i class="fas fa-plus me-1"></i> เพิ่มภาควิชาในคณะนี้
                    </button>
                    <button class="btn btn-sm btn-warning"
                            data-bs-toggle="modal" data-bs-target="#editFacultyModal"
                            onclick="setEditFaculty(<?= $fid ?>, '<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>')">
                        <i class="fas fa-edit me-1"></i> แก้ไขชื่อคณะ
                    </button>
                    <form method="POST" class="d-inline"
                          onsubmit="return confirm('ยืนยันลบคณะ \"<?= htmlspecialchars($f['name'], ENT_QUOTES) ?>\"?\n(ต้องลบภาควิชาทั้งหมดภายในก่อน)')">
                        <input type="hidden" name="action" value="delete_faculty">
                        <input type="hidden" name="id" value="<?= $fid ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash me-1"></i> ลบคณะ
                        </button>
                    </form>
                </div>

                <!-- Departments nested inside -->
                <?php if ($dept_count > 0): ?>
                <div class="accordion" id="dept-accordion-<?= $fid ?>">
                <?php while ($d = $depts->fetch_assoc()):
                    $did = (int)$d['id'];
                    $bldg_count = $conn->query("SELECT COUNT(*) as cnt FROM buildings WHERE department_id = $did")->fetch_assoc()['cnt'];
                ?>
                    <div class="accordion-item border-0 border-bottom rounded">
                        <h2 class="accordion-header" id="dept-h<?= $did ?>">
                            <button class="accordion-button collapsed fw-semibold text-indigo"
                                    style="background-color:#f8f9fc;"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#dept-c<?= $did ?>">
                                <i class="fas fa-sitemap me-2 text-secondary"></i>
                                <?= htmlspecialchars($d['name']) ?>
                                <span class="badge bg-secondary ms-3"><?= $bldg_count ?> อาคาร</span>
                            </button>
                        </h2>
                        <div id="dept-c<?= $did ?>" class="accordion-collapse collapse">
                            <div class="accordion-body ps-4">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal" data-bs-target="#editDeptModal"
                                            onclick="setEditDept(<?= $did ?>, '<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>', <?= $fid ?>)">
                                        <i class="fas fa-edit me-1"></i> แก้ไขภาควิชา
                                    </button>
                                    <form method="POST" class="d-inline"
                                          onsubmit="return confirm('ยืนยันลบภาควิชา \"<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>\"?\n(ต้องลบอาคารทั้งหมดภายในก่อน)')">
                                        <input type="hidden" name="action" value="delete_dept">
                                        <input type="hidden" name="id" value="<?= $did ?>">
                                        <input type="hidden" name="faculty_id" value="<?= $fid ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i> ลบภาควิชา
                                        </button>
                                    </form>
                                    <a href="manage_rooms.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-building me-1"></i> ดูอาคาร (<?= $bldg_count ?>)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-muted py-2">
                    <i class="fas fa-info-circle me-1"></i> ยังไม่มีภาควิชาในคณะนี้
                </p>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <!-- /FACULTY ROW -->

<?php endwhile; else: ?>
    <div class="alert alert-warning shadow-sm">
        <i class="fas fa-exclamation-triangle me-2"></i> ยังไม่มีข้อมูลคณะในระบบ
    </div>
<?php endif; ?>
</div><!-- /facultyAccordion -->


<!-- ==================== MODALS ==================== -->

<!-- Add Faculty Modal -->
<div class="modal fade" id="addFacultyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_faculty">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-university me-2 text-primary"></i>เพิ่มคณะใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">ชื่อคณะ</label>
                <input type="text" name="name" class="form-control"
                       placeholder="เช่น คณะวิศวกรรมศาสตร์" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Faculty Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="edit_faculty">
            <input type="hidden" name="id" id="edit_fac_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i>แก้ไขคณะ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">ชื่อคณะ</label>
                <input type="text" name="name" id="edit_fac_name" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_dept">
            <input type="hidden" name="faculty_id" id="add_dept_fac_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sitemap me-2 text-success"></i>เพิ่มภาควิชา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 p-2 bg-light rounded">
                    <small class="text-muted">คณะ: </small>
                    <span id="add_dept_fac_name" class="fw-semibold text-primary"></span>
                </div>
                <label class="form-label fw-semibold">ชื่อภาควิชา</label>
                <input type="text" name="name" class="form-control"
                       placeholder="เช่น ภาควิชาวิศวกรรมคอมพิวเตอร์" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="edit_dept">
            <input type="hidden" name="id" id="edit_dept_id">
            <input type="hidden" name="faculty_id" id="edit_dept_fac_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i>แก้ไขภาควิชา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">ชื่อภาควิชา</label>
                    <input type="text" name="name" id="edit_dept_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">สังกัดคณะ</label>
                    <select name="faculty_id" id="edit_dept_fac_select" class="form-select" required>
                        <?php foreach ($fac_dropdown as $fd): ?>
                        <option value="<?= $fd['id'] ?>"><?= htmlspecialchars($fd['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-warning">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script>
    function setEditFaculty(id, name) {
        document.getElementById('edit_fac_id').value = id;
        document.getElementById('edit_fac_name').value = name;
    }
    function setAddDeptFaculty(facId, facName) {
        document.getElementById('add_dept_fac_id').value = facId;
        document.getElementById('add_dept_fac_name').textContent = facName;
    }
    function setEditDept(id, name, facId) {
        document.getElementById('edit_dept_id').value = id;
        document.getElementById('edit_dept_name').value = name;
        document.getElementById('edit_dept_fac_id').value = facId;
        document.getElementById('edit_dept_fac_select').value = facId;
    }
</script>

<?php include 'includes/footer.php'; ?>
