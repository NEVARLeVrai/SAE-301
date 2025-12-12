<h1>Liste des Commandes</h1>

<p>Consultez l'historique des commandes passées sur le site.</p>

<?php if (isset($orders) && !empty($orders)): ?>
<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Client</th>
                <th>Montant Total</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= number_format($order['total_amount'], 2, ',', ' ') ?> €</td>
                    <td>
                        <span class="badge badge-<?= $order['status'] == 'paid' ? 'success' : 'warning' ?>">
                            <?= $order['status'] == 'paid' ? 'Payée' : ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    <p>Aucune commande pour le moment.</p>
</div>
<?php endif; ?>
