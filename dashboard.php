<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$selectedMonth = $_GET['month'] ?? date('Y-m');

if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

$startDate = $selectedMonth . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

$summaryStmt = $pdo->prepare(" 
    SELECT
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
        COUNT(*) AS total_items
    FROM transactions
    WHERE user_id = :user_id AND transaction_date BETWEEN :start AND :end
");

$summaryStmt->execute([
    ':user_id' => $userId,
    ':start' => $startDate,
    ':end' => $endDate,
]);

$summary = $summaryStmt->fetch();
$totalIncome = (float)$summary['total_income'];
$totalExpense = (float)$summary['total_expense'];
$balance = $totalIncome - $totalExpense;
$totalItems = (int)$summary['total_items'];

$transactionsStmt = $pdo->prepare(" 
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = :user_id AND t.transaction_date BETWEEN :start AND :end
    ORDER BY t.transaction_date DESC, t.id DESC
");

$transactionsStmt->execute([
    ':user_id' => $userId,
    ':start' => $startDate,
    ':end' => $endDate,
]);

$transactions = $transactionsStmt->fetchAll();

$categoryStmt = $pdo->prepare(" 
    SELECT c.name, SUM(t.amount) AS total
    FROM transactions t
    INNER JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = :user_id 
    AND t.type = 'expense' 
    AND t.transaction_date BETWEEN :start AND :end
    GROUP BY c.id, c.name
    ORDER BY total DESC
");

$categoryStmt->execute([
    ':user_id' => $userId,
    ':start' => $startDate,
    ':end' => $endDate,
]);

$expenseByCategory = $categoryStmt->fetchAll();

$monthStatus = 'Equilibrado';

if ($balance > 0) {
    $monthStatus = 'Positivo';
} elseif ($balance < 0) {
    $monthStatus = 'Negativo';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AWZION Finance</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>

<body>
<div class="app-shell">

    <aside class="sidebar">
        <div>
            <h2>AWZION</h2>
            <p>Finance v2</p>
        </div>

        <nav>
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
            <a class="nav-link" href="transactions.php">Lançamentos</a>
            <a class="nav-link" href="categories.php">Categorias</a>
            <a class="nav-link" href="logout.php">Sair</a>
        </nav>
    </aside>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>Olá, <?= h($_SESSION['user_name']) ?></h1>
                <p>Resumo financeiro de <?= date('m/Y', strtotime($startDate)) ?></p>
            </div>

            <form method="GET" class="month-inline">
                <input type="month" name="month" value="<?= h($selectedMonth) ?>">
                <button class="btn" type="submit">Atualizar</button>
            </form>
        </div>

        <div class="cards">
            <div class="card">
                <div class="label">Ganhos</div>
                <div class="value green"><?= money($totalIncome) ?></div>
            </div>

            <div class="card">
                <div class="label">Gastos</div>
                <div class="value red"><?= money($totalExpense) ?></div>
            </div>

            <div class="card">
                <div class="label">Saldo</div>
                <div class="value gold"><?= money($balance) ?></div>
            </div>

            <div class="card">
                <div class="label">Lançamentos</div>
                <div class="value"><?= $totalItems ?></div>
            </div>
        </div>

        <div class="grid two">

            <section class="panel">
                <div class="panel-title-row">
                    <h2>Resumo gráfico</h2>
                </div>

                <?php if ($totalIncome == 0 && $totalExpense == 0): ?>
                    <div class="empty">Sem dados para gerar gráfico neste mês.</div>
                <?php else: ?>
                    <canvas id="financeChart" style="max-height: 320px;"></canvas>
                <?php endif; ?>

                <div class="category-item" style="margin-top: 18px;">
                    <span>Status do mês</span>
                    <strong><?= h($monthStatus) ?></strong>
                </div>
            </section>

            <section class="panel">
                <div class="panel-title-row">
                    <h2>Últimos lançamentos</h2>

                    <div style="display:flex; gap:10px;">
                        <a class="btn small-btn" href="transactions.php">
                            Novo lançamento
                        </a>

                        <a class="btn small-btn" href="export_pdf.php">
                            Exportar PDF
                        </a>
                    </div>
                </div>

                <?php if (!$transactions): ?>

                    <div class="empty">Nenhum lançamento neste mês.</div>

                <?php else: ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Valor</th>
                                <th>Data</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach (array_slice($transactions, 0, 8) as $item): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= h($item['type']) ?>">
                                            <?= $item['type'] === 'income' ? 'Ganho' : 'Gasto' ?>
                                        </span>
                                    </td>

                                    <td><?= h($item['title']) ?></td>

                                    <td><?= h($item['category_name'] ?? 'Sem categoria') ?></td>

                                    <td><?= money((float)$item['amount']) ?></td>

                                    <td><?= date('d/m/Y', strtotime($item['transaction_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
            </section>

        </div>

        <div class="grid two" style="margin-top: 20px;">

            <section class="panel">
                <h2>Gastos por categoria</h2>

                <?php if (!$expenseByCategory): ?>

                    <div class="empty">Nenhum gasto categorizado neste mês.</div>

                <?php else: ?>

                    <?php foreach ($expenseByCategory as $cat): ?>
                        <div class="category-item">
                            <span><?= h($cat['name']) ?></span>
                            <strong><?= money((float)$cat['total']) ?></strong>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Visão rápida</h2>

                <div class="category-item">
                    <span>Mês selecionado</span>
                    <strong><?= date('m/Y', strtotime($startDate)) ?></strong>
                </div>

                <div class="category-item">
                    <span>Total de ganhos</span>
                    <strong class="green"><?= money($totalIncome) ?></strong>
                </div>

                <div class="category-item">
                    <span>Total de gastos</span>
                    <strong class="red"><?= money($totalExpense) ?></strong>
                </div>

                <div class="category-item">
                    <span>Resultado final</span>
                    <strong class="<?= $balance >= 0 ? 'green' : 'red' ?>">
                        <?= money($balance) ?>
                    </strong>
                </div>
            </section>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartCanvas = document.getElementById('financeChart');

if (chartCanvas) {
    new Chart(chartCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Ganhos', 'Gastos'],
            datasets: [{
                data: [<?= $totalIncome ?>, <?= $totalExpense ?>],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#f5f5f7'
                    }
                }
            }
        }
    });
}
</script>

</body>
</html>
