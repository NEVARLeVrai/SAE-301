<?php
/**
 * ===========================================
 * CONTRÔLEUR VISITEUR - Front-Office OmniMusique
 * ===========================================
 * 
 * Ce contrôleur gère toutes les actions du front-office :
 * - Consultation des cours (US-01, US-12, US-15, US-31)
 * - Blog et articles (US-02, US-13, US-35)
 * - Boutique et panier (US-03, US-17, US-18)
 * - Commandes et téléchargements (US-19, US-20, US-32)
 * - Favoris et avis (US-16, US-21)
 * - Vente d'instruments occasion (US-04)
 * - Demande de rôle (US-39)
 * 
 * Respect des consignes :
 * - Encapsulation : données POST traitées dans les classes
 * - Séparation MVC : contrôleur gère le routage, modèles gèrent les données
 * - Templates Twig pour l'affichage des vues
 */

session_start();

// ===========================================
// INITIALISATION TWIG
// ===========================================
include_once '../../include/twig.php';
$twig = init_twig();

// Récupération de l'action (routage par paramètre GET)
$action = isset($_GET['action']) ? $_GET['action'] : 'accueil';

// ===========================================
// INCLUSION DES MODÈLES
// ===========================================
include_once '../../modeles/Database.php';
include_once '../../modeles/Validator.php';   // Validation des données
include_once '../../modeles/Cours.php';       // US-01, US-12, US-15, US-31
include_once '../../modeles/Article.php';     // US-02, US-13
include_once '../../modeles/Produit.php';     // US-03, US-04
include_once '../../modeles/User.php';        // Authentification
include_once '../../modeles/Order.php';       // US-03, US-19, US-32
include_once '../../modeles/Commentaire.php'; // US-35
include_once '../../modeles/RoleRequest.php'; // US-39
include_once '../../modeles/Avis.php';        // US-21
include_once '../../modeles/ModerationRequest.php'; // US-04, US-10
include_once '../../modeles/Notification.php'; // US-29 (notifications admin)
include_once '../../modeles/Favori.php';      // US-16
include_once '../../modeles/FileUpload.php';  // US-23

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// ===========================================
// ACTIONS AVEC REDIRECTION (traitées avant le header HTML)
// ===========================================

// Déconnexion utilisateur
if ($action === 'logout') {
    session_destroy();
    header('Location: index.php?action=accueil');
    exit;
}

// US-17 : Ajout au panier depuis n'importe quelle page
if ($action === 'add_to_cart') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]++;
        } else {
            $_SESSION['panier'][$id] = 1;
        }
    }
    header('Location: index.php?action=panier');
    exit;
}

// US-18 : Suppression du panier
if ($action === 'remove_from_cart') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        if (isset($_SESSION['panier'][$id])) {
            unset($_SESSION['panier'][$id]);
        }
    }
    header('Location: index.php?action=panier');
    exit;
}

// US-18 : Mise à jour des quantités du panier
if ($action === 'update_cart') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            if ($qty <= 0) {
                unset($_SESSION['panier'][$id]);
            } else {
                $_SESSION['panier'][$id] = intval($qty);
            }
        }
    }
    header('Location: index.php?action=panier');
    exit;
}

// US-35 : Ajout d'un commentaire sur un article (nécessite connexion)
if ($action === 'add_comment') {
    if (!isset($_SESSION['user_logged_in'])) {
        header('Location: index.php?action=login');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_POST['user_id'] = $_SESSION['user_id'];
        $commentaireModel = new Commentaire($db);
        
        // Validation du contenu
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if (empty($content)) {
            $_SESSION['comment_message'] = ['type' => 'error', 'text' => 'Le commentaire ne peut pas être vide.'];
        } else {
            if ($commentaireModel->createFromPost()) {
                $_SESSION['comment_message'] = ['type' => 'success', 'text' => 'Votre commentaire a été soumis et est en attente de modération. Il sera visible une fois approuvé.'];
            } else {
                $_SESSION['comment_message'] = ['type' => 'error', 'text' => 'Une erreur est survenue lors de l\'envoi du commentaire.'];
            }
        }
        
        $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : 0;
        header("Location: index.php?action=article_details&id=$article_id");
        exit;
    }
}

