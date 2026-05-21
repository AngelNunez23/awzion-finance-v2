<?php
declare(strict_types=1);

require_once 'config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;

requireLogin();

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = :user_id
    ORDER BY t.transaction_date DESC
");

$stmt->execute([':user_id' => $userId]);
$transactions = $stmt->fetchAll();

$html = '
<h1>Relatório Financeiro - AWZION Finance</h1>

<table border="1" width="100%" cellspacing="0" cellpadding="8">
    <tr>
        <th>Tipo</th>
        <th>Título</th>
        <th>Categoria</th>
        <th>Valor</th>
        <th>Data</th>
    </tr>
';

foreach ($transactions as $item) {
    $tipo = $item['type'] === 'income' ? 'Ganho' : 'Gasto';

    $html .= '
    <tr>
        <td>' . htmlspecialchars($tipo) . '</td>
        <td>' . htmlspecialchars($item['title']) . '</td>
        <td>' . htmlspecialchars($item['category_name'] ?? 'Sem categoria') . '</td>
        <td>R$ ' . number_format((float)$item['amount'], 2, ',', '.') . '</td>
        <td>' . date('d/m/Y', strtotime($item['transaction_date'])) . '</td>
    </tr>';
}

$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('relatorio-awzion-finance.pdf');
