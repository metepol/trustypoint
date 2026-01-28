<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM treatments WHERE slug = :slug');
$stmt->execute(['slug' => $slug]);
$treatment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$treatment) {
    header('HTTP/1.1 404 Not Found');
    echo 'Treatment not found.';
    exit;
}

require __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <img class="img-fluid rounded shadow" src="<?php echo e($treatment['image_url']); ?>" alt="<?php echo e($treatment['title']); ?>">
        </div>
        <div class="col-lg-6">
            <span class="badge bg-secondary mb-2"><?php echo e($treatment['category']); ?></span>
            <h1 class="fw-bold"><?php echo e($treatment['title']); ?></h1>
            <p class="text-muted"><?php echo e($treatment['summary']); ?></p>
            <div class="mb-4"><?php echo nl2br(e($treatment['content'])); ?></div>
            <a class="btn btn-primary" href="/consultation.php?treatment_id=<?php echo e($treatment['id']); ?>">Request Online Consultation</a>
        </div>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
