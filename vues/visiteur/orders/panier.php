<h1>Votre Panier</h1>

<?php if(isset($panier_details) && count($panier_details) > 0): ?>
    <form action="index.php?action=update_cart" method="POST">
        <div class="cart-container">
            <table class="cart-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="text-center">Prix</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($panier_details as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($item['type']); ?></small>
                        </td>
                        <td class="text-center"><?php echo number_format($item['price'], 2); ?> €</td>
                        <td class="text-center">
                            <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['qty']; ?>" min="0" class="cart-qty-input">
                        </td>
                        <td class="text-right"><?php echo number_format($item['subtotal'], 2); ?> €</td>
                        <td class="text-center">
                            <a href="index.php?action=remove_from_cart&id=<?php echo $item['id']; ?>" class="cart-remove-link">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right cart-total-label">Total :</td>
                    <td class="text-right cart-total-value"><?php echo number_format($total, 2); ?> €</td>
                    <td></td>
                </tr>
            </tfoot>
            </table>
        </div>

        <div class="cart-actions">
            <a href="index.php?action=boutique" class="btn btn-secondary">&larr; Continuer mes achats</a>
            <div>
                <button type="submit" class="btn btn-small">Mettre à jour</button>
                <a href="index.php?action=checkout" class="btn">Valider la commande</a>
            </div>
        </div>
    </form>
<?php else: ?>
    <div class="text-center">
        <p>Votre panier est vide.</p>
        <a href="index.php?action=boutique" class="btn">Aller à la boutique</a>
    </div>
<?php endif; ?>