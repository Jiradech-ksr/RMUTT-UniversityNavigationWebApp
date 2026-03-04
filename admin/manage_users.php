<?php
include 'includes/header.php';

// --- 1. HANDLE FORM SUBMISSIONS ---
// Add New Staff/Technician
if (isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $role = $_POST['role'];
    $name = $_POST['name'];

    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $alert = "<div class='alert alert-danger'>อีเมลนี้มีอยู่ในระบบแล้ว (Email already exists)</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (display_name, email, role, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $name, $email, $role);
        if ($stmt->execute()) {
            $alert = "<div class='alert alert-success'>เพิ่มผู้ใช้งานเรียบร้อยแล้ว (User added successfully)</div>";
        }
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
    echo "<script>window.location='manage_users.php';</script>";
}

// Remove User
if (isset($_GET['delete_id'])) {
    $id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $id");
    echo "<script>window.location='manage_users.php';</script>";
}

// --- 2. FETCH USERS SEPARATELY ---

// Query 1: Users only (เปลี่ยนจากคำว่า student เป็นความหมายของ user ทั่วไป)
$user_query = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");

// Query 2: Staff, Technicians, and Admins
$staff_query = $conn->query("SELECT * FROM users WHERE role IN ('admin', 'staff', 'technician') ORDER BY FIELD(role, 'admin', 'staff', 'technician'), created_at DESC");
?>

