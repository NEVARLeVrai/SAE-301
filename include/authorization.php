<?php
/**
 * Fonctions d'autorisation centralisées
 */

include_once __DIR__ . '/../modeles/Permission.php';

function _ensure_session_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Vérifie si le rôle courant a la permission demandée.
 * Utilise le cache en session si disponible, sinon interroge la BDD.
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
 * Charge les permissions du rôle en session pour éviter des requêtes répétées.
 */
function loadRolePermissionsIntoSession($db, $role) {
    _ensure_session_started();
    $permModel = new Permission($db);
    $_SESSION['admin_permissions'] = $permModel->getByRole($role);
    return $_SESSION['admin_permissions'];
}

/**
 * Vérifie une permission et meurt si l'utilisateur n'a pas accès (pour les contrôleurs)
 */
function requirePermission($db, $permission) {
    if (!hasPermission($db, $permission)) {
        die("Accès refusé.");
    }
}

/**
 * Retourne les permissions de l'utilisateur actuel au format Twig (pour les templates)
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
 * Indique si le rôle courant est restreint à ses propres ressources
 * Exemples : 'redacteur' => articles (seulement ses articles), 'musicien' => produits (ses produits)
 * Centralise les règles au même endroit pour éviter les checks dispersés dans les contrôleurs.
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
