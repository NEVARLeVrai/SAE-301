<h1>Matrice des Permissions</h1>
<p>Définissez finement les permissions de chaque rôle.</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php 
$labels = Permission::getPermissionLabels();
?>

<div class="admin-card">
    <form action="index.php?action=permissions" method="POST">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Permission</th>
                        <?php foreach ($permissionData['roles'] as $role): ?>
                            <th class="text-center">
                                <?php echo ucfirst($role); ?>
                                <?php if ($role === 'admin'): ?>
                                    ⭐
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissionData['permissions'] as $permission): ?>
                        <tr>
                            <td>
                                <strong><?php echo $labels[$permission] ?? $permission; ?></strong>
                                <br><small class="text-muted"><?php echo $permission; ?></small>
                            </td>
                            <?php foreach ($permissionData['roles'] as $role): ?>
                                <td class="text-center">
                                    <?php 
                                    $fieldName = $role . '_' . $permission;
                                    $isChecked = isset($permissionData['matrix'][$role][$permission]) && $permissionData['matrix'][$role][$permission];
                                    ?>
                                    <input type="checkbox" 
                                           name="<?php echo $fieldName; ?>" 
                                           id="<?php echo $fieldName; ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>
                                           <?php echo ($role === 'admin') ? 'disabled checked' : ''; ?>>
                                    <?php if ($role === 'admin'): ?>
                                        <input type="hidden" name="<?php echo $fieldName; ?>" value="1">
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="form-group" style="margin-top: 1.5rem;">
            <button type="submit" class="btn">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<div class="admin-card" style="margin-top: 1.5rem;">
    <h3>Légende</h3>
    <ul>
        <li><strong>Admin</strong> : Toutes les permissions sont activées par défaut (non modifiable)</li>
        <li><strong>Rédacteur</strong> : Peut gérer les articles et modérer le contenu</li>
        <li><strong>Musicien</strong> : Peut gérer ses cours et produits, voir ses statistiques</li>
    </ul>
</div>
