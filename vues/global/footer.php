    </main>
    <footer class="site-footer py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                    <h5 class="mb-3 text-white">OmniMusique</h5>
                    <p class="small footer-text">Votre plateforme musicale pour apprendre, découvrir et acheter.</p>
                </div>
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <h5 class="mb-3 text-white">Suivez-nous</h5>
                    <a href="#" class="text-white me-3 text-decoration-none"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white me-3 text-decoration-none"><i class="bi bi-twitter-x fs-5"></i></a>
                    <a href="#" class="text-white me-3 text-decoration-none"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white text-decoration-none"><i class="bi bi-linkedin fs-5"></i></a>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <h5 class="mb-3 text-white">Informations</h5>
                    <ul class="list-unstyled">
                        <?php if (isset($isAdmin) && $isAdmin): ?>
                            <li><a href="<?php echo $basePath; ?>controllers/visiteur/index.php?action=mentions_legales" class="footer-link">Mentions légales</a></li>
                            <li><a href="<?php echo $basePath; ?>controllers/visiteur/index.php?action=contact" class="footer-link">Contact</a></li>
                        <?php else: ?>
                            <li><a href="index.php?action=mentions_legales" class="footer-link">Mentions légales</a></li>
                            <li><a href="index.php?action=contact" class="footer-link">Contact</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr class="my-3 border-light opacity-25">
            <p class="mb-0 text-center text-white small">&copy; 2025 - OmniMusique SAE 301. Tous droits réservés.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
