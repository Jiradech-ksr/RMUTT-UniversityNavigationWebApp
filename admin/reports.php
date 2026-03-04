<?php
include 'includes/header.php';

// จัดการอัปเดตสถานะเป็น "แก้ไขแล้ว"
if (isset($_GET['resolve_id'])) {
    $id = (int) $_GET['resolve_id'];
    $conn->query("UPDATE reports SET status = 'resolved' WHERE id = $id");
    echo "<script>window.location='reports.php';</script>";
}

// ดึงข้อมูลรายงานปัญหา พร้อมชื่อคนแจ้งและชื่อห้อง
$sql = "SELECT r.*, u.display_name, rm.name as room_name, rm.room_number, b.name as building_name 
        FROM reports r 
        JOIN users u ON r.user_id = u.id 
        LEFT JOIN rooms rm ON r.room_id = rm.id 
        LEFT JOIN buildings b ON rm.building_id = b.id
        ORDER BY r.status ASC, r.created_at DESC";
$reports = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-dark"><i class="fas fa-exclamation-triangle text-danger me-2"></i> ข้อเสนอแนะ / รายงานปัญหา</h3>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">วันที่แจ้ง</th>
                        <th>ผู้แจ้ง</th>
                        <th>สถานที่</th>
                        <th>ประเภทปัญหา</th>
                        <th>รายละเอียด</th>
                        <th>รูปภาพ</th>
                        <th>สถานะ</th>
                        <th class="text-end px-4">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports->num_rows > 0):
                        while ($rp = $reports->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4">
                                    <?= date("d/m/Y H:i", strtotime($rp['created_at'])); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($rp['display_name']); ?>
                                </td>
                                <td>
                                    <?php if ($rp['room_name']): ?>
                                        <span class="fw-bold text-primary">
                                            <?= htmlspecialchars($rp['room_name']); ?> (
                                            <?= htmlspecialchars($rp['room_number']); ?>)
                                        </span><br>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($rp['building_name']); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่ระบุ</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary">
                                        <?= htmlspecialchars($rp['issue_type']); ?>
                                    </span></td>
                                <td>
                                    <?= htmlspecialchars($rp['description']); ?>
                                </td>
                                <td>
                                    <?php if (!empty($rp['image_url'])): ?>
                                        <a href="<?= htmlspecialchars($rp['image_url']); ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($rp['image_url']); ?>" alt="evidence" width="50"
                                                height="50" class="rounded border" style="object-fit:cover;">
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($rp['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> รอดำเนินการ</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> แก้ไขแล้ว</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end px-4">
                                    <?php if ($rp['status'] == 'pending'): ?>
                                        <a href="?resolve_id=<?= $rp['id']; ?>" class="btn btn-sm btn-success"
                                            onclick="return confirm('ยืนยันว่าปัญหาถูกแก้ไขเรียบร้อยแล้ว?');">
                                            ทำเครื่องหมายว่าแก้ไขแล้ว
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>เสร็จสิ้น</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">ไม่มีรายการแจ้งปัญหา</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>