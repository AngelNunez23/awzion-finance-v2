<?php
require_once 'config.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $target = (float) $_POST['target_amount'];

    if ($title !== '' && $target > 0) {

        $stmt = $pdo->prepare("
            INSERT INTO goals(user_id, title, target_amount)
            VALUES(:user_id, :title, :target)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':target' => $target
        ]);
    }
}

$stmt = $pdo->prepare("
    SELECT *
    FROM goals
    WHERE user_id = :user_id
    ORDER BY id DESC
");

$stmt->execute([
    ':user_id' => $userId
]);

$goals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Metas Financeiras</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app-shell">

<aside class="sidebar">
    <div>
        <h2>AWZION</h2>
        <p>Finance v2</p>
    </div>

    <nav>
        <a class="nav-link" href="dashboard.php">Dashboard</a>
        <a class="nav-link" href="transactions.php">Lançamentos</a>
        <a class="nav-link active" href="goals.php">Metas</a>
        <a class="nav-link" href="categories.php">Categorias</a>
        <a class="nav-link" href="logout.php">Sair</a>
    </nav>
</aside>

<main class="main-content">

<h1>Metas Financeiras</h1>

<div class="grid two-fixed">

<section class="panel">

<h2>Nova Meta</h2>

<form method="POST">

<div class="field">
<label>Título</label>
<input type="text" name="title" required>
</div>

<div class="field">
<label>Meta R$</label>
<input type="number" step="0.01" name="target_amount" required>
</div>

<button class="btn">Salvar Meta</button>

</form>

</section>

<section class="panel">

<h2>Suas Metas</h2>

<?php if (!$goals): ?>

<div class="empty">
Nenhuma meta cadastrada.
</div>

<?php else: ?>

<?php foreach ($goals as $goal): ?>

<?php
$progress = 0;

if ($goal['target_amount'] > 0) {
    $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
}

if ($progress > 100) {
    $progress = 100;
}
?>

<div class="goal-card">

<div class="goal-top">
<strong><?= h($goal['title']) ?></strong>

<span class="green">
<?= number_format($progress, 0) ?>%
</span>
</div>

<div class="goal-values">
Meta:
<?= money((float)$goal['target_amount']) ?>
</div>

<div class="progress-bar">
<div class="progress-fill" style="width: <?= $progress ?>%"></div>
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

</section>

</div>

</main>
</div>

</body>
</html>
