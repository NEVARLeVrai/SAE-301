<div class="container mt-4 text-center">
    <div class="alert alert-success">
        <h2>Merci pour votre commande !</h2>
        <p><?= isset($success) ? $success : "Votre commande a été enregistrée." ?></p>
    </div>
    <p>Vous pouvez retrouver le détail de vos commandes dans votre espace personnel.</p>
    <a href="index.php?action=my_orders" class="btn btn-primary">Voir mes commandes</a>
    <a href="index.php?action=accueil" class="btn btn-secondary">Retour à l'accueil</a>
</div>
