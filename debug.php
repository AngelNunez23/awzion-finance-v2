<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "PHP funcionando<br>";

$stmt = $pdo->prepare("SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => 'admin@awzion.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
var_dump($user);

if ($user) {
    echo "Teste senha 123456:\n";
    var_dump(password_verify('123456', $user['password_hash']));
}
echo "</pre>";