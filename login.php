<?php
declare(strict_types=1);
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AWZION Finance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="brand-center">
            <h1>AWZION Finance</h1>
            <p>Login do sistema financeiro</p>
        </div>

        <?php if ($error === '1'): ?>
            <div class="alert error">E-mail ou senha inválidos.</div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <div class="field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="admin@awzion.com" required>
            </div>
            <div class="field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="123456" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>

        <div class="login-help">
            
        </div>
    </div>
</body>
</html>
