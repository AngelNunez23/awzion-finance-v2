<?php
declare(strict_types=1);
require_once 'config.php';

$email = 'admin@awzion.com';
$senha = '123456';

$stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

echo "<pre>";
var_dump($user);

if (!$user) {
    echo "USUARIO NAO ENCONTRADO";
    exit;
}

var_dump(password_verify($senha, $user['password_hash']));
echo "</pre>";