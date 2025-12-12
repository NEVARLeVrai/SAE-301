<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniMusique - Choix du Rôle</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/system/favicon.svg">
    <link rel="apple-touch-icon" href="assets/system/favicon.svg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1 class="text-center mt-2">Bienvenue sur OmniMusique</h1>
    <p class="text-center">Veuillez choisir votre espace :</p>
    
    <div class="role-selection">
        <div class="role-card">
            <i class="bi bi-house-door-fill"></i>
            <h2>Espace Visiteur</h2>
            <p>Accédez aux cours, au blog et à la boutique.</p>
            <a href="controllers/visiteur/index.php">Entrer</a>
        </div>
        
        <div class="role-card">
            <i class="bi bi-shield-lock-fill"></i>
            <h2>Espace Admin</h2>
            <p>Gérez le site et les contenus.</p>
            <a href="controllers/admin/index.php">Connexion</a>
        </div>
    </div>
</body>
</html>
