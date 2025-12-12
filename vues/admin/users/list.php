<h1>Gestion des Utilisateurs</h1>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Date d'inscription</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($users) && count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                $roleColor = 'secondary';
                                switch($user['role']) {
                                    case 'admin': $roleColor = 'danger'; break;
                                    case 'redacteur': $roleColor = 'warning'; break;
                                    case 'musicien': $roleColor = 'info'; break;
                                    case 'auteur': $roleColor = 'success'; break;
                                    default: $roleColor = 'secondary';
                                }
                                echo $roleColor;
                            ?>">
                                <?= htmlspecialchars(ucfirst($user['role'])) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="index.php?action=edit_user&id=<?= $user['id'] ?>" class="btn-small">Modifier</a>
                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                <a href="index.php?action=delete_user&id=<?= $user['id'] ?>" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Aucun utilisateur trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
