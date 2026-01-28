<?php
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['db'];

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['name'],
    $dbConfig['charset']
);

$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
