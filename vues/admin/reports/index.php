<h1>Rapports & Statistiques</h1>
<p>Visualisez et exportez les données d'activité du site.</p>

<!-- Filtres de période -->
<div class="admin-card mb-3">
    <h3>Filtrer par période</h3>
    <form action="index.php" method="GET" class="filter-form">
        <input type="hidden" name="action" value="reports">
        <div class="form-group">
            <label>Date de début</label>
            <input type="date" name="date_start" value="<?php echo $date_start ?? date('Y-m-01'); ?>">
        </div>
        <div class="form-group">
            <label>Date de fin</label>
            <input type="date" name="date_end" value="<?php echo $date_end ?? date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn">Filtrer</button>
        </div>
    </form>
</div>

<!-- Statistiques générales -->
<div class="dashboard-stats mb-3">
    <div class="admin-card text-center">
        <h3>Commandes</h3>
        <p class="stat-number text-primary"><?php echo $stats['total_orders'] ?? 0; ?></p>
        <small>sur la période</small>
    </div>
    <div class="admin-card text-center">
        <h3>Chiffre d'Affaires</h3>
        <p class="stat-number text-success"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> €</p>
        <small>sur la période</small>
    </div>
    <div class="admin-card text-center">
        <h3>Nouveaux Users</h3>
        <p class="stat-number text-info"><?php echo $stats['new_users'] ?? 0; ?></p>
        <small>sur la période</small>
    </div>
    <div class="admin-card text-center">
        <h3>Articles publiés</h3>
        <p class="stat-number text-secondary"><?php echo $stats['new_articles'] ?? 0; ?></p>
        <small>sur la période</small>
    </div>
</div>

<!-- Graphique simplifié (barres CSS) -->
<div class="admin-card mb-3">
    <h3>Ventes par catégorie</h3>
    <?php if (!empty($salesByCategory)): ?>
        <div class="chart-container">
            <?php 
            $maxSales = max(array_column($salesByCategory, 'total'));
            foreach ($salesByCategory as $cat): 
                $percentage = $maxSales > 0 ? ($cat['total'] / $maxSales) * 100 : 0;
            ?>
                <div class="chart-bar-container mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span><?php echo htmlspecialchars($cat['category'] ?? 'Non catégorisé'); ?></span>
                        <span><strong><?php echo number_format($cat['total'], 2); ?> €</strong></span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucune donnée de vente sur cette période.</p>
    <?php endif; ?>
</div>

<!-- Boutons d'export -->
<div class="admin-card">
    <h3>Exporter les données</h3>
    <p>Téléchargez les rapports au format de votre choix.</p>
    
    <div class="btn-group">
        <a href="index.php?action=export_csv&type=orders&date_start=<?php echo $date_start ?? ''; ?>&date_end=<?php echo $date_end ?? ''; ?>" 
           class="btn btn-outline">
            Export Commandes (CSV)
        </a>
        <a href="index.php?action=export_csv&type=users&date_start=<?php echo $date_start ?? ''; ?>&date_end=<?php echo $date_end ?? ''; ?>" 
           class="btn btn-outline">
            Export Utilisateurs (CSV)
        </a>
        <a href="index.php?action=export_csv&type=products&date_start=<?php echo $date_start ?? ''; ?>&date_end=<?php echo $date_end ?? ''; ?>" 
           class="btn btn-outline">
            Export Produits (CSV)
        </a>
    </div>
    
    <hr>
    
    <div class="btn-group">
        <a href="index.php?action=export_pdf&date_start=<?php echo $date_start ?? ''; ?>&date_end=<?php echo $date_end ?? ''; ?>" 
           class="btn btn-secondary">
            Rapport complet (PDF)
        </a>
    </div>
</div>

<!-- Tableau des dernières commandes -->
<div class="admin-card mt-3">
    <h3>Dernières commandes</h3>
    <?php if (!empty($recentOrders)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'Invité'); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> €</td>
                            <td>
                                <?php if ($order['status'] === 'paid'): ?>
                                    <span class="badge badge-success">Payé</span>
                                <?php elseif ($order['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?php echo $order['status']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucune commande sur cette période.</p>
    <?php endif; ?>
</div>
