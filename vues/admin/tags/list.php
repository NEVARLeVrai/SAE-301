<h1>Gestion des Tags</h1>

<p>Gérez les tags pour catégoriser les articles et produits.</p>

<div class="admin-grid">
    <!-- Formulaire d'ajout -->
    <div class="admin-card">
        <h3>Ajouter un Tag</h3>
        <form action="index.php?action=tags" method="POST" class="admin-form">
            <div class="form-group">
                <label for="name">Nom du Tag</label>
                <input type="text" id="name" name="name" required placeholder="Ex: Jazz, Rock, Classique...">
            </div>
            <button type="submit" class="btn">Ajouter</button>
        </form>
    </div>
    
    <!-- Liste des tags -->
    <div class="admin-card">
        <h3>Liste des Tags</h3>
        <?php if (isset($tags) && !empty($tags)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td><?= htmlspecialchars($tag['id']) ?></td>
                            <td><?= htmlspecialchars($tag['name']) ?></td>
                            <td class="actions">
                                <a href="index.php?action=tags&delete_id=<?= $tag['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Supprimer ce tag ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">Aucun tag pour le moment.</p>
        <?php endif; ?>
    </div>
</div>
