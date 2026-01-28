<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdmin();

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    $value = trim($value, '-');
    return $value ?: 'treatment';
}

$id = $_GET['id'] ?? null;
$treatment = [
    'title' => '',
    'category' => '',
    'summary' => '',
    'content' => '',
    'image_url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM treatments WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $treatment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');
    $slug = slugify($title);

    if ($id) {
        $stmt = $pdo->prepare('UPDATE treatments SET title = :title, slug = :slug, category = :category, summary = :summary, content = :content, image_url = :image_url WHERE id = :id');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'summary' => $summary,
            'content' => $content,
            'image_url' => $imageUrl,
            'id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO treatments (title, slug, category, summary, content, image_url) VALUES (:title, :slug, :category, :summary, :content, :image_url)');
        $stmt->execute([
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'summary' => $summary,
            'content' => $content,
            'image_url' => $imageUrl,
        ]);
        $id = $pdo->lastInsertId();
    }

    $success = true;
    $stmt = $pdo->prepare('SELECT * FROM treatments WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $treatment = $stmt->fetch(PDO::FETCH_ASSOC);
}

require __DIR__ . '/partials/header.php';
?>
<h1 class="h3 mb-4"><?php echo $id ? 'Edit Treatment' : 'Add Treatment'; ?></h1>
<?php if ($success): ?>
    <div class="alert alert-success">Treatment saved.</div>
<?php endif; ?>
<form method="post" class="card shadow-sm p-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo e($treatment['title'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" value="<?php echo e($treatment['category'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Summary</label>
            <textarea name="summary" rows="2" class="form-control"><?php echo e($treatment['summary'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Content</label>
            <textarea name="content" rows="5" class="form-control"><?php echo e($treatment['content'] ?? ''); ?></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Image URL</label>
            <input type="text" name="image_url" class="form-control" value="<?php echo e($treatment['image_url'] ?? ''); ?>">
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary" type="submit">Save Treatment</button>
        <a class="btn btn-outline-secondary" href="/admin/treatments.php">Back</a>
    </div>
</form>
<?php
require __DIR__ . '/partials/footer.php';
