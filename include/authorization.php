<?php
/**
 * Include/authorization.php
 *
 * Helpers centralisés pour la gestion des permissions et du cache des permissions en
 * session. Le contrôleur appelle ces fonctions pour vérifier l'accès aux actions
 * et charger en mémoire les permissions du rôle courant afin d'éviter des
 * requêtes répétées.
 *
 * Fonctions exportées :
 * - hasPermission(PDO $db, string $permission): bool
 * - loadRolePermissionsIntoSession(PDO $db, string $role): array
 * - requirePermission(PDO $db, string $permission): void (die si refus)
 * - getPermissionsForTwig(PDO $db): array
 * - isRestrictedToOwn(string $resource): bool
 *
 * Remarque : ces helpers utilisent la variable superglobale `$_SESSION`.
 */

include_once __DIR__ . '/../modeles/Permission.php';

/**
 * Ensure a PHP session is started.
 *
 * Cette fonction est utilisée en interne par les helpers pour garantir
 * l'existence de `$_SESSION`.
 *
 * @return void
 */
function _ensure_session_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Vérifie si le rôle courant a la permission demandée.
 * Utilise le cache en session si disponible, sinon interroge la BDD via le modèle Permission.
 *
 * @param PDO $db Connexion PDO
 * @param string $permission Nom de la permission à vérifier
 * @return bool True si autorisé
 */
function hasPermission($db, $permission) {
    _ensure_session_started();

    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
        return true;
    }

    if (!isset($_SESSION['admin_role'])) return false;

    if (isset($_SESSION['admin_permissions']) && is_array($_SESSION['admin_permissions'])) {
        return isset($_SESSION['admin_permissions'][$permission]) ? (bool)$_SESSION['admin_permissions'][$permission] : false;
    }

    $permModel = new Permission($db);
    return $permModel->hasPermission($_SESSION['admin_role'], $permission);
}

/**
 * Charge les permissions d'un rôle en session (`$_SESSION['admin_permissions']`).
 *
 * @param PDO $db
 * @param string $role
 * @return array Tableau associatif permission => bool
 */
function loadRolePermissionsIntoSession($db, $role) {
    _ensure_session_started();
    $permModel = new Permission($db);
    $_SESSION['admin_permissions'] = $permModel->getByRole($role);
    return $_SESSION['admin_permissions'];
}

/**
 * Vérifie une permission et renvoie une erreur fatale si l'utilisateur n'a pas accès.
 * Utilisé par les contrôleurs pour protéger des actions sensibles.
 *
 * @param PDO $db
 * @param string $permission
 * @return void
 */
function requirePermission($db, $permission) {
    if (!hasPermission($db, $permission)) {
        die("Accès refusé.");
    }
}

/**
 * Retourne les permissions du rôle courant au format utilisable par Twig.
 * Si l'utilisateur est `admin`, toutes les permissions sont retournées à true.
 *
 * @param PDO $db
 * @return array Tableau permission => bool
 */
function getPermissionsForTwig($db) {
    _ensure_session_started();
    
    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
        // Admin a toutes les permissions
        return [
            'manage_users' => true,
            'manage_articles' => true,
            'manage_courses' => true,
            'manage_products' => true,
            'manage_orders' => true,
            'manage_configurations' => true,
            'moderate_content' => true,
            'view_reports' => true,
            'export_data' => true,
            'manage_annonces' => true
        ];
    }

    if (!isset($_SESSION['admin_permissions']) || !is_array($_SESSION['admin_permissions'])) {
        loadRolePermissionsIntoSession($db, $_SESSION['admin_role'] ?? '');
    }

    return $_SESSION['admin_permissions'] ?? [];
}

/**
 * Indique si le rôle courant est restreint à ses propres ressources.
 * Exemples : 'redacteur' => articles (seulement ses articles), 'musicien' => produits (ses produits)
 * Centralise les règles au même endroit pour éviter les checks dispersés dans les contrôleurs.
 *
 * @param string $resource Nom logique de la ressource ('articles', 'produits', ...)
 * @return bool
 */
function isRestrictedToOwn($resource) {
    _ensure_session_started();
    $role = $_SESSION['admin_role'] ?? '';
    $map = [
        'redacteur' => ['articles'],
        'musicien' => ['produits']
    ];
    if (!$role) return false;
    return isset($map[$role]) && in_array($resource, $map[$role]);
}

?>
