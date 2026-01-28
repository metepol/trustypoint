<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdmin();

$pagesConfig = [
    'about' => 'About',
    'contact' => 'Contact',
    'treatments' => 'Treatments',
];

foreach ($pagesConfig as $slug => $title) {
    $stmt = $pdo->prepare('SELECT id FROM pages WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $insert = $pdo->prepare('INSERT INTO pages (slug, title, excerpt, content) VALUES (:slug, :title, :excerpt, :content)');
        $insert->execute([
            'slug' => $slug,
            'title' => $title,
            'excerpt' => '',
            'content' => '',
        ]);
    }
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = $_POST['slug'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (array_key_exists($slug, $pagesConfig)) {
        $stmt = $pdo->prepare('UPDATE pages SET excerpt = :excerpt, content = :content WHERE slug = :slug');
        $stmt->execute([
            'excerpt' => $excerpt,
            'content' => $content,
            'slug' => $slug,
        ]);
        $success = true;
    }
}

$stmt = $pdo->query('SELECT * FROM pages ORDER BY title');
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/partials/header.php';
?>
<h1 class="h3 mb-4">Editable Pages</h1>
<?php if ($success): ?>
    <div class="alert alert-success">Page updated.</div>
<?php endif; ?>
<div class="accordion" id="pagesAccordion">
    <?php foreach ($pages as $page): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?php echo e($page['slug']); ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo e($page['slug']); ?>">
                    <?php echo e($page['title']); ?>
                </button>
            </h2>
            <div id="collapse-<?php echo e($page['slug']); ?>" class="accordion-collapse collapse" data-bs-parent="#pagesAccordion">
                <div class="accordion-body">
                    <form method="post">
                        <input type="hidden" name="slug" value="<?php echo e($page['slug']); ?>">
                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="2"><?php echo e($page['excerpt']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="4"><?php echo e($page['content']); ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php
require __DIR__ . '/partials/footer.php';
