<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
$page = getPage($pdo, 'treatments');
$stmt = $pdo->query('SELECT * FROM treatments ORDER BY created_at DESC');
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);
require __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold">Treatments</h1>
            <p class="text-muted mb-0"><?php echo e($page['excerpt'] ?? 'Explore our signature treatments designed for transformative results.'); ?></p>
        </div>
        <a class="btn btn-primary" href="/consultation.php">Request Consultation</a>
    </div>
    <div class="mb-4">
        <?php echo nl2br(e($page['content'] ?? 'Every treatment is overseen by specialist physicians and tailored to your unique needs.')); ?>
    </div>
    <div class="row g-4">
        <?php foreach ($treatments as $treatment): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo e($treatment['image_url']); ?>" class="card-img-top" alt="<?php echo e($treatment['title']); ?>">
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?php echo e($treatment['category']); ?></span>
                        <h3 class="h5 card-title"><?php echo e($treatment['title']); ?></h3>
                        <p class="card-text text-muted"><?php echo e($treatment['summary']); ?></p>
                        <a class="btn btn-outline-primary" href="/treatment.php?slug=<?php echo e($treatment['slug']); ?>">View details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
