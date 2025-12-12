<?php
// Liste simple des demandes de modération pour admin
?>
<h1>Demandes de modération</h1>
<h2>En attente</h2>
<?php if (!empty($pendingMreqs)): ?>
    <ul>
    <?php foreach ($pendingMreqs as $r): ?>
        <li>
            #<?php echo $r['id']; ?> - <?php echo htmlspecialchars($r['product_name']); ?> (par <?php echo htmlspecialchars($r['username']); ?>)
            - <a href="index.php?action=approve_mreq&id=<?php echo $r['id']; ?>">Approuver</a>
            - <a href="index.php?action=reject_mreq&id=<?php echo $r['id']; ?>">Rejeter</a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucune demande en attente.</p>
<?php endif; ?>

<h2>Historique</h2>
<?php if (!empty($allMreqs)): ?>
    <ul>
    <?php foreach ($allMreqs as $r): ?>
        <li>#<?php echo $r['id']; ?> - <?php echo htmlspecialchars($r['product_name']); ?> - <?php echo $r['status']; ?> - demandé par <?php echo htmlspecialchars($r['username']); ?><?php if (!empty($r['processed_by_name'])) echo ' - traité par ' . htmlspecialchars($r['processed_by_name']); ?></li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun historique.</p>
<?php endif; ?>