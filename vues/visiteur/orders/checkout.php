<div class="container mt-4">
    <h2>Validation de la commande</h2>
    
    <div class="row">
        <div class="col-md-8">
            <h4>Récapitulatif</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['panier'] as $id => $qty): 
                        $produitModel = new Produit($db);
                        $p = $produitModel->getProductById($id);
                        if ($p):
                            $subtotal = $p['price'] * $qty;
                            $total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $qty ?></td>
                            <td><?= $subtotal ?> €</td>
                        </tr>
                    <?php endif; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end"><strong>Total à payer :</strong></td>
                        <td><strong><?= $total ?> €</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-4">
            <h4>Paiement</h4>
            <p>Ceci est une simulation de paiement.</p>
            <form method="POST" action="index.php?action=checkout">
                <div class="mb-3">
                    <label class="form-label">Numéro de carte (Simulation)</label>
                    <input type="text" class="form-control" value="**** **** **** 1234" disabled>
                </div>
                <button type="submit" class="btn btn-success w-100">Payer et Commander</button>
            </form>
        </div>
    </div>
</div>
