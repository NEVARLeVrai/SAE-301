<h1>Gestion des Produits</h1>

<div class="mb-2">
    <a href="index.php?action=create_produit" class="btn">+ Nouveau Produit</a>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Catégorie</th>
                <th>Type</th>
                <th>Statut</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($liste_produits) && count($liste_produits) > 0): ?>
                <?php foreach ($liste_produits as $produit): ?>
                    <tr>
                        <td><?= $produit['id'] ?></td>
                        <td><?= htmlspecialchars($produit['name']) ?></td>
                        <td><?= number_format($produit['price'], 2) ?> €</td>
                        <td><?= $produit['stock'] ?></td>
                        <td><?= htmlspecialchars($produit['category']) ?></td>
                        <td><?= $produit['type'] ?></td>
                        <td>
                            <span class="badge badge-<?= $produit['status'] === 'approved' ? 'success' : ($produit['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                <?= ucfirst($produit['status']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="index.php?action=edit_produit&id=<?= $produit['id'] ?>" class="btn-small">Modifier</a>
                            <a href="index.php?action=delete_produit&id=<?= $produit['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Aucun produit trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
