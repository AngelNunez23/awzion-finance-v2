<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// 🔥 NOVO: filtro por mês
$selectedMonth = $_GET['month'] ?? date('Y-m');
$startDate = $selectedMonth . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

// 🔥 CATEGORIAS
$categoriesStmt = $pdo->prepare('SELECT id, name, type FROM categories WHERE user_id = :user_id ORDER BY type, name');
$categoriesStmt->execute([':user_id' => $userId]);
$categories = $categoriesStmt->fetchAll();

// 🔥 TRANSAÇÕES FILTRADAS
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
    ':end' => $endDate
]);
$transactions = $transactionsStmt->fetchAll();

// 🔥 NOVO: resumo rápido
$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $t) {
    if ($t['type'] === 'income') $totalIncome += $t['amount'];
    if ($t['type'] === 'expense') $totalExpense += $t['amount'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lançamentos | AWZION Finance</title>
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
            <a class="nav-link active" href="transactions.php">Lançamentos</a>
            <a class="nav-link" href="categories.php">Categorias</a>
            <a class="nav-link" href="logout.php">Sair</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Lançamentos</h1>
                <p>Controle por mês</p>
            </div>

            <!-- 🔥 NOVO: filtro -->
            <form method="GET" class="month-inline">
                <input type="month" name="month" value="<?= h($selectedMonth) ?>">
                <button class="btn" type="submit">Filtrar</button>
            </form>
        </div>

        <!-- 🔥 NOVO: resumo -->
        <div class="cards">
            <div class="card">
                <div class="label">Ganhos</div>
                <div class="value green"><?= money($totalIncome) ?></div>
            </div>
            <div class="card">
                <div class="label">Gastos</div>
                <div class="value red"><?= money($totalExpense) ?></div>
            </div>
        </div>

        <?php if ($success === '1'): ?><div class="alert success">Lançamento salvo com sucesso.</div><?php endif; ?>
        <?php if ($success === '2'): ?><div class="alert success">Lançamento excluído com sucesso.</div><?php endif; ?>
        <?php if ($error === '1'): ?><div class="alert error">Preencha os dados corretamente.</div><?php endif; ?>

        <div class="grid two-fixed">
            <section class="panel">
                <h2>Novo lançamento</h2>

                <form action="save_transaction.php" method="POST">
                    <div class="field">
                        <label>Tipo</label>
                        <select name="type">
                            <option value="income">Ganho</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Título</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="field">
                        <label>Categoria</label>
                        <select name="category_id">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= h($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row-2">
                        <input type="number" name="amount" placeholder="Valor" step="0.01" required>
                        <input type="date" name="transaction_date" required>
                    </div>

                    <textarea name="notes" placeholder="Observações"></textarea>

                    <button class="btn">Salvar</button>
                </form>
            </section>

            <section class="panel">
                <h2>Histórico</h2>

                <?php if (!$transactions): ?>
                    <div class="empty">Sem lançamentos neste mês</div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Título</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($transactions as $item): ?>
                            <tr>
                                <td><?= $item['type'] === 'income' ? 'Ganho' : 'Gasto' ?></td>
                                <td><?= h($item['title']) ?></td>
                                <td><?= money((float)$item['amount']) ?></td>
                                <td><?= date('d/m/Y', strtotime($item['transaction_date'])) ?></td>
                                <td>
                                    <form action="delete_transaction.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button class="btn-danger">X</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>
</body>
</html>