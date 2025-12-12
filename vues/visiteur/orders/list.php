<div class="container mt-4">
    <h2>Mes Commandes</h2>
    
    <?php if (empty($orders)): ?>
        <p>Vous n'avez pas encore passé de commande.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Référence</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>CMD-<?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['total_amount']) ?> €</td>
                        <td>
                            <span class="badge bg-<?= $order['status'] == 'paid' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <!-- Détails non implémentés dans cette vue simplifiée, mais on pourrait ajouter une action -->
                            <button class="btn btn-sm btn-info" disabled>Détails</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
