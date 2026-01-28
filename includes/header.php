<?php
$config = require __DIR__ . '/../config.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($config['site']['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">TrustyPoint Clinic</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/treatments.php">Treatments</a></li>
                <li class="nav-item"><a class="nav-link" href="/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>
                <li class="nav-item"><a class="btn btn-primary ms-lg-2" href="/consultation.php">Online Consultation</a></li>
            </ul>
        </div>
    </div>
</nav>
