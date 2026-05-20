<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('categories.php');
}

$userId = (int)$_SESSION['user_id'];
$name = trim((string)($_POST['name'] ?? ''));
$type = ($_POST['type'] ?? '') === 'income' ? 'income' : 'expense';

if ($name === '') {
    redirect('categories.php?error=1');
}

$stmt = $pdo->prepare('INSERT INTO categories (user_id, name, type) VALUES (:user_id, :name, :type)');
$stmt->execute([
    ':user_id' => $userId,
    ':name' => $name,
    ':type' => $type,
]);

redirect('categories.php?success=1');
