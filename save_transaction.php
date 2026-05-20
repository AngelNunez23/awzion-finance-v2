<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('transactions.php');
}

$userId = (int)$_SESSION['user_id'];
$type = ($_POST['type'] ?? '') === 'income' ? 'income' : 'expense';
$title = trim((string)($_POST['title'] ?? ''));
$categoryId = (int)($_POST['category_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$transactionDate = trim((string)($_POST['transaction_date'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));
$dateObj = DateTime::createFromFormat('Y-m-d', $transactionDate);

$categoryStmt = $pdo->prepare('SELECT id, type FROM categories WHERE id = :id AND user_id = :user_id LIMIT 1');
$categoryStmt->execute([':id' => $categoryId, ':user_id' => $userId]);
$category = $categoryStmt->fetch();

if ($title === '' || $amount <= 0 || !$dateObj || $dateObj->format('Y-m-d') !== $transactionDate || !$category) {
    redirect('transactions.php?error=1');
}

$stmt = $pdo->prepare('INSERT INTO transactions (user_id, category_id, type, title, amount, transaction_date, notes) VALUES (:user_id, :category_id, :type, :title, :amount, :transaction_date, :notes)');
$stmt->execute([
    ':user_id' => $userId,
    ':category_id' => $categoryId,
    ':type' => $type,
    ':title' => $title,
    ':amount' => number_format($amount, 2, '.', ''),
    ':transaction_date' => $transactionDate,
    ':notes' => $notes !== '' ? $notes : null,
]);

redirect('transactions.php?success=1');
