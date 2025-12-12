<h1>Modifier l'utilisateur</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form action="index.php?action=edit_user" method="POST" class="admin-form">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    
    <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>

    <div class="form-group">
        <label for="role">Rôle</label>
        <select id="role" name="role" required>
            <option value="visiteur" <?= $user['role'] === 'visiteur' ? 'selected' : '' ?>>👤 Visiteur</option>
            <option value="musicien" <?= $user['role'] === 'musicien' ? 'selected' : '' ?>>🎸 Musicien</option>
            <option value="redacteur" <?= $user['role'] === 'redacteur' ? 'selected' : '' ?>>✍️ Rédacteur</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>🛡️ Administrateur</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Enregistrer</button>
        <a href="index.php?action=users" class="btn btn-outline">Annuler</a>
    </div>
</form>
