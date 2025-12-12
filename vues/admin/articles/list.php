<h1>Gestion des Articles</h1>

<div class="mb-2">
    <a href="index.php?action=create_article" class="btn">Rédiger un nouvel article</a>
</div>

<div class="table-responsive">
<table class="admin-table">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Catégorie</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($liste_articles) && count($liste_articles) > 0): ?>
            <?php foreach($liste_articles as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td class="text-center">
                        <a href="index.php?action=edit_article&id=<?php echo $row['id']; ?>" class="btn-small">Modifier</a>
                        <a href="index.php?action=delete_article&id=<?php echo $row['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Aucun article trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>