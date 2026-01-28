<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$categories = [
    'Medikal Estetik',
    'Saç Tedavileri',
    'Diş Tedavileri',
];

$selectedTreatment = null;
$treatmentId = $_GET['treatment_id'] ?? $_POST['treatment_id'] ?? null;
if ($treatmentId) {
    $stmt = $pdo->prepare('SELECT id, title FROM treatments WHERE id = :id');
    $stmt->execute(['id' => $treatmentId]);
    $selectedTreatment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$success = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($category === '') {
        $errors[] = 'Please select a treatment category.';
    }
    if ($name === '') {
        $errors[] = 'Please add your name.';
    }
    if ($email === '') {
        $errors[] = 'Please add your email.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO consultations (category, treatment_id, name, email, phone, message) VALUES (:category, :treatment_id, :name, :email, :phone, :message)');
        $stmt->execute([
            'category' => $category,
            'treatment_id' => $selectedTreatment['id'] ?? null,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
        ]);
        $success = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
    <div class="row g-4">
        <div class="col-lg-6">
            <h1 class="fw-bold">Online Consultation</h1>
            <p class="text-muted">Fill in the form and our medical team will respond within 24 hours.</p>
            <?php if ($selectedTreatment): ?>
                <div class="alert alert-info">You are requesting consultation for: <strong><?php echo e($selectedTreatment['title']); ?></strong></div>
            <?php endif; ?>
            <img class="img-fluid rounded shadow" src="https://images.unsplash.com/photo-1580281658629-04b9f1a2b7c1?auto=format&fit=crop&w=1200&q=80" alt="Consultation">
        </div>
        <div class="col-lg-6">
            <?php if ($success): ?>
                <div class="alert alert-success">Thank you! Your request has been submitted.</div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" class="p-4 bg-light rounded">
                <input type="hidden" name="treatment_id" value="<?php echo e($selectedTreatment['id'] ?? ''); ?>">
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $categoryOption): ?>
                            <option value="<?php echo e($categoryOption); ?>" <?php echo ($categoryOption === ($_POST['category'] ?? '')) ? 'selected' : ''; ?>><?php echo e($categoryOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo e($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" rows="4" class="form-control"><?php echo e($_POST['message'] ?? ''); ?></textarea>
                </div>
                <button class="btn btn-primary w-100" type="submit">Send Request</button>
            </form>
        </div>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
