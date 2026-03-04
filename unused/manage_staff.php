<?php
include 'includes/header.php';

// --- 1. HANDLE FORM SUBMISSIONS ---
if (isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $role = $_POST['role'];
    $name = $_POST['name'];

    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $alert = "<div class='alert alert-danger'>อีเมลนี้มีอยู่ในระบบแล้ว</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (display_name, email, role, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $name, $email, $role);
        $stmt->execute();
        $alert = "<div class='alert alert-success'>เพิ่มเจ้าหน้าที่เรียบร้อยแล้ว</div>";
    }
}

// Ban / Unban User
if (isset($_GET['ban_id'])) {
    $id = (int) $_GET['ban_id'];
    $current_status = $_GET['status'];
    $new_status = ($current_status == 'active') ? 'banned' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    echo "<script>window.location='manage_staff.php';</script>";
}

// Remove User
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    echo "<script>window.location='manage_staff.php';</script>";
}

// --- 2. FETCH STAFF/ADMINS ---
$users = $conn->query("SELECT * FROM users WHERE role IN ('admin', 'staff', 'technician') ORDER BY FIELD(role, 'admin', 'staff', 'technician'), created_at DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark"><i class="fas fa-user-tie text-primary me-2"></i> จัดการเจ้าหน้าที่และผู้ดูแลระบบ</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus"></i> เพิ่มเจ้าหน้าที่
    </button>
</div>

<?php if (isset($alert))
    echo $alert; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="text-white" style="background-color: #1A237E;">
                    <tr>
                        <th class="px-4 py-3">ข้อมูลเจ้าหน้าที่</th>
                        <th>บทบาท (Role)</th>
                        <th>สถานะ</th>
                        <th class="text-end px-4">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0):
                        while ($u = $users->fetch_assoc()): ?>
                            <tr class="<?= ($u['status'] == 'banned') ? 'table-secondary text-muted' : '' ?>">
                                <td class="px-4 py-2">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($u['photo_url'])): ?>
                                            <img src="<?= htmlspecialchars($u['photo_url']); ?>"
                                                class="rounded-circle me-3 shadow-sm" width="40" height="40"
                                                style="object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-3 shadow-sm"
                                                style="width:40px;height:40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($u['display_name']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($u['email']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    if ($u['role'] == 'admin')
                                        echo '<span class="badge bg-danger rounded-pill">Admin (ผู้ดูแลระบบ)</span>';
                                    if ($u['role'] == 'staff')
                                        echo '<span class="badge bg-primary rounded-pill">Staff (เจ้าหน้าที่)</span>';
                                    if ($u['role'] == 'technician')
                                        echo '<span class="badge bg-info text-dark rounded-pill">Technician (ช่างเทคนิค)</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($u['status'] == 'active'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> ใช้งานปกติ</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="fas fa-ban"></i> ถูกระงับ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-4">
                                    <?php if ($u['role'] != 'admin'): // ซ่อนปุ่มลบ/แบน ถ้าคนนั้นเป็น Admin ป้องกันการแบนตัวเอง ?>
                                        <a href="?ban_id=<?= $u['id']; ?>&status=<?= $u['status']; ?>"
                                            class="btn btn-sm <?= ($u['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?> me-1">
                                            <i class="fas <?= ($u['status'] == 'active') ? 'fa-ban' : 'fa-undo'; ?>"></i>
                                        </a>
                                        <a href="?delete_id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('ต้องการลบเจ้าหน้าที่ท่านนี้?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Super User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">ยังไม่มีข้อมูลเจ้าหน้าที่</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #1A237E;">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> เพิ่มบุคลากรเข้าระบบ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อ-นามสกุล</label>
                        <input type="text" name="name" class="form-control" required placeholder="เช่น สมชาย ใจดี">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">อีเมล (Google Account)</label>
                        <input type="email" name="email" class="form-control" required
                            placeholder="เช่น admin@rmutt.ac.th">
                        <small class="text-muted">เจ้าหน้าที่จะใช้ Gmail นี้ในการ Login เข้าใช้งานระบบ</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">สิทธิ์การใช้งาน (Role)</label>
                        <select name="role" class="form-select">
                            <option value="staff">Staff (เจ้าหน้าที่ทั่วไป)</option>
                            <option value="technician">Technician (ช่างเทคนิค/ซ่อมบำรุง)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบระดับสูงสุด)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="add_user" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>