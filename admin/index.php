<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdmin();

$stmt = $pdo->query('SELECT COUNT(*) FROM treatments');
$treatmentCount = $stmt->fetchColumn();
$stmt = $pdo->query('SELECT COUNT(*) FROM consultations');
$consultationCount = $stmt->fetchColumn();

require __DIR__ . '/partials/header.php';
?>
<h1 class="h3 mb-4">Dashboard</h1>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Treatments</h2>
                <p class="display-6 mb-0"><?php echo e((string) $treatmentCount); ?></p>
                <a href="/admin/treatments.php" class="btn btn-link">Manage treatments</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Consultations</h2>
                <p class="display-6 mb-0"><?php echo e((string) $consultationCount); ?></p>
                <a href="/admin/consultations.php" class="btn btn-link">View requests</a>
            </div>
        </div>
    </div>
</div>
<?php
require __DIR__ . '/partials/footer.php';
