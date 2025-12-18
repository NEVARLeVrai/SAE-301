<?php
// Script de hachage de mot de passe
// Usage:
//   php tools/hash_password.php [password] [algo]
//   algo: 'argon2id' ou 'bcrypt' (default)

$password = $argv[1] ?? 'respannonce123';
$algoArg = $argv[2] ?? '';

if (strtolower($algoArg) === 'argon2id' && defined('PASSWORD_ARGON2ID')) {
    $algo = PASSWORD_ARGON2ID;
    $algoName = 'argon2id';
} else {
    $algo = PASSWORD_DEFAULT; // généralement bcrypt or better
    $algoName = 'default';
}

$hash = password_hash($password, $algo);

echo "Password: $password" . PHP_EOL;
echo "Algorithm: $algoName" . PHP_EOL;
echo "Hash: $hash" . PHP_EOL;

// Vérification exemple (décommenter pour tester dans le script):
// var_dump(password_verify($password, $hash));

exit(0);
