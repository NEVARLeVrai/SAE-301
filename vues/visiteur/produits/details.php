<div class="product-detail-container">
    <a href="index.php?action=boutique" class="btn btn-secondary mb-2">&larr; Retour à la boutique</a>
    
    <div class="card">
        <?php if(!empty($produit['image_url'])): ?>
            <img src="../../assets/<?php echo htmlspecialchars($produit['image_url']); ?>" alt="<?php echo htmlspecialchars($produit['name']); ?>" class="product-detail-img">
        <?php endif; ?>
        
        <div class="product-body p-3">
            <h1><?php echo htmlspecialchars($produit['name']); ?></h1>
            <p class="product-price"><?php echo number_format($produit['price'], 2); ?> €</p>
            <p class="card-meta">
                Catégorie : <strong><?php echo htmlspecialchars($produit['category']); ?></strong> | 
                Type : <em><?php echo htmlspecialchars($produit['type']); ?></em>
            </p>
            
            <div class="product-description mt-2">
                <?php echo nl2br(htmlspecialchars($produit['description'])); ?>
            </div>

            <div class="mt-2">
                <?php if($produit['stock'] > 0): ?>
                    <p class="text-success">En stock (<?php echo $produit['stock']; ?> disponible<?php echo $produit['stock'] > 1 ? 's' : ''; ?>)</p>
                    <a href="index.php?action=add_to_cart&id=<?php echo $produit['id']; ?>" class="btn">Ajouter au panier</a>
                <?php else: ?>
                    <p class="text-danger">Rupture de stock</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- US-21 : Avis et Notes -->
    <section class="reviews-section mt-4">
        <h2>Avis clients</h2>
        
        <?php if(isset($moyenne_note) && $moyenne_note > 0): ?>
            <p>Note moyenne : <strong><?php echo $moyenne_note; ?>/5</strong> ⭐</p>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['user_logged_in']) && isset($user_has_bought) && $user_has_bought): ?>
            <form action="index.php?action=add_review" method="POST" class="review-form mb-3">
                <input type="hidden" name="product_id" value="<?php echo $produit['id']; ?>">
                <div class="form-group">
                    <label for="rating">Votre note</label>
                    <select id="rating" name="rating" required class="form-control w-auto">
                        <option value="">-- Choisir --</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="review_comment">Votre avis</label>
                    <textarea id="review_comment" name="comment" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn">Envoyer mon avis</button>
            </form>
        <?php elseif(isset($_SESSION['user_logged_in'])): ?>
            <p class="text-muted">Vous devez acheter ce produit pour laisser un avis.</p>
        <?php else: ?>
            <p><a href="index.php?action=login">Connectez-vous</a> pour laisser un avis.</p>
        <?php endif; ?>

        <div class="reviews-list">
            <?php if(isset($avis) && !empty($avis)): ?>
                <?php foreach($avis as $review): ?>
                    <div class="review-card card mb-2">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                            <span>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $review['rating'] ? '⭐' : '☆'; ?>
                                <?php endfor; ?>
                            </span>
                            <small class="text-muted">le <?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <?php if(!empty($review['comment'])): ?>
                            <div class="review-body">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun avis pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
