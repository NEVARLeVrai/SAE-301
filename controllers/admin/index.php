<?php
/**
 * ===========================================
 * CONTRÔLEUR ADMIN - Back-Office OmniMusique
 * ===========================================
 * 
 * Ce contrôleur gère toutes les actions du back-office pour :
 * - Administrateurs : accès complet (US-08, US-09, US-26, US-27, US-28, US-29, US-30, US-37, US-38, US-39)
 * - Rédacteurs : gestion de leurs articles (US-06, US-07, US-24, US-25, US-36)
 * - Musiciens : gestion de leurs produits (US-05, US-33, US-34)
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

// ===========================================
// INCLUSION DES MODÈLES (Connexion BDD unique)
// ===========================================
include_once '../../modeles/Database.php';
include_once '../../modeles/Validator.php';  // Validation des données
include_once '../../modeles/Article.php';    // US-06, US-07, US-25
include_once '../../modeles/Cours.php';      // Gestion des cours
include_once '../../modeles/Produit.php';    // US-05, US-34
include_once '../../modeles/User.php';       // US-26
include_once '../../modeles/Configuration.php'; // US-09, US-38
include_once '../../modeles/Order.php';      // US-08, US-33
include_once '../../modeles/Tag.php';        // US-28
include_once '../../modeles/Commentaire.php'; // US-24
include_once '../../modeles/Notification.php'; // US-29
include_once '../../modeles/RoleRequest.php'; // US-39
include_once '../../modeles/ModerationRequest.php'; // US-10
include_once '../../modeles/Permission.php'; // US-30
include_once '../../modeles/Rapport.php';    // US-27
include_once '../../modeles/FileUpload.php'; // US-23

// Connexion à la base de données (singleton pattern)
$database = new Database();
$db = $database->getConnection();

// Récupération de l'action (routage par paramètre GET)
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

// ===========================================
// AUTHENTIFICATION - Connexion au back-office
// ===========================================
if ($action == 'login') {
    // Si déjà connecté en admin, rediriger vers le dashboard
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        header('Location: index.php?action=dashboard');
        exit;
    }
    
    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation avec la classe Validator
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        $validator = new Validator();
        if (!$validator->validateLogin($email, $password)) {
            $error = $validator->getErrorsString();
            echo $twig->render('admin/login.twig', ['error' => $error]);
        } else {
            // Les données POST sont traitées dans la classe (encapsulation)
            $user = new User($db);

            if ($user->loginFromPost()) {
                // Vérification du rôle
                if (in_array($user->getRole(), ['admin', 'redacteur', 'musicien'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user->getId();
                    $_SESSION['admin_name'] = $user->getUsername();
                    $_SESSION['admin_role'] = $user->getRole();
                    header('Location: index.php?action=dashboard');
                    exit;
                } else {
                    $error = "Accès refusé. Vous n'avez pas les droits d'administration.";
                    echo $twig->render('admin/login.twig', ['error' => $error]);
                }
            } else {
                // Vérifier s'il y a des erreurs de validation
                if (isset($_SESSION['validation_errors']) && !empty($_SESSION['validation_errors'])) {
                    $error = implode(' ', $_SESSION['validation_errors']);
                    unset($_SESSION['validation_errors']);
                } else {
                    $error = "Identifiants incorrects.";
                }
                echo $twig->render('admin/login.twig', ['error' => $error]);
            }
        }
    } else {
        // Affichage du formulaire de login (PAS DE HEADER GLOBAL)
        echo $twig->render('admin/login.twig', []);
    }
    exit; // On arrête le script ici pour ne pas charger le reste
}

// --- LOGIQUE DE DÉCONNEXION ---
if ($action == 'logout') {
    session_destroy();
    header('Location: ../../index.php');
    exit;
}

// --- VÉRIFICATION DE SÉCURITÉ ---
// Si on n'est pas connecté, on renvoie au login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php?action=login');
    exit;
}

// ===========================================
// ACTIONS AVEC REDIRECTION (traitées avant le header HTML)
// ===========================================
// Ces actions modifient des données et redirigent ensuite vers une page
// Elles doivent être traitées AVANT l'envoi du header HTML
$redirectActions = [
    'delete_user',           // US-26 : Suppression utilisateur
    'delete_article',        // US-36 : Suppression article (soft delete)
    'delete_produit',        // US-34 : Suppression produit (soft delete)
    'delete_cours',          // Suppression cours
    'delete_comment',        // US-24 : Suppression commentaire
    'delete_notification',   // US-29 : Suppression notification
    'mark_notification_read', // US-29 : Marquer notification comme lue
    'mark_all_notifications_read', // US-29 : Tout marquer comme lu
    'approve_comment',       // US-24 : Approuver commentaire
    'reject_comment',        // US-24 : Rejeter commentaire
    'approve_product',       // US-10 : Approuver produit (modération)
    'reject_product',        // US-10 : Rejeter produit (modération)
    'approve_role_request',  // US-39 : Approuver demande de rôle
    'reject_role_request',   // US-39 : Rejeter demande de rôle
    'approve_mreq',          // US-10 : Approuver demande modération
    'reject_mreq'            // US-10 : Rejeter demande modération
];

if (in_array($action, $redirectActions)) {
    switch ($action) {
        // US-26 : Suppression d'un utilisateur (Admin uniquement)
        case 'delete_user':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                if ($_GET['id'] == $_SESSION['admin_id']) {
                    die("Impossible de supprimer son propre compte.");
                }
                $userModel = new User($db);
                $userModel->delete($_GET['id']);
            }
            header('Location: index.php?action=users');
            exit;

        // US-36 : Suppression d'un article (soft delete)
        // Rédacteur : uniquement SES articles | Admin : tous (US-37)
        case 'delete_article':
            if (isset($_GET['id'])) {
                $articleModel = new Article($db);
                $existingArticle = $articleModel->getArticleById($_GET['id']);
                
                // Vérification des droits (Rédacteur = ses articles uniquement)
                if ($_SESSION['admin_role'] === 'redacteur') {
                    if (!$existingArticle || $existingArticle['author_id'] != $_SESSION['admin_id']) {
                        die("Accès refusé : Vous ne pouvez supprimer que vos propres articles.");
                    }
                }
                
                // Supprimer les fichiers liés (image)
                if ($existingArticle && !empty($existingArticle['image_url'])) {
                    FileUpload::deleteFile($existingArticle['image_url']);
                }
                $articleModel->delete($_GET['id']);
            }
            header('Location: index.php?action=articles');
            exit;

        // US-34 : Suppression d'un produit (soft delete)
        // Musicien : uniquement SES produits | Admin : tous (US-37)
        case 'delete_produit':
            if ($_SESSION['admin_role'] === 'redacteur') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $produitModel = new Produit($db);
                $existingProduct = $produitModel->getProductById($_GET['id']);
                
                // Vérification des droits (Musicien = ses produits uniquement)
                if ($_SESSION['admin_role'] === 'musicien') {
                    if (!$existingProduct || $existingProduct['seller_id'] != $_SESSION['admin_id']) {
                        die("Accès refusé : Vous ne pouvez supprimer que vos propres produits.");
                    }
                }
                
                // Supprimer les fichiers liés (image, file)
                if ($existingProduct) {
                    if (!empty($existingProduct['image_url'])) {
                        FileUpload::deleteFile($existingProduct['image_url']);
                    }
                    if (!empty($existingProduct['file_url'])) {
                        FileUpload::deleteFile($existingProduct['file_url']);
                    }
                }
                $produitModel->delete($_GET['id']);
            }
            header('Location: index.php?action=produits');
            exit;

        case 'delete_cours':
            if ($_SESSION['admin_role'] === 'musicien') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $coursModel = new Cours($db);
                $coursModel->delete($_GET['id']);
            }
            header('Location: index.php?action=cours');
            exit;

        case 'delete_comment':
            if ($_SESSION['admin_role'] === 'musicien') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $commentModel = new Commentaire($db);
                // Vérification pour rédacteur : seulement ses articles
                if ($_SESSION['admin_role'] === 'redacteur') {
                    if (!$commentModel->isCommentOnAuthorArticle($_GET['id'], $_SESSION['admin_id'])) {
                        die("Accès refusé : Vous ne pouvez supprimer que les commentaires sur vos propres articles.");
                    }
                }
                $commentModel->delete($_GET['id']);
            }
            header('Location: index.php?action=comments');
            exit;

        case 'approve_comment':
            if ($_SESSION['admin_role'] === 'musicien') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $commentaireModel = new Commentaire($db);
                // Vérification pour rédacteur : seulement ses articles
                if ($_SESSION['admin_role'] === 'redacteur') {
                    if (!$commentaireModel->isCommentOnAuthorArticle($_GET['id'], $_SESSION['admin_id'])) {
                        die("Accès refusé : Vous ne pouvez modérer que les commentaires sur vos propres articles.");
                    }
                }
                $commentaireModel->updateStatus($_GET['id'], 'approved');
            }
            header('Location: index.php?action=comments');
            exit;

        case 'reject_comment':
            if ($_SESSION['admin_role'] === 'musicien') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $commentaireModel = new Commentaire($db);
                // Vérification pour rédacteur : seulement ses articles
                if ($_SESSION['admin_role'] === 'redacteur') {
                    if (!$commentaireModel->isCommentOnAuthorArticle($_GET['id'], $_SESSION['admin_id'])) {
                        die("Accès refusé : Vous ne pouvez modérer que les commentaires sur vos propres articles.");
                    }
                }
                $commentaireModel->updateStatus($_GET['id'], 'rejected');
            }
            header('Location: index.php?action=comments');
            exit;

        case 'delete_notification':
            if (isset($_GET['id'])) {
                $notifModel = new Notification($db);
                $notifModel->delete($_GET['id']);
            }
            header('Location: index.php?action=notifications');
            exit;

        case 'mark_notification_read':
            if (isset($_GET['id'])) {
                $notifModel = new Notification($db);
                $notifModel->markAsRead($_GET['id']);
            }
            header('Location: index.php?action=notifications');
            exit;

        case 'mark_all_notifications_read':
            $notifModel = new Notification($db);
            $notifModel->markAllAsRead($_SESSION['admin_id']);
            header('Location: index.php?action=notifications');
            exit;

        case 'approve_product':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $produitModel = new Produit($db);
                $product = $produitModel->getProductById($_GET['id']);
                if ($product) {
                    $produitModel->update($product['id'], $product['name'], $product['description'], $product['price'], $product['stock'], $product['category'], $product['type'], $product['image_url'], $product['file_url'], 'approved');
                }
            }
            header('Location: index.php?action=moderation');
            exit;

        case 'reject_product':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $produitModel = new Produit($db);
                $product = $produitModel->getProductById($_GET['id']);
                if ($product) {
                    $produitModel->update($product['id'], $product['name'], $product['description'], $product['price'], $product['stock'], $product['category'], $product['type'], $product['image_url'], $product['file_url'], 'rejected');
                }
            }
            header('Location: index.php?action=moderation');
            exit;

        // US-39 : Validation demande de rôle (Visiteur -> Rédacteur/Musicien)
        case 'approve_role_request':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $roleRequestModel = new RoleRequest($db);
                // Change automatiquement le rôle de l'utilisateur
                $roleRequestModel->approve($_GET['id'], $_SESSION['admin_id']);
            }
            header('Location: index.php?action=role_requests');
            exit;

        // US-39 : Rejet demande de rôle
        case 'reject_role_request':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $roleRequestModel = new RoleRequest($db);
                $roleRequestModel->reject($_GET['id'], $_SESSION['admin_id']);
            }
            header('Location: index.php?action=role_requests');
            exit;
        case 'approve_mreq':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $mreq = new ModerationRequest($db);
                $mreq->approve($_GET['id'], $_SESSION['admin_id']);
            }
            header('Location: index.php?action=moderation_requests');
            exit;

        case 'reject_mreq':
            if ($_SESSION['admin_role'] !== 'admin') {
                die("Accès refusé.");
            }
            if (isset($_GET['id'])) {
                $mreq = new ModerationRequest($db);
                $mreq->reject($_GET['id'], $_SESSION['admin_id']);
            }
            header('Location: index.php?action=moderation_requests');
            exit;
    }
}

// --- TRAITEMENT DES ACTIONS AVEC REDIRECTION (AVANT LE HEADER) ---
// Suppression de tag
if ($action === 'tags' && isset($_GET['delete_id'])) {
    if ($_SESSION['admin_role'] !== 'admin') {
        die("Accès refusé.");
    }
    $tagModel = new Tag($db);
    $tagModel->delete($_GET['delete_id']);
    header('Location: index.php?action=tags');
    exit;
}

// --- TRAITEMENT POST edit_user (AVANT HEADER) ---
if ($action === 'edit_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['admin_role'] !== 'admin') {
        die("Accès refusé.");
    }
    $userModel = new User($db);
    if ($userModel->updateFromPost()) {
        header('Location: index.php?action=users');
        exit;
    }
}

// --- TRAITEMENT POST create_cours (AVANT HEADER) ---
if ($action === 'create_cours' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['admin_role'] === 'musicien') {
        die("Accès refusé.");
    }
    $coursModel = new Cours($db);
    if ($coursModel->createFromPost($_SESSION['admin_id'])) {
        header('Location: index.php?action=cours');
        exit;
    }
}

// --- TRAITEMENT POST edit_cours (AVANT HEADER) ---
if ($action === 'edit_cours' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['admin_role'] === 'musicien') {
        die("Accès refusé.");
    }
    $coursModel = new Cours($db);
    if ($coursModel->updateFromPost()) {
        header('Location: index.php?action=cours');
        exit;
    }
}

// --- TRAITEMENT POST create_article (AVANT HEADER) ---
if ($action === 'create_article' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = FileUpload::uploadImage('image', FileUpload::UPLOAD_DIR_IMG);
    $articleModel = new Article($db);
    if ($articleModel->createFromPost($_SESSION['admin_id'], $image_url)) {
        // US-28 : Gestion des tags
        $new_article_id = $db->lastInsertId();
        if (isset($_POST['tags']) && is_array($_POST['tags'])) {
            $tagModel = new Tag($db);
            $tagModel->setArticleTags($new_article_id, $_POST['tags']);
        }
        header('Location: index.php?action=articles');
        exit;
    }
}

// --- TRAITEMENT POST edit_article (AVANT HEADER) ---
if ($action === 'edit_article' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleModel = new Article($db);
    $id = $articleModel->getIdFromPost();
    
    if ($_SESSION['admin_role'] === 'redacteur') {
        $existingArticle = $articleModel->getArticleById($id);
        if ($existingArticle['author_id'] != $_SESSION['admin_id']) {
            die("Accès refusé : Vous ne pouvez modifier que vos propres articles.");
        }
    }
    
    $image_url = FileUpload::uploadImage('image', FileUpload::UPLOAD_DIR_ASSETS);
    // Si pas de nouvelle image, garder l'ancienne
    if ($image_url === null && isset($_POST['current_image'])) {
        $image_url = $_POST['current_image'];
    }
    
    if ($articleModel->updateFromPost($image_url)) {
        // US-28 : Gestion des tags
        $tagModel = new Tag($db);
        $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
        $tagModel->setArticleTags($id, $tags);
        
        header('Location: index.php?action=articles');
        exit;
    }
}

// --- TRAITEMENT POST create_produit (AVANT HEADER) ---
if ($action === 'create_produit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['admin_role'] === 'redacteur') {
        die("Accès refusé.");
    }
    
    $image_url = FileUpload::uploadImage('image', FileUpload::UPLOAD_DIR_IMG);
    $file_url = FileUpload::uploadFile('file', FileUpload::UPLOAD_DIR_IMG);
    
    $produitModel = new Produit($db);
    if ($produitModel->createFromPost($_SESSION['admin_id'], $image_url, $file_url)) {
        header('Location: index.php?action=produits');
        exit;
    }
}

// --- TRAITEMENT POST edit_produit (AVANT HEADER) ---
if ($action === 'edit_produit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['admin_role'] === 'redacteur') {
        die("Accès refusé.");
    }
    
    $produitModel = new Produit($db);
    $id = $produitModel->getIdFromPost();
    
    if ($_SESSION['admin_role'] === 'musicien') {
        $existingProduct = $produitModel->getProductById($id);
        if ($existingProduct['seller_id'] != $_SESSION['admin_id']) {
            die("Accès refusé : Vous ne pouvez modifier que vos propres produits.");
        }
    }
    
    $image_url = FileUpload::uploadImage('image', FileUpload::UPLOAD_DIR_ASSETS);
    // Si pas de nouvelle image, garder l'ancienne
    if ($image_url === null && isset($_POST['current_image'])) {
        $image_url = $_POST['current_image'];
    }
    
    $file_url = FileUpload::uploadFile('file', FileUpload::UPLOAD_DIR_ASSETS);
    // Si pas de nouveau fichier, garder l'ancien
    if ($file_url === null && isset($_POST['current_file'])) {
        $file_url = $_POST['current_file'];
    }
    
    if ($produitModel->updateFromPost($image_url, $file_url)) {
        header('Location: index.php?action=produits');
        exit;
    }
}

// ==========================================
// US-27 : EXPORT CSV (AVANT HEADER)
// ==========================================
if ($action === 'export_csv') {
    if ($_SESSION['admin_role'] !== 'admin') {
        die("Accès refusé.");
    }
    
    $type = isset($_GET['type']) ? $_GET['type'] : 'orders';
    $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01');
    $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d');
    
    // Utilisation du modèle Rapport (encapsulation des requêtes SQL)
    $rapportModel = new Rapport($db);
    
    // Headers pour le téléchargement
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="export_' . $type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($type === 'orders') {
        fputcsv($output, ['ID', 'Client', 'Email', 'Montant', 'Statut', 'Date'], ';');
        $data = $rapportModel->getOrdersForExport($date_start, $date_end);
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    } elseif ($type === 'users') {
        fputcsv($output, ['ID', 'Nom', 'Email', 'Rôle', 'Date inscription'], ';');
        $data = $rapportModel->getUsersForExport($date_start, $date_end);
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    } elseif ($type === 'products') {
        fputcsv($output, ['ID', 'Nom', 'Prix', 'Stock', 'Catégorie', 'Type', 'Statut'], ';');
        $data = $rapportModel->getProductsForExport();
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }
    
    fclose($output);
    exit;
}

// --- REDIRECTION moderation -> moderation_requests (AVANT HEADER) ---
if ($action === 'moderation') {
    header('Location: index.php?action=moderation_requests');
    exit;
}

// ==========================================
// US-27 : EXPORT PDF (AVANT HEADER)
// ==========================================
if ($action === 'export_pdf') {
    if ($_SESSION['admin_role'] !== 'admin') {
        die("Accès refusé.");
    }
    
    $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01');
    $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d');
    
    // Utilisation du modèle Rapport (encapsulation des requêtes SQL)
    $rapportModel = new Rapport($db);
    $orderStats = $rapportModel->getOrderStats($date_start, $date_end);
    $ordersDetail = $rapportModel->getOrdersDetailForPdf($date_start, $date_end);
    
    // Génération HTML pour impression/PDF
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Rapport OmniMusique - <?php echo $date_start; ?> au <?php echo $date_end; ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            h1 { color: #2e1065; border-bottom: 2px solid #2e1065; padding-bottom: 10px; }
            .stat-box { display: inline-block; padding: 20px; margin: 10px; background: #f5f3ff; border-radius: 8px; text-align: center; min-width: 150px; }
            .stat-number { font-size: 32px; font-weight: bold; color: #4c1d95; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #2e1065; color: white; }
            .print-btn { background: #4c1d95; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-bottom: 20px; }
            @media print { .print-btn { display: none; } }
        </style>
    </head>
    <body>
        <button class="print-btn" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
        
        <h1>📊 Rapport d'activité OmniMusique</h1>
        <p><strong>Période :</strong> <?php echo date('d/m/Y', strtotime($date_start)); ?> au <?php echo date('d/m/Y', strtotime($date_end)); ?></p>
        <p><strong>Généré le :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
        
        <h2>Résumé</h2>
        <div class="stat-box">
            <div class="stat-number"><?php echo $orderStats['total_orders']; ?></div>
            <div>Commandes</div>
        </div>
        <div class="stat-box">
            <div class="stat-number"><?php echo number_format($orderStats['total_revenue'], 2); ?> €</div>
            <div>Chiffre d'Affaires</div>
        </div>
        
        <h2>Détail des commandes</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Client</th><th>Montant</th><th>Statut</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($ordersDetail as $row): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username'] ?? 'Invité'); ?></td>
                    <td><?php echo number_format($row['total_amount'], 2); ?> €</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 40px; font-size: 12px; color: #666;">
            Ce rapport a été généré automatiquement par le système OmniMusique.
        </p>
    </body>
    </html>
    <?php
    exit;
}

// --- ZONE PROTÉGÉE ---
// Variables communes pour Twig
$twigVars = [
    'is_admin' => true,
    'admin_role' => $_SESSION['admin_role'],
    'admin_name' => $_SESSION['admin_name'],
    'admin_id' => $_SESSION['admin_id']
];

// ===========================================
// ROUTAGE PRINCIPAL - Rendu avec Twig
// ===========================================
switch ($action) {
    // US-08 : Dashboard avec supervision globale
    // US-33 : Stats de ventes pour les Musiciens
    case 'dashboard':
        $articleModel = new Article($db);
        $nbArticles = $articleModel->count();
        
        $coursModel = new Cours($db);
        $nbCours = $coursModel->count();

        $produitModel = new Produit($db);
        
        // US-33 : Affichage du CA pour les Musiciens Professionnels
        if ($_SESSION['admin_role'] === 'musicien') {
            // On compte seulement ses produits
            $allProducts = $produitModel->getProducts();
            $myProducts = array_filter($allProducts, function($p) {
                return $p['seller_id'] == $_SESSION['admin_id'];
            });
            $nbProduits = count($myProducts);
            
            // Calcul du CA
            $orderModel = new Order($db);
            $caTotal = $orderModel->getSalesBySeller($_SESSION['admin_id']);
        } else {
            $nbProduits = $produitModel->count();
            $caTotal = null;
        }
        
        echo render_template($twig, 'admin/dashboard.twig', array_merge($twigVars, [
            'nbArticles' => $nbArticles,
            'nbCours' => $nbCours,
            'nbProduits' => $nbProduits,
            'caTotal' => $caTotal,
            'breadcrumbs' => ['Dashboard' => 'index.php?action=dashboard']
        ]));
        break;

    // --- GESTION DES ARTICLES (US-06) ---
    case 'articles':
        if ($_SESSION['admin_role'] === 'musicien') {
            die("Accès refusé.");
        }
        $articleModel = new Article($db);
        // Admin voit tout (status = 'all')
        $liste_articles = $articleModel->getArticles(null, 'all');
        echo render_template($twig, 'admin/articles/list.twig', array_merge($twigVars, [
            'liste_articles' => $liste_articles,
            'breadcrumbs' => ['Articles' => 'index.php?action=articles']
        ]));
        break;

    case 'create_article':
        // Le traitement POST est fait avant le header
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = "Erreur lors de la création.";
        }
        // US-28 : Récupérer tous les tags pour le formulaire
        $tagModel = new Tag($db);
        $allTags = $tagModel->getAll();
        echo render_template($twig, 'admin/articles/form.twig', array_merge($twigVars, [
            'allTags' => $allTags,
            'error' => $error,
            'breadcrumbs' => [
                'Articles' => 'index.php?action=articles',
                'Créer' => ''
            ]
        ]));
        break;

    case 'edit_article':
        $articleModel = new Article($db);
        // Le traitement POST est fait avant le header
        if (isset($_GET['id'])) {
            $article = $articleModel->getArticleById($_GET['id']);
            
            // Vérification des droits
            if ($_SESSION['admin_role'] === 'redacteur' && $article['author_id'] != $_SESSION['admin_id']) {
                die("Accès refusé : Vous ne pouvez modifier que vos propres articles.");
            }

            // US-28 : Récupérer tous les tags et les tags de l'article
            $tagModel = new Tag($db);
            $allTags = $tagModel->getAll();
            $articleTagIds = $tagModel->getArticleTagIds($_GET['id']);

            echo render_template($twig, 'admin/articles/form.twig', array_merge($twigVars, [
                'article' => $article,
                'allTags' => $allTags,
                'articleTagIds' => $articleTagIds,
                'breadcrumbs' => [
                    'Articles' => 'index.php?action=articles',
                    'Modifier' => ''
                ]
            ]));
        }
        break;

    case 'produits':
        if ($_SESSION['admin_role'] === 'redacteur') {
            die("Accès refusé.");
        }
        $produitModel = new Produit($db);
        $liste_produits = $produitModel->getProducts();
        // Filtrer pour le musicien
        if ($_SESSION['admin_role'] === 'musicien') {
            $liste_produits = array_filter($liste_produits, function($p) {
                return $p['seller_id'] == $_SESSION['admin_id'];
            });
        }
        echo render_template($twig, 'admin/produits/list.twig', array_merge($twigVars, [
            'liste_produits' => $liste_produits,
            'breadcrumbs' => ['Produits' => 'index.php?action=produits']
        ]));
        break;

    case 'create_produit':
        if ($_SESSION['admin_role'] === 'redacteur') {
            die("Accès refusé.");
        }
        // Le traitement POST est fait avant le header
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = "Erreur lors de la création.";
        }
        echo render_template($twig, 'admin/produits/form.twig', array_merge($twigVars, [
            'error' => $error,
            'breadcrumbs' => [
                'Produits' => 'index.php?action=produits',
                'Créer' => ''
            ]
        ]));
        break;

    case 'edit_produit':
        if ($_SESSION['admin_role'] === 'redacteur') {
            die("Accès refusé.");
        }
        $produitModel = new Produit($db);
        // Le traitement POST est fait avant le header
        if (isset($_GET['id'])) {
            $produit = $produitModel->getProductById($_GET['id']);
            
            // Vérification Musicien
            if ($_SESSION['admin_role'] === 'musicien' && $produit['seller_id'] != $_SESSION['admin_id']) {
                die("Accès refusé : Vous ne pouvez modifier que vos propres produits.");
            }

            echo render_template($twig, 'admin/produits/form.twig', array_merge($twigVars, [
                'produit' => $produit,
                'breadcrumbs' => [
                    'Produits' => 'index.php?action=produits',
                    'Modifier' => ''
                ]
            ]));
        }
        break;

    // --- CONFIGURATIONS (US-09) ---
    case 'configurations':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $configModel = new Configuration($db);
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                $configModel->update($key, $value);
            }
            $success = "Configurations mises à jour.";
        }
        
        $configs = $configModel->getAll();
        echo render_template($twig, 'admin/configurations.twig', array_merge($twigVars, [
            'configs' => $configs,
            'success' => $success,
            'breadcrumbs' => ['Configurations' => 'index.php?action=configurations']
        ]));
        break;

    // --- COMMANDES (US-08) ---
    case 'orders':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $orderModel = new Order($db);
        $orders = $orderModel->getAllOrders();
        echo render_template($twig, 'admin/orders/list.twig', array_merge($twigVars, [
            'orders' => $orders,
            'breadcrumbs' => ['Commandes' => 'index.php?action=orders']
        ]));
        break;

    // --- GESTION DES TAGS (US-28) ---
    case 'tags':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $tagModel = new Tag($db);
        
        // Ajout
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Les données POST sont traitées dans la classe (encapsulation)
            $tagModel->createFromPost();
        }

        $tags = $tagModel->getAll();
        echo render_template($twig, 'admin/tags/list.twig', array_merge($twigVars, [
            'tags' => $tags,
            'breadcrumbs' => ['Tags' => 'index.php?action=tags']
        ]));
        break;

    // --- GESTION UTILISATEURS (US-26) ---
    case 'users':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $userModel = new User($db);
        $users = $userModel->getAllUsers();
        echo render_template($twig, 'admin/users/list.twig', array_merge($twigVars, [
            'users' => $users,
            'breadcrumbs' => ['Utilisateurs' => 'index.php?action=users']
        ]));
        break;

    case 'edit_user':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $userModel = new User($db);
        $error = null;
        
        // Le traitement POST est fait avant le header
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userModel->updateFromPost()) {
            $error = "Erreur lors de la mise à jour.";
        }
        
        if (isset($_GET['id'])) {
            $user = $userModel->getUserById($_GET['id']);
            echo render_template($twig, 'admin/users/form.twig', array_merge($twigVars, [
                'user' => $user,
                'error' => $error,
                'breadcrumbs' => [
                    'Utilisateurs' => 'index.php?action=users',
                    'Modifier' => ''
                ]
            ]));
        }
        break;

    // --- GESTION DES COURS (CRUD) ---
    case 'cours':
        if ($_SESSION['admin_role'] === 'musicien') {
            die("Accès refusé.");
        }
        $coursModel = new Cours($db);
        $liste_cours = $coursModel->getCourses();
        echo render_template($twig, 'admin/cours/list.twig', array_merge($twigVars, [
            'liste_cours' => $liste_cours,
            'breadcrumbs' => ['Cours' => 'index.php?action=cours']
        ]));
        break;

    case 'create_cours':
        if ($_SESSION['admin_role'] === 'musicien') {
            die("Accès refusé.");
        }
        // Le traitement POST est fait avant le header
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = "Erreur lors de la création.";
        }
        echo render_template($twig, 'admin/cours/form.twig', array_merge($twigVars, [
            'error' => $error,
            'breadcrumbs' => [
                'Cours' => 'index.php?action=cours',
                'Créer' => ''
            ]
        ]));
        break;

    case 'edit_cours':
        if ($_SESSION['admin_role'] === 'musicien') {
            die("Accès refusé.");
        }
        $coursModel = new Cours($db);
        // Le traitement POST est fait avant le header
        if (isset($_GET['id'])) {
            $cours_edit = $coursModel->getCourseById($_GET['id']);
            echo render_template($twig, 'admin/cours/form.twig', array_merge($twigVars, [
                'cours' => $cours_edit,
                'breadcrumbs' => [
                    'Cours' => 'index.php?action=cours',
                    'Modifier' => ''
                ]
            ]));
        }
        break;

    // --- GESTION DES COMMENTAIRES (US-24) ---
    case 'comments':
        if ($_SESSION['admin_role'] === 'musicien') {
            die("Accès refusé.");
        }
        $commentaireModel = new Commentaire($db);
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';
        
        // Rédacteur : seulement les commentaires sur ses articles
        if ($_SESSION['admin_role'] === 'redacteur') {
            $allComments = $commentaireModel->getCommentsByArticleAuthor($_SESSION['admin_id']);
        } else {
            $allComments = $commentaireModel->getAllComments();
        }
        
        // Filtrage par statut
        if ($filter !== 'all') {
            $comments = array_filter($allComments, function($c) use ($filter) {
                return $c['status'] === $filter;
            });
        } else {
            $comments = $allComments;
        }
        
        echo render_template($twig, 'admin/comments/list.twig', array_merge($twigVars, [
            'comments' => $comments,
            'filter' => $filter,
            'breadcrumbs' => ['Commentaires' => 'index.php?action=comments']
        ]));
        break;

    // --- NOTIFICATIONS (US-29) ---
    case 'notifications':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $notificationModel = new Notification($db);
        $notifications = $notificationModel->getByUser($_SESSION['admin_id']);
        echo render_template($twig, 'admin/notifications/list.twig', array_merge($twigVars, [
            'notifications' => $notifications,
            'breadcrumbs' => ['Notifications' => 'index.php?action=notifications']
        ]));
        break;

    // ==========================================
    // US-39 : GESTION DES DEMANDES DE RÔLE
    // ==========================================
    case 'role_requests':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $roleRequestModel = new RoleRequest($db);
        $pendingRequests = $roleRequestModel->getPendingRequests();
        $allRequests = $roleRequestModel->getAllRequests();
        echo render_template($twig, 'admin/role_requests/list.twig', array_merge($twigVars, [
            'pendingRequests' => $pendingRequests,
            'allRequests' => $allRequests,
            'breadcrumbs' => ['Demandes de rôle' => 'index.php?action=role_requests']
        ]));
        break;

    // ==========================================
    // GESTION DES DEMANDES DE MODÉRATION PRODUITS
    // ==========================================
    case 'moderation_requests':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $mreqModel = new ModerationRequest($db);
        $pendingMreqs = $mreqModel->getPendingRequests();
        $allMreqs = $mreqModel->getAllRequests();
        echo render_template($twig, 'admin/moderation_requests/list.twig', array_merge($twigVars, [
            'pendingMreqs' => $pendingMreqs,
            'allMreqs' => $allMreqs,
            'breadcrumbs' => ['Modération' => 'index.php?action=moderation_requests']
        ]));
        break;

    // ==========================================
    // US-30 : GESTION DES PERMISSIONS
    // ==========================================
    case 'permissions':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        $permissionModel = new Permission($db);
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permissionModel->updateFromMatrix($_POST);
            $success = "Permissions mises à jour avec succès.";
        }
        
        $permissionData = $permissionModel->getPermissionMatrix();
        echo render_template($twig, 'admin/permissions/list.twig', array_merge($twigVars, [
            'permissionData' => $permissionData,
            'success' => $success,
            'breadcrumbs' => ['Permissions' => 'index.php?action=permissions']
        ]));
        break;

    // ==========================================
    // US-27 : RAPPORTS ET STATISTIQUES
    // ==========================================
    case 'reports':
        if ($_SESSION['admin_role'] !== 'admin') {
            die("Accès refusé.");
        }
        
        // Récupération des filtres de date
        $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01');
        $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d');
        
        // Utilisation du modèle Rapport (encapsulation des requêtes SQL)
        $rapportModel = new Rapport($db);
        
        $stats = $rapportModel->getAllStats($date_start, $date_end);
        $salesByCategory = $rapportModel->getSalesByCategory($date_start, $date_end);
        $recentOrders = $rapportModel->getRecentOrders($date_start, $date_end);
        
        echo render_template($twig, 'admin/reports/index.twig', array_merge($twigVars, [
            'stats' => $stats,
            'salesByCategory' => $salesByCategory,
            'recentOrders' => $recentOrders,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'breadcrumbs' => ['Rapports' => 'index.php?action=reports']
        ]));
        break;

    default:
        // Redirection vers dashboard par défaut
        header('Location: index.php?action=dashboard');
        exit;
}