<style>
    .table-container {
        height: 500px;
        overflow-y: auto;
        position: relative;
        border: 1px solid #dee2e6;
        border-top: none;
        /* Blends with tabs */
        background-color: #fff;
    }

    .table-container thead th {
        position: sticky;
        top: 0;
        background-color: #1A237E;
        color: white;
        z-index: 2;
    }

    .nav-tabs .nav-link.active {
        font-weight: bold;
        color: #1A237E;
        border-bottom-color: transparent;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark"><i class="fas fa-users-cog text-primary me-2"></i> จัดการบัญชีผู้ใช้งาน (Manage Users)</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-shield"></i> เพิ่มเจ้าหน้าที่/แอดมิน
    </button>
</div>

<?php if (isset($alert))
    echo $alert; ?>

<ul class="nav nav-tabs" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-pane" type="button"
            role="tab">
            <i class="fas fa-users me-1"></i> ผู้ใช้งานแอป (App Users)
            <span class="badge bg-success ms-1"><?= $user_query->num_rows; ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-pane" type="button"
            role="tab">
            <i class="fas fa-user-tie me-1"></i> เจ้าหน้าที่ระบบ (Staff & Admin)
            <span class="badge bg-primary ms-1"><?= $staff_query->num_rows; ?></span>
        </button>
    </li>
</ul>

<div class="tab-content shadow-sm" id="userTabContent">

    <div class="tab-pane fade show active" id="user-pane" role="tabpanel" tabindex="0">
        <div class="table-container">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">ข้อมูลผู้ใช้ (User Info)</th>
                        <th>วันที่เข้าร่วม (Joined Date)</th>
                        <th>สถานะ (Status)</th>
                        <th class="text-end px-4">การจัดการ (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($user_query->num_rows > 0):
                        while ($u = $user_query->fetch_assoc()): ?>
                            <tr class="<?= ($u['status'] == 'banned') ? 'table-secondary text-muted' : '' ?>">
                                <td class="px-4 py-2">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($u['photo_url'])): ?>
                                            <img src="<?= htmlspecialchars($u['photo_url']); ?>" class="rounded-circle me-3 border"
                                                width="40" height="40" style="object-fit:cover;"
                                                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['display_name']) ?>&background=1A237E&color=fff&rounded=true';">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-3"
                                                style="width:40px;height:40px;"><i class="fas fa-user"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semi bold"><?= htmlspecialchars($u['display_name']); ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date("d M Y", strtotime($u['created_at'])); ?></td>
                                <td>
                                    <?= ($u['status'] == 'active') ? '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>' : '<span class="badge bg-secondary"><i class="fas fa-ban"></i> Banned</span>'; ?>
                                </td>
                                <td class="text-end px-4">
                                    <a href="?ban_id=<?= $u['id']; ?>&status=<?= $u['status']; ?>"
                                        class="btn btn-sm <?= ($u['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?> me-1"
                                        title="Ban/Unban">
                                        <i class="fas <?= ($u['status'] == 'active') ? 'fa-ban' : 'fa-undo'; ?>"></i>
                                    </a>
                                    <a href="?delete_id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('ลบผู้ใช้คนนี้ออกจากระบบ?');"><i
                                            class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูลผู้ใช้งาน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-pane fade" id="staff-pane" role="tabpanel" tabindex="0">
        <div class="table-container">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3">ข้อมูลเจ้าหน้าที่ (Staff Info)</th>
                        <th>บทบาท (Role)</th>
                        <th>สถานะ (Status)</th>
                        <th class="text-end px-4">การจัดการ (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($staff_query->num_rows > 0):
                        while ($u = $staff_query->fetch_assoc()): ?>
                            <tr class="<?= ($u['status'] == 'banned') ? 'table-secondary text-muted' : '' ?>">
                                <td class="px-4 py-2">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($u['photo_url'])): ?>
                                            <img src="<?= htmlspecialchars($u['photo_url']); ?>" class="rounded-circle me-3 border"
                                                width="40" height="40" style="object-fit:cover;"
                                                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['display_name']) ?>&background=1A237E&color=fff&rounded=true';">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-3"
                                                style="width:40px;height:40px;"><i class="fas fa-user"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semi bold"><?= htmlspecialchars($u['display_name']); ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($u['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $badge_color = 'bg-secondary';
                                    if ($u['role'] == 'admin')
                                        $badge_color = 'bg-danger';
                                    if ($u['role'] == 'staff')
                                        $badge_color = 'bg-primary';
                                    if ($u['role'] == 'technician')
                                        $badge_color = 'bg-info text-dark';
                                    ?>
                                    <span
                                        class="badge <?= $badge_color; ?> rounded-pill px-3"><?= strtoupper($u['role']); ?></span>
                                </td>
                                <td>
                                    <?= ($u['status'] == 'active') ? '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>' : '<span class="badge bg-secondary"><i class="fas fa-ban"></i> Banned</span>'; ?>
                                </td>
                                <td class="text-end px-4">
                                    <?php if ($u['role'] != 'admin'): ?>
                                        <a href="?ban_id=<?= $u['id']; ?>&status=<?= $u['status']; ?>"
                                            class="btn btn-sm <?= ($u['status'] == 'active') ? 'btn-warning' : 'btn-success'; ?> me-1"
                                            title="<?= ($u['status'] == 'active') ? 'ระงับสิทธิ์' : 'คืนสิทธิ์'; ?>">
                                            <i class="fas <?= ($u['status'] == 'active') ? 'fa-ban' : 'fa-undo'; ?>"></i>
                                        </a>
                                        <a href="?delete_id=<?= $u['id']; ?>" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('ลบบัญชีนี้ถาวร?');"><i class="fas fa-trash-alt"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="fas fa-shield-alt"></i> Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">ไม่มีข้อมูลเจ้าหน้าที่</td>
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
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> เพิ่มเจ้าหน้าที่/แอดมิน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล (Display Name)</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">อีเมล (Google Email)</label>
                        <input type="email" name="email" class="form-control" required>
                        <div class="form-text text-danger">*เฉพาะบัญชีเจ้าหน้าที่เท่านั้น</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สิทธิ์การใช้งาน (Role)</label>
                        <select name="role" class="form-select">
                            <option value="staff">Staff (เจ้าหน้าที่ทั่วไป)</option>
                            <option value="technician">Technician (ช่างเทคนิค)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบสูงสุด)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="add_user" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // เช็คว่ามีค่าการเลือกแท็บเดิมเซฟไว้ไหม
        let activeTab = localStorage.getItem('activeUserTab');
        if (activeTab) {
            let tabElement = document.querySelector('button[data-bs-target="' + activeTab + '"]');
            if (tabElement) {
                let tabInstance = new bootstrap.Tab(tabElement);
                tabInstance.show();
            }
        }

        // เมื่อมีการเปลี่ยนแท็บ ให้เซฟค่า ID ของแท็บนั้นไว้
        let tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabButtons.forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function (event) {
                let targetPane = event.target.getAttribute('data-bs-target');
                localStorage.setItem('activeUserTab', targetPane);
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>