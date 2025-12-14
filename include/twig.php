<?php
/**
 * ===========================================
 * CONFIGURATION TWIG - OmniMusique
 * ===========================================
 * 
 * Initialisation et configuration du moteur de templates Twig
 * Basé sur le tutoriel CONSIGNES/TWIG/tutotwig-main
 */

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

// Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Initialise le moteur de template Twig
 * 
 * @param string $templatePath Chemin vers le dossier des templates (relatif à la racine du projet)
 * @return Environment Instance Twig configurée
 */
function init_twig($templatePath = null) {
    // Chemin par défaut vers les templates depuis la racine du projet
    if ($templatePath === null) {
        $templatePath = __DIR__ . '/../templates';
    }
    
    // Création du loader avec le chemin des templates
    $loader = new FilesystemLoader($templatePath);
    
    // Configuration de l'environnement Twig
    $twig = new Environment($loader, [
        'debug' => true,
        'cache' => false, // Mettre un chemin vers un dossier cache en production
        'auto_reload' => true,
        'strict_variables' => false,
    ]);
    
    // Ajout de l'extension de debug (permet d'utiliser {{ dump(variable) }})
    $twig->addExtension(new DebugExtension());
    
    // Ajout de fonctions personnalisées utiles
    
    // Fonction pour générer le chemin de base
    $twig->addFunction(new TwigFunction('asset', function ($path) {
        return '../../assets/' . $path;
    }));
    
    // Fonction pour vérifier si un utilisateur est connecté
    $twig->addFunction(new TwigFunction('is_logged_in', function () {
        return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'];
    }));
    
    // Fonction pour vérifier si un admin est connecté
    $twig->addFunction(new TwigFunction('is_admin_logged_in', function () {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
    }));
    
    // Fonction pour obtenir le rôle de l'utilisateur
    $twig->addFunction(new TwigFunction('user_role', function () {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }));
    
    // Fonction pour obtenir le rôle admin
    $twig->addFunction(new TwigFunction('admin_role', function () {
        return isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
    }));
    
    return $twig;
}

/**
 * Rendu d'un template avec les variables de session globales
 * 
 * @param Environment $twig Instance Twig
 * @param string $template Nom du fichier template
 * @param array $variables Variables à passer au template
 * @return string HTML rendu
 */
function render_template($twig, $template, $variables = []) {
    // Variables globales disponibles dans tous les templates
    $globalVars = [
        'session' => $_SESSION ?? [],
        'user_logged_in' => isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'],
        'admin_logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'],
        'user_name' => $_SESSION['user_name'] ?? null,
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_role' => $_SESSION['user_role'] ?? null,
        'admin_name' => $_SESSION['admin_name'] ?? null,
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'admin_role' => $_SESSION['admin_role'] ?? null,
        'current_action' => $_GET['action'] ?? 'accueil',
        'base_path' => '../../',
    ];
    
    // Fusion des variables globales et des variables spécifiques
    $allVariables = array_merge($globalVars, $variables);
    
    return $twig->render($template, $allVariables);
}
