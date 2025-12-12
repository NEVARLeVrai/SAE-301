<h1><?php echo isset($cours_edit) ? 'Modifier le Cours' : 'Nouveau Cours'; ?></h1>

<form method="POST" action="index.php?action=<?php echo isset($cours_edit) ? 'edit_cours' : 'create_cours'; ?>" class="admin-form">
    <?php if (isset($cours_edit)): ?>
        <input type="hidden" name="id" value="<?php echo $cours_edit['id']; ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="title">Titre du cours *</label>
        <input type="text" id="title" name="title" 
               value="<?php echo isset($cours_edit) ? htmlspecialchars($cours_edit['title']) : ''; ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description courte *</label>
        <textarea id="description" name="description" rows="3" required><?php echo isset($cours_edit) ? htmlspecialchars($cours_edit['description']) : ''; ?></textarea>
    </div>

    <div class="form-group">
        <label for="content">Contenu du cours (HTML ou lien vidéo)</label>
        <textarea id="content" name="content" rows="8" class="admin-textarea-large"><?php echo isset($cours_edit) ? htmlspecialchars($cours_edit['content']) : ''; ?></textarea>
        <small class="text-muted">Vous pouvez inclure du HTML, des liens YouTube embed, etc.</small>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="instrument">Instrument *</label>
            <input type="text" id="instrument" name="instrument" 
                   value="<?php echo isset($cours_edit) ? htmlspecialchars($cours_edit['instrument']) : ''; ?>" 
                   placeholder="Ex: Piano, Guitare..." required>
        </div>

        <div class="form-group">
            <label for="level">Niveau *</label>
            <select id="level" name="level" required>
                <option value="">-- Choisir --</option>
                <option value="debutant" <?php echo (isset($cours_edit) && $cours_edit['level'] === 'debutant') ? 'selected' : ''; ?>>🟢 Débutant</option>
                <option value="intermediaire" <?php echo (isset($cours_edit) && $cours_edit['level'] === 'intermediaire') ? 'selected' : ''; ?>>🟡 Intermédiaire</option>
                <option value="avance" <?php echo (isset($cours_edit) && $cours_edit['level'] === 'avance') ? 'selected' : ''; ?>>🟢 Avancé</option>
            </select>
        </div>

        <div class="form-group">
            <label for="category">Catégorie / Style *</label>
            <input type="text" id="category" name="category" 
                   value="<?php echo isset($cours_edit) ? htmlspecialchars($cours_edit['category']) : ''; ?>" 
                   placeholder="Ex: Classique, Jazz, Rock..." required>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">
            <?php echo isset($cours_edit) ? 'Mettre à jour' : 'Créer le cours'; ?>
        </button>
        <a href="index.php?action=cours" class="btn btn-outline">Annuler</a>
    </div>
</form>
