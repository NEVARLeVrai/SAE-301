<?php

// Initialise Twig
include('include/twig.php');
$twig = init_twig();

// Lancement du moteur Twig :
// $twig->render($modele-de-page, $tableau-de-variables)
//
// Le premier paramètre est le nom du modèle de page (le fichier Twig) à utiliser
//
// Le second paramètre est un tableau contenant les variables envoyées au modèle Twig
// Chaque ligne indique 'nom-variable-twig' => valeur-variable-twig
echo $twig->render('page-independante.twig', [
	'paragraphe' => 'Un paragraphe contenant du texte et aussi des <b>Balises HTML</b> &amps; des caractères spéciaux. Notez que Twig échappe (inactive) les caractères spéciaux en les remplaçant par des entités HTML.',
	'url_image' => 'images/cigogne90px.png',
	'alt_image' => 'Une cigogne'
]);
