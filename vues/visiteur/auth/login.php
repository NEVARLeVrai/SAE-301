<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Connexion
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=login" method="post">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Pas encore de compte ? <a href="index.php?action=register"><i class="bi bi-person-plus me-1"></i>S'inscrire</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
