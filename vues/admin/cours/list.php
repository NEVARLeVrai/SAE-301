<h1>Gestion des Cours</h1>

<div class="mb-2">
    <a href="index.php?action=create_cours" class="btn">+ Nouveau Cours</a>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Instrument</th>
                <th>Niveau</th>
                <th>Catégorie</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($liste_cours) && !empty($liste_cours)): ?>
                <?php foreach ($liste_cours as $cours): ?>
                    <tr>
                        <td><?php echo $cours['id']; ?></td>
                        <td><?php echo htmlspecialchars($cours['title']); ?></td>
                        <td><?php echo htmlspecialchars($cours['instrument']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $cours['level'] === 'debutant' ? 'success' : 
                                    ($cours['level'] === 'intermediaire' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo htmlspecialchars(ucfirst($cours['level'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($cours['category']); ?></td>
                        <td class="text-center">
                            <a href="index.php?action=edit_cours&id=<?php echo $cours['id']; ?>" class="btn-small">Modifier</a>
                            <a href="index.php?action=delete_cours&id=<?php echo $cours['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Aucun cours disponible.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