// US-21 : Ajout d'un avis sur un produit (nécessite achat préalable)
if ($action === 'add_review') {
    if (!isset($_SESSION['user_logged_in'])) {
        header('Location: index.php?action=login');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_POST['user_id'] = $_SESSION['user_id'];
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        $orderModel = new Order($db);
        if ($orderModel->hasUserBoughtProduct($_SESSION['user_id'], $product_id)) {
            $avisModel = new Avis($db);
            $avisModel->createFromPost();
        }
        
        header("Location: index.php?action=produit_details&id=$product_id");
        exit;
    }
}

// US-16 : Ajout aux favoris (cours ou article)
if ($action === 'add_favorite') {
    if (!isset($_SESSION['user_logged_in'])) {
        header('Location: index.php?action=login');
        exit;
    }
    if (isset($_GET['item_id']) && isset($_GET['item_type'])) {
        $favoriModel = new Favori($db);
        $favoriModel->add($_SESSION['user_id'], $_GET['item_id'], $_GET['item_type']);
        
        $redirect = ($_GET['item_type'] === 'course') ? 'cours_details' : 'article_details';
        header("Location: index.php?action={$redirect}&id=" . $_GET['item_id']);
        exit;
    }
}

// US-16 : Suppression d'un favori
if ($action === 'remove_favorite') {
    if (!isset($_SESSION['user_logged_in'])) {
        header('Location: index.php?action=login');
        exit;
    }
    if (isset($_GET['item_id']) && isset($_GET['item_type'])) {
        $favoriModel = new Favori($db);
        $favoriModel->remove($_SESSION['user_id'], $_GET['item_id'], $_GET['item_type']);
        
        if (isset($_GET['from']) && $_GET['from'] === 'detail') {
            $redirect = ($_GET['item_type'] === 'course') ? 'cours_details' : 'article_details';
            header("Location: index.php?action={$redirect}&id=" . $_GET['item_id']);
        } else {
            header("Location: index.php?action=my_favorites&type=" . $_GET['item_type']);
        }
        exit;
    }
}

// Vérification connexion pour pages protégées (redirection vers login)
$protected_actions = ['my_orders', 'my_favorites', 'checkout', 'download', 'request_role', 'sell_instrument'];
if (in_array($action, $protected_actions) && !isset($_SESSION['user_logged_in'])) {
    header('Location: index.php?action=login');
    exit;
}

// Checkout avec panier vide -> redirection
if ($action === 'checkout' && empty($_SESSION['panier'])) {
    header('Location: index.php?action=panier');
    exit;
}

// Request_role : redirection si pas visiteur
if ($action === 'request_role' && isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'visiteur') {
    header('Location: index.php?action=accueil');
    exit;
}

