<h1>Nos Cours de Musique</h1>
<p>Recherchez et consultez des cours adaptés à votre niveau.</p>

<!-- Formulaire de filtres (US-12) -->
<form action="index.php" method="GET" class="filter-form mb-2">
    <input type="hidden" name="action" value="cours">
    
    <div class="filter-group">
        <select name="instrument">
            <option value="">Tous les instruments</option>
            <?php foreach($instruments as $inst): ?>
                <option value="<?php echo htmlspecialchars($inst); ?>" <?php if(isset($_GET['instrument']) && $_GET['instrument'] == $inst) echo 'selected'; ?>>
                    <?php echo '🎵 ' . htmlspecialchars($inst); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="level">
            <option value="">Tous les niveaux</option>
            <?php foreach($levels as $lvl): ?>
                <option value="<?php echo htmlspecialchars($lvl); ?>" <?php if(isset($_GET['level']) && $_GET['level'] == $lvl) echo 'selected'; ?>>
                    <?php echo '🎯 ' . htmlspecialchars($lvl); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="category">
            <option value="">Toutes les catégories</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if(isset($_GET['category']) && $_GET['category'] == $cat) echo 'selected'; ?>>
                    <?php echo '🎼 ' . htmlspecialchars($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-small">Filtrer</button>
        <a href="index.php?action=cours" class="btn btn-small btn-admin">Réinitialiser</a>
    </div>
</form>

<div class="cours-container">
    <?php if(isset($liste_cours) && count($liste_cours) > 0): ?>
        <?php foreach($liste_cours as $row): ?>
            <div class="cours-card">
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p class="card-meta"><strong>Instrument :</strong> <?php echo htmlspecialchars($row['instrument']); ?> | <strong>Niveau :</strong> <?php echo htmlspecialchars($row['level']); ?></p>
                <p class="card-content"><?php echo htmlspecialchars($row['description']); ?></p>
                <p><small>Par <?php echo htmlspecialchars($row['author']); ?></small></p>
                <a href="index.php?action=cours_details&id=<?php echo $row['id']; ?>" class="btn">Voir le cours</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun cours ne correspond à vos critères.</p>
    <?php endif; ?>
</div>
