<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
$page = getPage($pdo, 'contact');
require __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <h1 class="fw-bold">Contact Us</h1>
            <p class="text-muted"><?php echo e($page['excerpt'] ?? 'Let us answer your questions and arrange a personalized consultation.'); ?></p>
            <div><?php echo nl2br(e($page['content'] ?? "Phone: +90 212 000 00 00\nEmail: info@trustypoint.com\nAddress: Istanbul, Turkey")); ?></div>
        </div>
        <div class="col-lg-7">
            <div class="p-4 bg-light rounded">
                <h2 class="h5">Consultation Support</h2>
                <p class="text-muted">Share your details and we will return to you within 24 hours.</p>
                <a class="btn btn-primary" href="/consultation.php">Request Online Consultation</a>
            </div>
        </div>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
