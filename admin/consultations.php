<?php
session_start();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdmin();

$stmt = $pdo->query('SELECT consultations.*, treatments.title AS treatment_title FROM consultations LEFT JOIN treatments ON consultations.treatment_id = treatments.id ORDER BY consultations.created_at DESC');
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/partials/header.php';
?>
<h1 class="h3 mb-4">Consultation Requests</h1>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Treatment</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Received</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $consultation): ?>
                    <tr>
                        <td><?php echo e($consultation['category']); ?></td>
                        <td><?php echo e($consultation['treatment_title'] ?? 'General'); ?></td>
                        <td><?php echo e($consultation['name']); ?></td>
                        <td><?php echo e($consultation['email']); ?></td>
                        <td><?php echo e($consultation['phone']); ?></td>
                        <td><?php echo e($consultation['message']); ?></td>
                        <td><?php echo e($consultation['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
require __DIR__ . '/partials/footer.php';
