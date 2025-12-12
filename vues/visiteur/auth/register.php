<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-plus me-2"></i>Inscription
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=register" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-2"></i>Nom d'utilisateur
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Mot de passe
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="role" name="role" value="musicien">
                            <label class="form-check-label" for="role">
                                <i class="bi bi-music-note-beamed me-1"></i>Je suis un musicien (Je veux vendre mes créations)
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus me-2"></i>S'inscrire
                        </button>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Déjà un compte ? <a href="index.php?action=login"><i class="bi bi-box-arrow-in-right me-1"></i>Se connecter</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
