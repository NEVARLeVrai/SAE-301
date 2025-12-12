<h1>Demandes de rôle</h1>
<p>Validez ou refusez les demandes de promotion des utilisateurs.</p>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Demandes en attente -->
<div class="admin-card mb-3">
    <h3>En attente de validation</h3>
    
    <?php if (!empty($pendingRequests)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle demandé</th>
                        <th>Motivation</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $request): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($request['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo ucfirst($request['requested_role']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($request['motivation'] ?? 'Non spécifié'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?></td>
                            <td class="actions">
                                <a href="index.php?action=approve_role_request&id=<?php echo $request['id']; ?>" 
                                   class="btn-small btn-success" 
                                   onclick="return confirm('Approuver cette demande ? L\'utilisateur deviendra <?php echo $request['requested_role']; ?>.');">
                                    Approuver
                                </a>
                                <a href="index.php?action=reject_role_request&id=<?php echo $request['id']; ?>" 
                                   class="btn-small btn-danger"
                                   onclick="return confirm('Rejeter cette demande ?');">
                                    Rejeter
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucune demande en attente.</p>
    <?php endif; ?>
</div>

<!-- Historique -->
<div class="admin-card">
    <h3>Historique des demandes</h3>
    
    <?php if (!empty($allRequests)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Rôle demandé</th>
                        <th>Statut</th>
                        <th>Date demande</th>
                        <th>Traité par</th>
                        <th>Date traitement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRequests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                            <td><?php echo ucfirst($request['requested_role']); ?></td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php elseif ($request['status'] === 'approved'): ?>
                                    <span class="badge badge-success">Approuvé</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejeté</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($request['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($request['processed_by_name'] ?? '-'); ?></td>
                            <td><?php echo $request['processed_at'] ? date('d/m/Y', strtotime($request['processed_at'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucune demande dans l'historique.</p>
    <?php endif; ?>
</div>
