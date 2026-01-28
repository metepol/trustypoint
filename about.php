<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
$page = getPage($pdo, 'about');
require __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <h1 class="fw-bold">About TrustyPoint</h1>
            <p class="text-muted"><?php echo e($page['excerpt'] ?? 'Our clinic blends science and artistry to deliver advanced hair restoration, medical aesthetics, and dental treatments.'); ?></p>
            <div class="lead"><?php echo nl2br(e($page['content'] ?? 'We focus on ethical care, personalized treatment planning, and transparent communication at every step.')); ?></div>
        </div>
        <div class="col-lg-6">
            <img class="img-fluid rounded shadow" src="https://images.unsplash.com/photo-1504813184591-01572f98c85f?auto=format&fit=crop&w=1200&q=80" alt="Medical team">
        </div>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
