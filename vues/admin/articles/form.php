<h1><?php echo isset($article) ? 'Modifier l\'article' : 'Nouvel Article'; ?></h1>

<form action="index.php?action=<?php echo isset($article) ? 'edit_article' : 'create_article'; ?>" method="POST" enctype="multipart/form-data">
    <?php if(isset($article)): ?>
        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
        <input type="hidden" name="current_image" value="<?php echo $article['image_url']; ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="title">Titre de l'article</label>
        <input type="text" id="title" name="title" required value="<?php echo isset($article) ? htmlspecialchars($article['title']) : ''; ?>">
    </div>

    <div class="form-group">
        <label for="category">Catégorie</label>
        <select id="category" name="category" required>
            <option value="Actualité" <?php echo (isset($article) && $article['category'] == 'Actualité') ? 'selected' : ''; ?>>Actualité</option>
            <option value="Technique" <?php echo (isset($article) && $article['category'] == 'Technique') ? 'selected' : ''; ?>>Technique</option>
            <option value="Interviews" <?php echo (isset($article) && $article['category'] == 'Interviews') ? 'selected' : ''; ?>>Interviews</option>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Image à la une (US-23)</label>
        <?php if(isset($article) && !empty($article['image_url'])): ?>
            <div class="mb-2">
                <img src="../../assets/<?php echo htmlspecialchars($article['image_url']); ?>" alt="Aperçu" class="admin-preview-img">
            </div>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/*">
        <small>Formats acceptés : JPG, PNG. Max 5Mo.</small>
    </div>

    <div class="form-group">
        <label for="content">Contenu</label>
        <textarea id="content" name="content" required class="admin-textarea-large"><?php echo isset($article) ? htmlspecialchars($article['content']) : ''; ?></textarea>
    </div>

    <!-- US-25 : Système de brouillon -->
    <div class="form-group">
        <label for="status">Statut de l'article</label>
        <select id="status" name="status" required>
            <option value="published" <?php echo (!isset($article) || (isset($article['status']) && $article['status'] == 'published')) ? 'selected' : ''; ?>>Publié</option>
            <option value="draft" <?php echo (isset($article) && isset($article['status']) && $article['status'] == 'draft') ? 'selected' : ''; ?>>Brouillon</option>
        </select>
    </div>

    <!-- US-22 : Planification de publication -->
    <div class="form-group">
        <label for="published_at">Date de publication (Laisser vide pour publication immédiate)</label>
        <input type="datetime-local" id="published_at" name="published_at" value="<?php echo (isset($article) && !empty($article['published_at'])) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : ''; ?>">
    </div>

    <!-- US-28 : Tags -->
    <?php if(isset($allTags) && !empty($allTags)): ?>
    <div class="form-group">
        <label>Tags (US-28)</label>
        <div class="tags-checkboxes" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <?php foreach($allTags as $tag): ?>
                <label style="display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: #f1f5f9; border-radius: 4px; cursor: pointer;">
                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" 
                        <?php echo (isset($articleTagIds) && in_array($tag['id'], $articleTagIds)) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($tag['name']); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <small>Sélectionnez les tags à associer à cet article.</small>
    </div>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="btn"><?php echo isset($article) ? 'Mettre à jour' : 'Publier'; ?></button>
        <a href="index.php?action=articles" class="btn btn-outline">Annuler</a>
    </div>
</form>