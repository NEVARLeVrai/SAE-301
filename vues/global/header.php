<?php
// Définir le chemin de base - les deux contrôleurs sont dans controllers/xxx/
// donc le chemin vers la racine du projet est toujours ../../
$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OmniMusique<?php echo (isset($isAdmin) && $isAdmin) ? ' - Admin' : ''; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath; ?>assets/system/favicon.svg">
    <link rel="apple-touch-icon" href="<?php echo $basePath; ?>assets/system/favicon.svg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body>
    <header class="<?php echo (isset($isAdmin) && $isAdmin) ? 'admin-header' : ''; ?>">
        <div class="container">
            <nav class="d-flex align-items-center">
                <div class="logo me-auto">
                    <?php if (isset($isAdmin) && $isAdmin): ?>
                        <a href="<?php echo $basePath; ?>controllers/visiteur/index.php?action=accueil" class="text-decoration-none fs-5 fw-bold text-white"><i class="bi bi-music-note-beamed me-2"></i>OmniMusique</a>
                    <?php else: ?>
                        <a href="index.php?action=accueil" class="text-decoration-none fs-5 fw-bold text-white"><i class="bi bi-music-note-beamed me-2"></i>OmniMusique</a>
                    <?php endif; ?>
                </div>
                <ul class="nav mx-auto">
                    <?php if (isset($isAdmin) && $isAdmin): ?>
                        <!-- Menu Admin -->
                        <li class="nav-item"><a class="nav-link" href="index.php?action=dashboard"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        
                        <!-- Dropdown Gestion Contenu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-collection me-1"></i> Contenu</a>
                            <ul class="dropdown-menu">
                                <?php if ($_SESSION['admin_role'] !== 'musicien'): ?>
                                    <li><a class="dropdown-item" href="index.php?action=articles"><i class="bi bi-file-text me-1"></i> Articles</a></li>
                                    <?php if ($_SESSION['admin_role'] === 'admin'): ?>
                                        <li><a class="dropdown-item" href="index.php?action=cours"><i class="bi bi-music-note-list me-1"></i> Cours</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="index.php?action=comments"><i class="bi bi-chat-left-text me-1"></i> Commentaires</a></li>
                                <?php endif; ?>
                                <?php if ($_SESSION['admin_role'] !== 'redacteur'): ?>
                                    <li><a class="dropdown-item" href="index.php?action=produits"><i class="bi bi-bag me-1"></i> Produits</a></li>
                                <?php endif; ?>
                                <?php if ($_SESSION['admin_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="index.php?action=tags"><i class="bi bi-tags me-1"></i> Tags</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <?php if ($_SESSION['admin_role'] === 'admin'): ?>
                            <!-- Dropdown Administration -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-gear me-1"></i> Administration</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?action=orders"><i class="bi bi-receipt me-1"></i> Commandes</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=users"><i class="bi bi-people me-1"></i> Utilisateurs</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=role_requests"><i class="bi bi-person-badge me-1"></i> Demandes de rôle</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=moderation_requests"><i class="bi bi-exclamation-triangle me-1"></i> Modération produits</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?action=reports"><i class="bi bi-bar-chart me-1"></i> Rapports</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=permissions"><i class="bi bi-shield-lock me-1"></i> Permissions</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=configurations"><i class="bi bi-sliders me-1"></i> Config</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="index.php?action=notifications"><i class="bi bi-bell me-1"></i> Notifs</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-item"><a class="nav-link logout-link" href="index.php?action=logout"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</a></li>
                    
                    <?php else: ?>
                        <!-- Menu Visiteur -->
                        <li class="nav-item"><a class="nav-link" href="index.php?action=cours"><i class="bi bi-music-note-beamed me-1"></i> Cours</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=blog"><i class="bi bi-journal-text me-1"></i> Blog</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=boutique"><i class="bi bi-shop me-1"></i> Boutique</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?action=contact"><i class="bi bi-envelope me-1"></i> Contact</a></li>
                        
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="color: #a78bfa !important;">
                                    <i class="bi bi-person-circle me-1"></i> Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?action=my_orders"><i class="bi bi-receipt me-1"></i> Mes commandes</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=my_favorites"><i class="bi bi-heart me-1"></i> Favoris</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=sell_instrument"><i class="bi bi-megaphone me-1"></i> Vendre</a></li>
                                    <?php if ($_SESSION['user_role'] === 'visiteur'): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-info" href="index.php?action=request_role"><i class="bi bi-person-plus me-1"></i> Devenir contributeur</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="index.php?action=logout"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-primary" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i>Compte
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?action=login"><i class="bi bi-box-arrow-in-right me-2"></i>Connexion</a></li>
                                    <li><a class="dropdown-item" href="index.php?action=register"><i class="bi bi-person-plus me-2"></i>Inscription</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <?php if (!isset($isAdmin) || !$isAdmin): ?>
                    <div class="ms-auto d-flex align-items-center">
                        <a class="nav-link text-dark" href="index.php?action=panier"><i class="bi bi-cart me-1"></i> Panier</a>
                        <a class="btn btn-outline-secondary btn-sm ms-2" href="<?php echo $basePath; ?>controllers/admin/index.php"><i class="bi bi-shield-lock"></i> Admin</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <!-- US-31 : Fil d'Ariane (Breadcrumb) -->
    <?php
    // Génération automatique du fil d'Ariane
    $breadcrumbs = [];
    $currentAction = isset($_GET['action']) ? $_GET['action'] : 'accueil';
    
    // Configuration des breadcrumbs selon l'action
    $breadcrumbConfig = [
        'accueil' => ['Accueil'],
        'cours' => ['Accueil' => 'index.php?action=accueil', 'Cours'],
        'cours_details' => ['Accueil' => 'index.php?action=accueil', 'Cours' => 'index.php?action=cours', 'Détail du cours'],
        'blog' => ['Accueil' => 'index.php?action=accueil', 'Blog'],
        'article_details' => ['Accueil' => 'index.php?action=accueil', 'Blog' => 'index.php?action=blog', 'Article'],
        'boutique' => ['Accueil' => 'index.php?action=accueil', 'Boutique'],
        'produit_details' => ['Accueil' => 'index.php?action=accueil', 'Boutique' => 'index.php?action=boutique', 'Produit'],
        'panier' => ['Accueil' => 'index.php?action=accueil', 'Boutique' => 'index.php?action=boutique', 'Panier'],
        'checkout' => ['Accueil' => 'index.php?action=accueil', 'Panier' => 'index.php?action=panier', 'Paiement'],
        'my_orders' => ['Accueil' => 'index.php?action=accueil', 'Mes commandes'],
        'my_favorites' => ['Accueil' => 'index.php?action=accueil', 'Mes favoris'],
        'contact' => ['Accueil' => 'index.php?action=accueil', 'Contact'],
        'mentions_legales' => ['Accueil' => 'index.php?action=accueil', 'Mentions légales'],
        'login' => ['Accueil' => 'index.php?action=accueil', 'Connexion'],
        'register' => ['Accueil' => 'index.php?action=accueil', 'Inscription'],
        'sell_instrument' => ['Accueil' => 'index.php?action=accueil', 'Boutique' => 'index.php?action=boutique', 'Vendre un instrument'],
        'request_role' => ['Accueil' => 'index.php?action=accueil', 'Devenir contributeur'],
        // Admin breadcrumbs
        'dashboard' => ['Dashboard'],
        'articles' => ['Dashboard' => 'index.php?action=dashboard', 'Articles'],
        'produits' => ['Dashboard' => 'index.php?action=dashboard', 'Produits'],
        'users' => ['Dashboard' => 'index.php?action=dashboard', 'Utilisateurs'],
        'orders' => ['Dashboard' => 'index.php?action=dashboard', 'Commandes'],
        'reports' => ['Dashboard' => 'index.php?action=dashboard', 'Rapports'],
        'permissions' => ['Dashboard' => 'index.php?action=dashboard', 'Permissions'],
        'role_requests' => ['Dashboard' => 'index.php?action=dashboard', 'Demandes de rôle'],
        'configurations' => ['Dashboard' => 'index.php?action=dashboard', 'Configurations'],
        'moderation_requests' => ['Dashboard' => 'index.php?action=dashboard', 'Modération produits'],
        'notifications' => ['Dashboard' => 'index.php?action=dashboard', 'Notifications'],
        'tags' => ['Dashboard' => 'index.php?action=dashboard', 'Tags'],
        'comments' => ['Dashboard' => 'index.php?action=dashboard', 'Commentaires'],
    ];
    
    if (isset($breadcrumbConfig[$currentAction]) && $currentAction !== 'accueil' && $currentAction !== 'dashboard'):
    ?>
    <nav aria-label="breadcrumb" class="container mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?action=<?php echo (isset($isAdmin) && $isAdmin) ? 'dashboard' : 'accueil'; ?>"><i class="bi bi-house"></i></a></li>
            <?php 
            $items = $breadcrumbConfig[$currentAction];
            $count = count($items);
            $i = 0;
            foreach ($items as $label => $link): 
                $i++;
                if (is_numeric($label)) {
                    // Dernier élément (actif)
                    echo '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($link) . '</li>';
                } else {
                    // Élément avec lien
                    if ($i < $count) {
                        echo '<li class="breadcrumb-item"><a href="' . $link . '">' . htmlspecialchars($label) . '</a></li>';
                    }
                }
            endforeach; 
            ?>
        </ol>
    </nav>
    <?php endif; ?>
    
    <main class="container py-4">
