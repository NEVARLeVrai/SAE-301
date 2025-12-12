<h1>Modération des Commentaires</h1>

<p>Gérez les commentaires des utilisateurs sur les articles du blog.</p>

<!-- Filtres -->
<div class="filter-bar mb-3">
    <a href="index.php?action=comments&filter=pending" class="btn <?php echo (!isset($_GET['filter']) || $_GET['filter'] === 'pending') ? '' : 'btn-secondary'; ?>">
        En attente
    </a>
    <a href="index.php?action=comments&filter=approved" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'approved') ? '' : 'btn-secondary'; ?>">
        Approuvés
    </a>
    <a href="index.php?action=comments&filter=all" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'all') ? '' : 'btn-secondary'; ?>">
        Tous
    </a>
</div>

<?php if (isset($comments) && !empty($comments)): ?>
<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Article</th>
                <th>Auteur</th>
                <th>Commentaire</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?php echo $comment['id']; ?></td>
                    <td>
                        <a href="../visiteur/index.php?action=article_details&id=<?php echo $comment['article_id']; ?>" target="_blank">
                            <?php echo htmlspecialchars($comment['article_title'] ?? 'Article #' . $comment['article_id']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($comment['username']); ?></td>
                    <td>
                        <div class="comment-preview" title="<?php echo htmlspecialchars($comment['content']); ?>">
                            <?php echo htmlspecialchars(substr($comment['content'], 0, 100)); ?>
                            <?php if (strlen($comment['content']) > 100) echo '...'; ?>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $status_class = '';
                        switch ($comment['status']) {
                            case 'pending': $status_class = 'badge-warning'; break;
                            case 'approved': $status_class = 'badge-success'; break;
                            case 'rejected': $status_class = 'badge-danger'; break;
                        }
                        ?>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($comment['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                    <td class="actions">
                        <?php if ($comment['status'] === 'pending'): ?>
                            <a href="index.php?action=approve_comment&id=<?php echo $comment['id']; ?>" class="btn-small btn-success" title="Approuver">✓</a>
                            <a href="index.php?action=reject_comment&id=<?php echo $comment['id']; ?>" class="btn-small btn-warning" title="Rejeter">✗</a>
                        <?php endif; ?>
                        <a href="index.php?action=delete_comment&id=<?php echo $comment['id']; ?>" class="btn-small btn-danger" title="Supprimer" onclick="return confirm('Supprimer ce commentaire ?');">🗑</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info">
        <p>Aucun commentaire à afficher.</p>
    </div>
<?php endif; ?>

