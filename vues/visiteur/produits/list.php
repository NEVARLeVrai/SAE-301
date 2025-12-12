<h1>Boutique en ligne</h1>
<p>Partitions, Instruments et Accessoires.</p>

<div class="products-container">
    <?php if(isset($liste_produits) && count($liste_produits) > 0): ?>
        <?php foreach($liste_produits as $row): ?>
            <div class="product-card">
                <?php if(!empty($row['image_url'])): ?>
                    <img src="../../assets/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-card-img">
                <?php endif; ?>
                <div class="card-content">
                    <h2><a href="index.php?action=produit_details&id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></h2>
                    <p class="product-price"><?php echo number_format($row['price'], 2); ?> €</p>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p><small>Catégorie : <?php echo htmlspecialchars($row['category']); ?></small></p>
                    
                    <div class="product-actions">
                        <a href="index.php?action=produit_details&id=<?php echo $row['id']; ?>" class="btn btn-small btn-admin">Voir détails</a>
                        <?php if($row['stock'] > 0): ?>
                            <a href="index.php?action=add_to_cart&id=<?php echo $row['id']; ?>" class="btn btn-small">Ajouter au panier</a>
                        <?php else: ?>
                            <span class="btn btn-small btn-admin btn-disabled">Rupture de stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun produit disponible.</p>
    <?php endif; ?>
</div>
