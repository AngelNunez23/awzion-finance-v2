<?php
declare(strict_types=1);
require_once 'config.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM categories WHERE user_id = :user_id ORDER BY type, name');
$stmt->execute([':user_id' => $userId]);
$categories = $stmt->fetchAll();

$totalCategories = count($categories);
$totalIncomeCategories = 0;
$totalExpenseCategories = 0;

foreach ($categories as $category) {
    if ($category['type'] === 'income') {
        $totalIncomeCategories++;
    } else {
        $totalExpenseCategories++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias | AWZION Finance</title>
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
            <a class="nav-link active" href="categories.php">Categorias</a>
            <a class="nav-link" href="logout.php">Sair</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Categorias</h1>
                <p>Organize ganhos e gastos por categoria.</p>
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <div class="label">Total de categorias</div>
                <div class="value"><?= $totalCategories ?></div>
            </div>
            <div class="card">
                <div class="label">Categorias de ganho</div>
                <div class="value green"><?= $totalIncomeCategories ?></div>
            </div>
            <div class="card">
                <div class="label">Categorias de gasto</div>
                <div class="value red"><?= $totalExpenseCategories ?></div>
            </div>
        </div>

        <?php if ($success === '1'): ?><div class="alert success">Categoria salva com sucesso.</div><?php endif; ?>
        <?php if ($success === '2'): ?><div class="alert success">Categoria excluída com sucesso.</div><?php endif; ?>
        <?php if ($error === '1'): ?><div class="alert error">Informe os dados corretamente.</div><?php endif; ?>

        <div class="grid two-fixed">
            <section class="panel">
                <h2>Nova categoria</h2>
                <form action="save_category.php" method="POST">
                    <div class="field">
                        <label for="name">Nome</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="field">
                        <label for="type">Tipo</label>
                        <select name="type" id="type" required>
                            <option value="income">Ganho</option>
                            <option value="expense">Gasto</option>
                        </select>
                    </div>
                    <button class="btn" type="submit">Salvar categoria</button>
                </form>
            </section>

            <section class="panel">
                <h2>Lista de categorias</h2>
                <?php if (!$categories): ?>
                    <div class="empty">Nenhuma categoria cadastrada.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= h($category['name']) ?></td>
                                    <td>
                                        <span class="badge <?= h($category['type']) ?>">
                                            <?= $category['type'] === 'income' ? 'Ganho' : 'Gasto' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="delete_category.php" method="POST" onsubmit="return confirm('Excluir categoria?');">
                                            <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                                            <button class="btn-danger" type="submit">Excluir</button>
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