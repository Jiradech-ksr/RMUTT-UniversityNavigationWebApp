<?php
// htdocs/admin/dashboard.php
include 'includes/header.php'; // <--- LOADS NAV & CSS
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2><i class="fas fa-tachometer-alt text-warning"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, Admin. What would you like to manage today?</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fas fa-building fa-3x text-primary mb-3"></i>
                <h3>Buildings</h3>
                <p class="text-muted">Add or edit campus buildings.</p>
                <a href="add_building.php" class="btn btn-primary mt-2">Manage Buildings</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fas fa-door-open fa-3x text-warning mb-3"></i>
                <h3>Rooms</h3>
                <p class="text-muted">Upload photos and floor layouts.</p>
                <a href="add_room.php" class="btn btn-gold mt-2">Manage Rooms</a>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php'; // <--- CLOSES HTML
?>