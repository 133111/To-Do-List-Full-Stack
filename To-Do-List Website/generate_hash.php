<?php
// Generate correct bcrypt hash for admin123 and tester123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "\n";

// For update SQL
echo "UPDATE SQL:\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'tester';\n";
?>
