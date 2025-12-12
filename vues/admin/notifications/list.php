<h1>Centre de Notifications</h1>

<p>Consultez les notifications et alertes du système.</p>

<?php if (isset($notifications) && !empty($notifications)): ?>
    <div class="notifications-actions mb-3">
        <a href="index.php?action=mark_all_notifications_read" class="btn btn-outline">
            ✓ Tout marquer comme lu
        </a>
    </div>

    <div class="notifications-list">
        <?php foreach ($notifications as $notif): ?>
            <div class="notification-card admin-card mb-2 <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                <div class="notification-content">
                    <div class="notification-icon">
                        <?php echo $notif['is_read'] ? '📭' : '📬'; ?>
                    </div>
                    <div class="notification-body">
                        <p class="notification-message">
                            <?php echo htmlspecialchars($notif['message']); ?>
                        </p>
                        <small class="notification-date text-muted">
                            <?php echo date('d/m/Y à H:i', strtotime($notif['created_at'])); ?>
                        </small>
                    </div>
                    <div class="notification-actions">
                        <?php if (!empty($notif['link'])): ?>
                            <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn-small">Voir</a>
                        <?php endif; ?>
                        <?php if (!$notif['is_read']): ?>
                            <a href="index.php?action=mark_notification_read&id=<?php echo $notif['id']; ?>" class="btn-small btn-outline" title="Marquer comme lu">✓</a>
                        <?php endif; ?>
                        <a href="index.php?action=delete_notification&id=<?php echo $notif['id']; ?>" class="btn-small btn-danger" title="Supprimer" onclick="return confirm('Supprimer cette notification ?');">🗑</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <p>🔔 Aucune notification pour le moment.</p>
    </div>
<?php endif; ?>

