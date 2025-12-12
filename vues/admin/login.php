<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Connexion</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../../assets/system/favicon.svg">
    <link rel="apple-touch-icon" href="../../assets/system/favicon.svg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <h2 class="text-center mb-4">
            <i class="bi bi-shield-lock me-2"></i>Administration
        </h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=login" method="post">
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope me-2"></i>Email
                </label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock me-2"></i>Mot de passe
                </label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </button>
        </form>
        <div class="mt-3 text-center">
            <a href="../../index.php">
                <i class="bi bi-arrow-left me-2"></i>Retour au site
            </a>
        </div>
    </div>
</body>
</html>
