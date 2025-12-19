<?php
/**
 * tools/hash_password.php
 *
 * Petit utilitaire CLI pour générer un hash de mot de passe compatible PHP
 * (`password_hash`) pour peupler la base de données lors de l'initialisation.
 *
 * Usage:
 *   php tools/hash_password.php [password] [algo]
 *
 *   - password : mot de passe à hacher (optionnel, défaut : 'respannonce123')
 *   - algo     : 'argon2id' ou 'bcrypt' (optionnel, default : PASSWORD_DEFAULT)
 *
 * Exemple:
 *   php tools/hash_password.php admin123 argon2id
 *
 * Remarques de sécurité:
 * - En production, préférez `PASSWORD_ARGON2ID` si disponible.
 * - Ne stockez jamais de mots de passe en clair dans le dépôt.
 * - Le script est prévu pour être utilisé en local/installation uniquement.
 */

$password = $argv[1] ?? 'respannonce123';
$algoArg = $argv[2] ?? '';

if (strtolower($algoArg) === 'argon2id' && defined('PASSWORD_ARGON2ID')) {
    $algo = PASSWORD_ARGON2ID;
    $algoName = 'argon2id';
} else {
    $algo = PASSWORD_DEFAULT; // généralement bcrypt ou meilleur algo disponible
    $algoName = 'default';
}

$hash = password_hash($password, $algo);

echo "Password: $password" . PHP_EOL;
echo "Algorithm: $algoName" . PHP_EOL;
echo "Hash: $hash" . PHP_EOL;

// Vérification exemple (décommenter pour tester dans le script):
// var_dump(password_verify($password, $hash));

exit(0);
