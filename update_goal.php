<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('goals.php');
}

$userId = (int) $_SESSION['user_id'];
$goalId = (int) ($_POST['goal_id'] ?? 0);
$currentAmount = (float) ($_POST['current_amount'] ?? 0);

if ($goalId > 0 && $currentAmount >= 0) {
    $stmt = $pdo->prepare("
        UPDATE goals
        SET current_amount = :current_amount
        WHERE id = :id AND user_id = :user_id
    ");

    $stmt->execute([
        ':current_amount' => $currentAmount,
        ':id' => $goalId,
        ':user_id' => $userId
    ]);
}

redirect('goals.php');