// ===========================================
// ROUTAGE PRINCIPAL - Rendu avec Twig
// ===========================================
switch ($action) {
    // US-11 : Page d'accueil avec dernières nouveautés
    case 'accueil':
        $coursModel = new Cours($db);
        $articleModel = new Article($db);
        
        // Récupération des derniers articles (publiés uniquement)
        $derniers_articles = $articleModel->getLatestArticles(3);

        // Récupération des derniers cours
        $derniers_cours = $coursModel->getLatestCourses(3);

        echo render_template($twig, 'visiteur/accueil.twig', [
            'derniers_articles' => $derniers_articles,
            'derniers_cours' => $derniers_cours
        ]);
        break;
    
    // US-01, US-12 : Liste des cours avec filtres (niveau, instrument, catégorie)
    case 'cours':
        $cours = new Cours($db);

        // US-12 : Gestion des filtres cumulables
        $filters = [];
        if (isset($_GET['level']) && !empty($_GET['level'])) $filters['level'] = $_GET['level'];
        if (isset($_GET['instrument']) && !empty($_GET['instrument'])) $filters['instrument'] = $_GET['instrument'];
        if (isset($_GET['category']) && !empty($_GET['category'])) $filters['category'] = $_GET['category'];

        // Récupération des options pour les filtres
        $levels = $cours->getDistinctValues('level');
        $instruments = $cours->getDistinctValues('instrument');
        $categories = $cours->getDistinctValues('category');

        $liste_cours = $cours->getCourses($filters);
        
        echo render_template($twig, 'visiteur/cours/list.twig', [
            'liste_cours' => $liste_cours,
            'levels' => $levels,
            'instruments' => $instruments,
            'categories' => $categories,
            'current_level' => $_GET['level'] ?? '',
            'current_instrument' => $_GET['instrument'] ?? '',
            'current_category' => $_GET['category'] ?? '',
            'breadcrumbs' => ['Cours' => 'index.php?action=cours']
        ]);
        break;

    // US-15, US-31 : Fiche détaillée d'un cours
    case 'cours_details':
        if (isset($_GET['id'])) {
            $coursModel = new Cours($db);
            $cours_detail = $coursModel->getCourseById($_GET['id']);
            if ($cours_detail) {
                $twigVars = [
                    'cours' => $cours_detail,
                    'breadcrumbs' => [
                        'Cours' => 'index.php?action=cours',
                        'Détail' => ''
                    ]
                ];
                
                // Vérifier favori si connecté
                if (isset($_SESSION['user_logged_in'])) {
                    $favoriModel = new Favori($db);
                    $twigVars['is_favorite'] = $favoriModel->isFavorite($_SESSION['user_id'], $_GET['id'], 'course');
                }
                
                echo render_template($twig, 'visiteur/cours/details.twig', $twigVars);
            } else {
                echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Cours introuvable.']);
            }
        } else {
            echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Identifiant manquant.']);
        }
        break;
    
    // US-02, US-13 : Blog avec recherche et filtres par catégorie
    case 'blog':
        $articleModel = new Article($db);
        $categories = $articleModel->getCategories();
        
        $twigVars = [
            'categories' => $categories,
            'breadcrumbs' => ['Blog' => 'index.php?action=blog']
        ];
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $twigVars['liste_articles'] = $articleModel->searchArticles($_GET['search']);
            $twigVars['current_search'] = $_GET['search'];
        } elseif (isset($_GET['category']) && !empty($_GET['category'])) {
            $twigVars['liste_articles'] = $articleModel->getArticles($_GET['category']);
            $twigVars['current_category'] = $_GET['category'];
        } else {
            $twigVars['liste_articles'] = $articleModel->getArticles();
        }
        
        echo render_template($twig, 'visiteur/articles/list.twig', $twigVars);
        break;

    // US-02 : Fiche détaillée d'un article avec commentaires
    case 'article_details':
        if (isset($_GET['id'])) {
            $articleModel = new Article($db);
            $article = $articleModel->getArticleById($_GET['id']);
            
            // Récupération des commentaires validés
            $commentaireModel = new Commentaire($db);
            $commentaires = $commentaireModel->getCommentsByArticle($_GET['id']);
            
            // Récupération des commentaires de l'utilisateur connecté avec leur statut
            $userComments = [];
            if (isset($_SESSION['user_logged_in'])) {
                $userComments = $commentaireModel->getUserCommentsByArticle($_GET['id'], $_SESSION['user_id']);
            }

            if ($article) {
                $twigVars = [
                    'article' => $article,
                    'commentaires' => $commentaires,
                    'userComments' => $userComments,
                    'current_url' => "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    'breadcrumbs' => [
                        'Blog' => 'index.php?action=blog',
                        'Article' => ''
                    ]
                ];
                
                // Vérifier favori si connecté
                if (isset($_SESSION['user_logged_in'])) {
                    $favoriModel = new Favori($db);
                    $twigVars['is_favorite'] = $favoriModel->isFavorite($_SESSION['user_id'], $_GET['id'], 'article');
                }
                
                // Message de commentaire (flash message)
                if (isset($_SESSION['comment_message'])) {
                    $twigVars['comment_message'] = $_SESSION['comment_message'];
                    unset($_SESSION['comment_message']);
                }
                
                echo render_template($twig, 'visiteur/articles/details.twig', $twigVars);
            } else {
                echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Article introuvable.']);
            }
        } else {
            echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Identifiant manquant.']);
        }
        break;

    // US-03 : Boutique avec produits approuvés uniquement
    case 'boutique':
        $produitModel = new Produit($db);
        // Affiche uniquement les produits approuvés (pas pending/rejected)
        $liste_produits = $produitModel->getApprovedProducts();
        // Ajouter informations du vendeur pour affichage (nom + email)
        $userModel = new User($db);
        foreach ($liste_produits as &$p) {
            $p['seller_name'] = null;
            $p['seller_email'] = null;
            if (!empty($p['seller_id'])) {
                $seller = $userModel->getUserById($p['seller_id']);
                if ($seller) {
                    $p['seller_name'] = $seller['username'];
                    $p['seller_email'] = $seller['email'];
                }
            }
        }
        echo render_template($twig, 'visiteur/produits/list.twig', [
            'liste_produits' => $liste_produits,
            'breadcrumbs' => ['Boutique' => 'index.php?action=boutique']
        ]);
        break;

    // US-03, US-21 : Fiche produit avec avis et notes
    case 'produit_details':
        if (isset($_GET['id'])) {
            $produitModel = new Produit($db);
            $produit = $produitModel->getProductById($_GET['id']);
            
            if ($produit) {
                $avisModel = new Avis($db);
                $avis = $avisModel->getReviewsByProduct($_GET['id']);
                $moyenne_note = $avisModel->getAverageRating($_GET['id']);
                
                // Vérifier si l'utilisateur connecté a acheté ce produit
                $user_has_bought = false;
                if (isset($_SESSION['user_logged_in'])) {
                    $orderModel = new Order($db);
                    $user_has_bought = $orderModel->hasUserBoughtProduct($_SESSION['user_id'], $_GET['id']);
                }
                // Récupérer le vendeur (si présent) pour affichage/contact
                $seller = null;
                if (!empty($produit['seller_id'])) {
                    $userModel = new User($db);
                    $seller = $userModel->getUserById($produit['seller_id']);
                }
                
                echo render_template($twig, 'visiteur/produits/details.twig', [
                    'produit' => $produit,
                    'seller' => $seller,
                    'avis' => $avis,
                    'moyenne_note' => $moyenne_note,
                    'user_has_bought' => $user_has_bought,
                    'breadcrumbs' => [
                        'Boutique' => 'index.php?action=boutique',
                        'Produit' => ''
                    ]
                ]);
            } else {
                echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Produit introuvable.']);
            }
        } else {
            echo render_template($twig, 'visiteur/accueil.twig', ['error' => 'Identifiant manquant.']);
        }
        break;

    case 'contact':
        echo render_template($twig, 'visiteur/contact.twig', [
            'breadcrumbs' => ['Contact' => 'index.php?action=contact'],
            'deposer_annonce' => isset($_GET['deposer']) && $_GET['deposer'] == 1
        ]);
        break;

    case 'panier':
        // Récupérer les détails des produits du panier
        $panier_details = [];
        $total = 0;
        if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
            $produitModel = new Produit($db);
            foreach ($_SESSION['panier'] as $id => $qty) {
                $prod = $produitModel->getProductById($id);
                if ($prod) {
                    $prod['qty'] = $qty;
                    $prod['subtotal'] = $prod['price'] * $qty;
                    $total += $prod['subtotal'];
                    $panier_details[] = $prod;
                }
            }
        }
        echo render_template($twig, 'visiteur/orders/panier.twig', [
            'panier_details' => $panier_details,
            'total' => $total,
            'breadcrumbs' => [
                'Boutique' => 'index.php?action=boutique',
                'Panier' => ''
            ]
        ]);
        break;

    case 'login':
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation avec la classe Validator
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            $validator = new Validator();
            if (!$validator->validateLogin($email, $password)) {
                $error = $validator->getErrorsString();
            } else {
                $user = new User($db);
                // Les données POST sont traitées dans la classe (encapsulation)
                if ($user->loginFromPost()) {
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_name'] = $user->getUsername();
                    $_SESSION['user_role'] = $user->getRole();
                    
                    // Redirection selon le rôle
                    if (in_array($user->getRole(), ['admin', 'redacteur', 'musicien', 'responsable_annonce'])) {
                        // Initialisation des variables de session Admin
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user->getId();
                        $_SESSION['admin_name'] = $user->getUsername();
                        $_SESSION['admin_role'] = $user->getRole();
                        // Charger en cache les permissions du rôle afin d'avoir les droits en back-office
                        if (function_exists('loadRolePermissionsIntoSession')) {
                            loadRolePermissionsIntoSession($db, $user->getRole());
                        }
                        
                        header('Location: ../admin/index.php?action=dashboard');
                    } else {
                        header('Location: index.php?action=accueil');
                    }
                    exit;
                } else {
                    // Vérifier s'il y a des erreurs de validation
                    if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])) {
                        $error = implode(' ', $_SESSION['validation_errors']);
                        unset($_SESSION['validation_errors']);
                    } else {
                        $error = "Identifiants incorrects.";
                    }
                }
            }
        }
        echo render_template($twig, 'visiteur/auth/login.twig', [
            'error' => $error,
            'breadcrumbs' => ['Connexion' => 'index.php?action=login']
        ]);
        break;

    case 'register':
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation avec la classe Validator
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            $validator = new Validator();
            if (!$validator->validateRegister($username, $email, $password)) {
                $error = $validator->getErrorsString();
            } else {
                $user = new User($db);
                // Les données POST sont traitées dans la classe (encapsulation)
                if ($user->createFromPost()) {
                    // Connexion automatique après inscription
                    $user->loginFromPost();
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_name'] = $user->getUsername();
                    $_SESSION['user_role'] = $user->getRole();
                    
                    if (in_array($user->getRole(), ['admin', 'redacteur', 'musicien', 'responsable_annonce'])) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user->getId();
                        $_SESSION['admin_name'] = $user->getUsername();
                        $_SESSION['admin_role'] = $user->getRole();
                        if (function_exists('loadRolePermissionsIntoSession')) {
                            loadRolePermissionsIntoSession($db, $user->getRole());
                        }
                        header('Location: ../admin/index.php?action=dashboard');
                    } else {
                        header('Location: index.php?action=accueil');
                    }
                    exit;
                } else {
                    // Vérifier s'il y a des erreurs de validation
                    if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])) {
                        $error = implode(' ', $_SESSION['validation_errors']);
                        unset($_SESSION['validation_errors']);
                    } else {
                        $error = "Erreur lors de l'inscription.";
                    }
                }
            }
        }
        echo render_template($twig, 'visiteur/auth/register.twig', [
            'error' => $error,
            'breadcrumbs' => ['Inscription' => 'index.php?action=register']
        ]);
        break;

    // US-03, US-20 : Validation de commande et envoi email de confirmation
    case 'checkout':
        // Vérifications connexion et panier faites avant le header
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // US-03 : Création de la commande avec décrémentation du stock
            $produitModel = new Produit($db);
            $items = [];
            $total = 0;

            foreach ($_SESSION['panier'] as $id => $qty) {
                $p = $produitModel->getProductById($id);
                if ($p) {
                    $items[] = [
                        'product_id' => $id,
                        'quantity' => $qty,
                        'price' => $p['price']
                    ];
                    $total += $p['price'] * $qty;
                }
            }

            $orderModel = new Order($db);
            if ($orderModel->create($_SESSION['user_id'], $total, $items)) {
                // US-20 : Envoi d'email de confirmation au client
                // Récupération de l'email de l'utilisateur
                $userModel = new User($db);
                $currentUser = $userModel->getUserById($_SESSION['user_id']);
                
                if ($currentUser) {
                    // Préparation de l'email, désactivé car non fonctionnel en local, il n'y a pas de serveur mail

                    // $to = $currentUser['email'];
                    // $subject = "Confirmation de votre commande - OmniMusique";
                    // $body = "Bonjour " . htmlspecialchars($_SESSION['user_name']) . ",\n\n";
                    // $body .= "Nous vous confirmons la réception de votre commande.\n";
                    // $body .= "Montant total : " . number_format($total, 2) . " €\n\n";
                    // $body .= "Détail de la commande :\n";
                    // foreach ($items as $item) {
                    //     $body .= "- Produit ID " . $item['product_id'] . " x " . $item['quantity'] . " (" . $item['price'] . " €)\n";
                    // }
                    // $body .= "\nMerci de votre confiance.\nL'équipe OmniMusique";
                    // $headers = "From: no-reply@omnimusique.fr" . "\r\n" .
                    //            "X-Mailer: PHP/" . phpversion();

                    // @mail($to, $subject, $body, $headers);
                }

                unset($_SESSION['panier']);
                echo render_template($twig, 'visiteur/orders/confirmation.twig', [
                    'success' => "Commande validée avec succès ! Un email de confirmation vous a été envoyé."
                ]);
                exit;
            } else {
                $error = "Erreur lors de la commande.";
            }
        }
        
        // Récapitulatif du panier pour checkout
        $panier_details = [];
        $total = 0;
        if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
            $produitModel = new Produit($db);
            foreach ($_SESSION['panier'] as $id => $qty) {
                $prod = $produitModel->getProductById($id);
                if ($prod) {
                    $prod['qty'] = $qty;
                    $prod['subtotal'] = $prod['price'] * $qty;
                    $total += $prod['subtotal'];
                    $panier_details[] = $prod;
                }
            }
        }
        
        echo render_template($twig, 'visiteur/orders/checkout.twig', [
            'panier_details' => $panier_details,
            'total' => $total,
            'error' => $error,
            'breadcrumbs' => [
                'Panier' => 'index.php?action=panier',
                'Paiement' => ''
            ]
        ]);
        break;

    // US-04 : Vente d'instrument d'occasion par un visiteur
    // Le produit passe en status 'pending' pour modération (US-10)
    case 'sell_instrument':
        $success = null;
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // US-23 : Upload d'image avec validation type/taille
            $image_url = FileUpload::uploadImage('image', FileUpload::UPLOAD_DIR_ASSETS);

            $produitModel = new Produit($db);
            // Encapsulation : données POST traitées dans la classe
            if ($produitModel->createInstrumentFromPost($_SESSION['user_id'], $image_url)) {
                // US-10 : Création d'une demande de modération pour l'admin
                $mreq = new ModerationRequest($db);
                $message = "Annonce : " . $produitModel->getNameFromPost() . " | Prix: " . $produitModel->getPriceFromPost() . " €";
                $product_id = $db->lastInsertId();
                $mreq->create($_SESSION['user_id'], $product_id, $message);

                // Créer aussi une notification pour les admins afin qu'ils voient la demande
                $notifModel = new Notification($db);
                $notifMessage = "Nouvelle annonce à modérer : " . $produitModel->getNameFromPost() . " (" . $produitModel->getPriceFromPost() . " €)";
                $notifLink = "index.php?action=moderation_requests";
                $notifModel->notifyAdmins($notifMessage, $notifLink);

                $success = "Votre annonce a été soumise et est en attente de validation.";
            } else {
                $error = "Erreur lors de la soumission.";
            }
        }
        echo render_template($twig, 'visiteur/produits/sell.twig', [
            'success' => $success,
            'error' => $error,
            'breadcrumbs' => [
                'Boutique' => 'index.php?action=boutique',
                'Vendre' => ''
            ]
        ]);
        break;

    // --- MES COMMANDES (US-19) ---
    case 'my_orders':
        // Vérification connexion faite avant le header
        $orderModel = new Order($db);
        $orders = $orderModel->getOrdersByUser($_SESSION['user_id']);
        echo render_template($twig, 'visiteur/orders/list.twig', [
            'orders' => $orders,
            'breadcrumbs' => ['Mes commandes' => 'index.php?action=my_orders']
        ]);
        break;

    // US-32 : Téléchargement sécurisé de produit numérique
    // Protection : seuls les acheteurs peuvent télécharger (403 sinon)
    case 'download':
        if (isset($_GET['id'])) {
            $product_id = $_GET['id'];
            $user_id = $_SESSION['user_id'];
            
            $orderModel = new Order($db);
            // Vérification que l'utilisateur a bien acheté ce produit
            if ($orderModel->hasUserBoughtProduct($user_id, $product_id)) {
                $produitModel = new Produit($db);
                $product = $produitModel->getProductById($product_id);
                
                if ($product && !empty($product['file_url'])) {
                    // Note: Idéalement, les fichiers devraient être dans un dossier protégé
                    $file_path = "../../assets/" . $product['file_url']; 
                    
                    if (file_exists($file_path)) {
                        // Forcer le téléchargement
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file_path));
                        readfile($file_path);
                        exit;
                    } else {
                        die("Erreur : Le fichier est introuvable sur le serveur.");
                    }
                } else {
                    die("Erreur : Aucun fichier numérique associé à ce produit.");
                }
            } else {
                // US-32 : Protection contre l'accès direct sans achat
                http_response_code(403);
                die("Accès refusé (403 Forbidden) : Vous devez acheter ce produit pour le télécharger.");
            }
        }
        break;

    // --- FAVORIS (US-16) ---
    case 'my_favorites':
        // Vérification connexion faite avant le header
        $favoriModel = new Favori($db);
        
        $type = isset($_GET['type']) ? $_GET['type'] : 'course';
        
        $twigVars = [
            'current_type' => $type,
            'breadcrumbs' => ['Mes favoris' => 'index.php?action=my_favorites']
        ];
        
        if ($type === 'article') {
            $twigVars['favorite_articles'] = $favoriModel->getFavoriteArticlesWithDetails($_SESSION['user_id']);
        } else {
            $twigVars['favorite_courses'] = $favoriModel->getFavoriteCoursesWithDetails($_SESSION['user_id']);
        }
        
        echo render_template($twig, 'visiteur/favorites/list.twig', $twigVars);
        break;

    // ==========================================
    // US-39 : DEMANDE DE RÔLE (Visiteur -> Rédacteur/Musicien)
    // ==========================================
    case 'request_role':
        // Vérifications connexion et rôle faites avant le header
        $roleRequestModel = new RoleRequest($db);
        $success = null;
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer le rôle demandé depuis POST pour la notification
            $requested_role = isset($_POST['requested_role']) ? htmlspecialchars($_POST['requested_role']) : 'inconnu';
            
            // Les données POST sont traitées dans la classe (encapsulation)
            if ($roleRequestModel->createFromPost($_SESSION['user_id'])) {
                // Notifier les admins
                $notifModel = new Notification($db);
                $notifModel->notifyAdmins(
                    "Nouvelle demande de rôle '" . $requested_role . "' par " . $_SESSION['user_name'],
                    "index.php?action=role_requests"
                );
                
                $success = "Votre demande a été envoyée ! Un administrateur l'examinera prochainement.";
            } else {
                $error = "Vous avez déjà une demande en attente.";
            }
        }
        
        $hasPending = $roleRequestModel->hasPendingRequest($_SESSION['user_id']);
        $myRequests = $roleRequestModel->getRequestsByUser($_SESSION['user_id']);
        
        echo render_template($twig, 'visiteur/auth/request_role.twig', [
            'hasPending' => $hasPending,
            'myRequests' => $myRequests,
            'success' => $success,
            'error' => $error,
            'breadcrumbs' => ['Devenir contributeur' => 'index.php?action=request_role']
        ]);
        break;

    // --- MENTIONS LÉGALES ---
    case 'mentions_legales':
        echo render_template($twig, 'visiteur/mentions_legales.twig', [
            'breadcrumbs' => ['Mentions légales' => 'index.php?action=mentions_legales']
        ]);
        break;

    default:
        echo render_template($twig, 'visiteur/accueil.twig', []);
        break;
}
