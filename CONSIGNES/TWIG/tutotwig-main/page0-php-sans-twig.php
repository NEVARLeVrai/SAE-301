<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Essais Twig</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
<main>
	<nav>
		<!-- Menu spécifique à cette page -->
		<a href="page2-accueil.php">Retour Page Accueil</a>
	</nav>
	<h1>Page générée en PHP simple sans utiliser TWIG</h1>
	<h2>Les variables et le code PHP sont imbriqués dans le code HTML</h2>
	<?php
	// Chargement des données exemples
	include('include/data1-livre.php');
	include('include/data2-tableaux.php');

	// Des exemples de variables locales
	$titre = 'Premier essai Twig';
	$texte = 'Un <i>paragraphe</i> contenant des <b>Balises HTML</b> &amps; des caractères spéciaux';

	// Génération du code HTML en PHP à partir des variables
	echo '<h2>' . $titre . '</h2>';
	echo '<p>Bienvenue sur cette page</p>';
	echo '<p>' . $texte . '</p>';

	echo '<div class="livre">
		<h2>' . $livre_hyperion['titre'] . '</h2>
		<p>Numéro ISBN : ' . $livre_hyperion['isbn'] . '</p>
		<p>Prix : ' . $livre_hyperion['prix'] . ' € HT -  ' . ($livre_hyperion['prix'] * 1.2) . ' € TTC</p>
		<p>Editeur :
			<a href="#">' . $livre_hyperion['editeur'] . '</a>
		</p>
		<p>Résumé :</p>
		<p class="resume">' . $livre_hyperion['resume'] . '</p>
	</div>';

	echo '<h2>Affichage d\'un tableau simple sous forme de liste à puces</h2>';
	echo '<ul>';
	foreach ($mois as $element) {
		echo '<li>' . $element . '</li>';
	}
	echo '</ul>';

	echo '<h2>Un tableau de tableaux : une liste d\'éditeurs</h2>';
	echo '<ul class="livre">';
	foreach ($editeurs as $editeur) {
		echo '<li><a href="#">' . $editeur['nom'] . '</a>
			<br>
			<i>' . $editeur['adresse'] . '</i>
		</li>';
	}
	echo '</ul>';
	?>
</main>
</body>
</html>
