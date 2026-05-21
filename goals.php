<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $target = (float)($_POST['target_amount'] ?? 0);
    $current = (float)($_POST['current_amount'] ?? 0);

    if ($title !== '' && $target > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO goals(user_id, title, target_amount, current_amount)
            VALUES(:user_id, :title, :target, :current)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':target' => $target,
            ':current' => $current
        ]);
    }

    redirect('goals.php');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM goals
    WHERE user_id = :user_id
    ORDER BY id DESC
");

$stmt->execute([':user_id' => $userId]);
$goals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Metas Financeiras | AWZION Finance</title>
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
        <a class="nav-link" href="categories.php">Categorias</a>
        <a class="nav-link active" href="goals.php">Metas</a>
        <a class="nav-link" href="logout.php">Sair</a>
    </nav>
</aside>

<main class="main-content">

<div class="page-header">
    <div>
        <h1>Metas Financeiras</h1>
        <p>Acompanhe seus objetivos e progresso financeiro.</p>
    </div>
</div>

<div class="grid two-fixed">

<section class="panel">
    <h2>Nova Meta</h2>

    <form method="POST">
        <div class="field">
            <label>Título da meta</label>
            <input type="text" name="title" placeholder="Ex: Comprar notebook" required>
        </div>

        <div class="field">
            <label>Valor da meta</label>
            <input type="number" step="0.01" name="target_amount" placeholder="5000" required>
        </div>

        <div class="field">
            <label>Valor atual</label>
            <input type="number" step="0.01" name="current_amount" placeholder="0">
        </div>

        <button class="btn" type="submit">Salvar Meta</button>
    </form>
</section>

<section class="panel">
    <h2>Suas Metas</h2>

    <?php if (!$goals): ?>

        <div class="empty">Nenhuma meta cadastrada.</div>

    <?php else: ?>

        <?php foreach ($goals as $goal): ?>

            <?php
            $target = (float)$goal['target_amount'];
            $current = (float)$goal['current_amount'];
            $progress = $target > 0 ? ($current / $target) * 100 : 0;
            $progress = min($progress, 100);
            $missing = max($target - $current, 0);
            ?>

            <div class="goal-card">
    <div class="goal-header">
        <div>
            <h3><?= h($goal['title']) ?></h3>
            <p>Faltam <?= money(max((float)$goal['target_amount'] - (float)$goal['current_amount'], 0)) ?></p>
        </div>

        <span><?= number_format($progress, 0) ?>%</span>
    </div>

    <div style="width:100%; height:18px; background:#1f2937; border-radius:999px; overflow:hidden; margin:18px 0;">
    <div style="width:<?= $progress ?>%; height:18px; background:linear-gradient(90deg,#d4af37,#f1d57c); border-radius:999px;"></div>
</div>

    <div class="goal-values">
        <span>Atual: <?= money((float)$goal['current_amount']) ?></span>
        <span>Meta: <?= money((float)$goal['target_amount']) ?></span>
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
