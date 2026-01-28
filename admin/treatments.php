<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare('DELETE FROM treatments WHERE id = :id');
    $stmt->execute(['id' => $deleteId]);
}

$stmt = $pdo->query('SELECT * FROM treatments ORDER BY created_at DESC');
$treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/partials/header.php';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">Treatments</h1>
    <a class="btn btn-primary" href="/admin/treatment-edit.php">Add Treatment</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($treatments as $treatment): ?>
                    <tr>
                        <td><?php echo e($treatment['title']); ?></td>
                        <td><?php echo e($treatment['category']); ?></td>
                        <td><?php echo e($treatment['created_at']); ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="/admin/treatment-edit.php?id=<?php echo e($treatment['id']); ?>">Edit</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this treatment?');">
                                <input type="hidden" name="delete_id" value="<?php echo e($treatment['id']); ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require __DIR__ . '/partials/footer.php';
