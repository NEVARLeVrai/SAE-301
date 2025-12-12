<h1>Modération des Annonces</h1>
<p>Validez ou refusez les produits soumis par les vendeurs.</p>

<?php if (empty($pendingProducts)): ?>
    <div class="alert alert-info">
        <p>✓ Aucune annonce en attente de validation.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Vendeur</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingProducts as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image_url']): ?>
                                <img src="../../assets/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-thumbnail-small">
                            <?php else: ?>
                                <span class="text-muted">Pas d'image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</td>
                        <td><?= number_format($product['price'], 2, ',', ' ') ?> €</td>
                        <td>ID: <?= htmlspecialchars($product['seller_id']) ?></td>
                        <td class="actions">
                            <a href="index.php?action=approve_product&id=<?= $product['id'] ?>" class="btn-small btn-success">Approuver</a>
                            <a href="index.php?action=reject_product&id=<?= $product['id'] ?>" class="btn-small btn-danger">Rejeter</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>