<h1><?= isset($produit) ? 'Modifier' : 'Créer' ?> un Produit</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form action="index.php?action=<?= isset($produit) ? 'edit_produit' : 'create_produit' ?>" method="post" enctype="multipart/form-data" class="admin-form">
    <?php if (isset($produit)): ?>
        <input type="hidden" name="id" value="<?= $produit['id'] ?>">
        <input type="hidden" name="current_image" value="<?= $produit['image_url'] ?>">
        <input type="hidden" name="current_file" value="<?= $produit['file_url'] ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="name">Nom du produit</label>
        <input type="text" id="name" name="name" value="<?= isset($produit) ? htmlspecialchars($produit['name']) : '' ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5"><?= isset($produit) ? htmlspecialchars($produit['description']) : '' ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="price">Prix (€)</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= isset($produit) ? $produit['price'] : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" value="<?= isset($produit) ? $produit['stock'] : '0' ?>" required>
        </div>
        <div class="form-group">
            <label for="category">Catégorie</label>
            <input type="text" id="category" name="category" value="<?= isset($produit) ? htmlspecialchars($produit['category']) : '' ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="type">Type</label>
        <select id="type" name="type" required>
            <option value="partition_physique" <?= (isset($produit) && $produit['type'] == 'partition_physique') ? 'selected' : '' ?>>Partition Physique</option>
            <option value="partition_virtuelle" <?= (isset($produit) && $produit['type'] == 'partition_virtuelle') ? 'selected' : '' ?>>Partition Virtuelle (PDF)</option>
            <option value="instrument" <?= (isset($produit) && $produit['type'] == 'instrument') ? 'selected' : '' ?>>Instrument</option>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Image</label>
        <?php if (isset($produit) && $produit['image_url']): ?>
            <div class="mb-2">
                <img src="../../assets/<?= $produit['image_url'] ?>" alt="Image actuelle" class="admin-preview-img">
            </div>
        <?php endif; ?>
        <input type="file" id="image" name="image">
    </div>

    <div class="form-group">
        <label for="file">Fichier (PDF/MP3) - Optionnel</label>
        <?php if (isset($produit) && $produit['file_url']): ?>
            <p class="text-muted">Fichier actuel : <?= $produit['file_url'] ?></p>
        <?php endif; ?>
        <input type="file" id="file" name="file">
    </div>

    <div class="form-group">
        <label for="status">Statut</label>
        <select id="status" name="status">
            <option value="approved" <?= (isset($produit) && $produit['status'] == 'approved') ? 'selected' : '' ?>>Approuvé</option>
            <option value="pending" <?= (isset($produit) && $produit['status'] == 'pending') ? 'selected' : '' ?>>En attente</option>
            <option value="rejected" <?= (isset($produit) && $produit['status'] == 'rejected') ? 'selected' : '' ?>>Rejeté</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn"><?= isset($produit) ? 'Mettre à jour' : 'Créer' ?></button>
        <a href="index.php?action=produits" class="btn btn-outline">Annuler</a>
    </div>
</form>
