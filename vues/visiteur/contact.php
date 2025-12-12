<div class="container mt-4">
    <h1>Contactez-nous</h1>
    
    <div class="row" style="align-items: flex-start;">
        <div class="col-md-6">
            <h3>Envoyez-nous un message</h3>
            <form id="contactForm">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="p-3 btn btn-primary">Envoyer (Ouvrir Client Mail)</button>
            </form>
        </div>
        <div class="col-md-6">
            <h3 style="visibility: hidden; margin-bottom: 1rem;">Placeholder</h3>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Vendre un instrument ?</h5>
                    <p class="card-text">Vous souhaitez proposer un instrument de musique d'occasion à la vente ? Utilisez notre formulaire dédié.</p>
                    <a href="index.php?action=sell_instrument" class="btn btn-success">Déposer une annonce</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Nos coordonnées</h5>
                    <p class="card-text">
                        <strong>OmniMusique</strong><br>
                        123 Rue de la Musique<br>
                        75000 Paris<br>
                        <br>
                        <strong>Email:</strong> <a href="mailto:contact@omnimusique.fr">contact@omnimusique.fr</a><br>
                        <strong>Tél:</strong> 01 23 45 67 89
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var nom = document.getElementById('nom').value;
    var email = document.getElementById('email').value;
    var message = document.getElementById('message').value;
    
    var subject = "Contact OmniMusique de " + nom;
    var body = "Nom: " + nom + "\nEmail: " + email + "\n\nMessage:\n" + message;
    
    var mailtoLink = "mailto:contact@omnimusique.fr" + 
                     "?subject=" + encodeURIComponent(subject) + 
                     "&body=" + encodeURIComponent(body);
                     
    window.location.href = mailtoLink;
});
</script>
