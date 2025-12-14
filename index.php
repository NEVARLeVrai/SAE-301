<?php
/**
 * ===========================================
 * PAGE D'ACCUEIL - Sélection du Rôle
 * ===========================================
 * 
 * Point d'entrée principal du site.
 * Permet de choisir entre l'espace visiteur et admin.
 * 
 * Utilise Twig pour le rendu (conformité consignes).
 */

// Initialisation de Twig
include_once 'include/twig.php';
$twig = init_twig();

// Rendu du template d'accueil
echo $twig->render('index.twig', []);

