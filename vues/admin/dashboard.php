<?php
$role = $_SESSION['admin_role'] ?? '';
?>
<h1>Tableau de bord</h1>
<p>Bienvenue, <strong><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Utilisateur'; ?></strong>.</p>

<div class="dashboard-stats">
    <?php if($role === 'admin'): ?>
        <div class="admin-card text-center">
            <h3>Articles</h3>
            <p class="stat-number text-secondary"><?php echo isset($nbArticles) ? $nbArticles : 0; ?></p>
            <a href="index.php?action=articles" class="btn-small">Gérer</a>
        </div>
        <div class="admin-card text-center">
            <h3>Cours</h3>
            <p class="stat-number text-accent"><?php echo isset($nbCours) ? $nbCours : 0; ?></p>
            <a href="index.php?action=cours" class="btn-small">Gérer</a>
        </div>
        <div class="admin-card text-center">
            <h3>Produits</h3>
            <p class="stat-number text-success"><?php echo isset($nbProduits) ? $nbProduits : 0; ?></p>
            <a href="index.php?action=produits" class="btn-small">Gérer</a>
        </div>
    <?php elseif($role === 'redacteur'): ?>
        <div class="admin-card text-center">
            <h3>Mes Articles</h3>
            <p class="stat-number text-secondary"><?php echo isset($nbArticles) ? $nbArticles : 0; ?></p>
            <a href="index.php?action=articles" class="btn-small">Gérer</a>
        </div>
        <div class="admin-card text-center">
            <h3>Modération Commentaires</h3>
            <p class="text-muted">Sur vos articles</p>
            <a href="index.php?action=comments" class="btn-small">Modérer</a>
        </div>
    <?php elseif($role === 'musicien'): ?>
        <div class="admin-card text-center">
            <h3>Mes Produits</h3>
            <p class="stat-number text-success"><?php echo isset($nbProduits) ? $nbProduits : 0; ?></p>
            <a href="index.php?action=produits" class="btn-small">Gérer</a>
        </div>
        <div class="mt-2">
            <h2>Mes Statistiques de Vente (US-33)</h2>
            <p>Chiffre d'affaires total : <strong><?php echo isset($caTotal) ? number_format($caTotal, 2) : '0.00'; ?> €</strong></p>
        </div>
    <?php else: ?>
        <p>Vous n'avez pas accès au tableau de bord.</p>
    <?php endif; ?>
</div>

<div class="mt-2">
    <h2>Activités récentes</h2>
    <p>Aucune activité récente.</p>
</div>
