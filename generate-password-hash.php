<?php
// Script para generar hash de contraseña
// Este script genera el hash correcto para la contraseña 'admin123'

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: {$password}\n";
echo "Hash: {$hash}\n";
echo "\n";
echo "Para verificar:\n";
echo "password_verify('{$password}', '{$hash}') = " . (password_verify($password, $hash) ? 'TRUE' : 'FALSE') . "\n";
?>
