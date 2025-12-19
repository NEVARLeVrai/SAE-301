-- ===========================================
-- SAE 301 - OmniMusique
-- Script de création de la base de données
-- Auteur : SOARES Daniels
-- ===========================================
-- 
-- Ce script crée toutes les tables nécessaires pour :
-- - Gestion des utilisateurs et rôles
-- - Blog et articles (US-02, US-06, US-07, US-22, US-25)
-- - Cours de musique (US-01, US-12, US-15)
-- - Boutique et produits (US-03, US-04, US-05)
-- - Commandes (US-03, US-19, US-20)
-- - Favoris et avis (US-16, US-21)
-- - Commentaires (US-24, US-35)
-- - Notifications et modération (US-10, US-29)
-- - Permissions et configuration (US-09, US-30, US-38)
-- - Demandes de rôle (US-39)
-- ===========================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS soares_sae301 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisation de la base de données
USE soares_sae301;

-- ===========================================
-- TABLES SPRINT 1 & 2
-- ===========================================

-- ===========================================
-- NOTES POUR DÉVELOPPEURS
-- - Ce script crée les tables nécessaires pour l'application OmniMusique.
-- - Pour garantir l'utilisation des transactions et des verrous row-level
--   (ex: `SELECT ... FOR UPDATE` dans `Order::create()`), assurez-vous
--   que les tables utilisent le moteur InnoDB. Certaines tables précisent
--   déjà ENGINE=InnoDB mais vous pouvez forcer InnoDB globalement si besoin.
-- - Les mots de passe des utilisateurs fournis ci-dessous sont hachés
--   (Bcrypt) : utilisez `tools/hash_password.php` pour générer de nouveaux
--   hashes si vous changez les mots de passe de test.
-- - La table `role_permissions` contient la matrice des permissions et
--   est utilisée par `include/authorization.php` pour charger les droits en session.
-- - Conseils de sécurité : stocker les fichiers sensibles (PDF/MP3) hors du
--   webroot et servir les téléchargements via un contrôleur qui vérifie
--   les permissions et la propriété avant d'appeler `readfile()`.
-- ===========================================

-- ===========================================
-- TABLE USERS - Gestion des utilisateurs
-- US-26 : CRUD utilisateurs (Admin)
-- Rôles : admin, redacteur, musicien, visiteur
-- ===========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, 
    role ENUM('admin', 'redacteur', 'musicien', 'visiteur', 'responsable_annonce') DEFAULT 'visiteur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- TABLE COURSES - Cours de musique gratuits
-- US-01, US-12, US-15, US-31
-- ===========================================
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    content TEXT, -- Contenu du cours ou lien vidéo
    author_id INT,
    level ENUM('debutant', 'intermediaire', 'avance') NOT NULL,
    instrument VARCHAR(50),
    category VARCHAR(50), -- Style ou catégorie pédagogique
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===========================================
-- TABLE ARTICLES - Blog et actualités musicales
-- US-02, US-06, US-07, US-13, US-22, US-25, US-36
-- status: published/draft | is_deleted: soft delete
-- ===========================================
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    category ENUM('Actualité', 'Technique', 'Interviews') NOT NULL,
    image_url VARCHAR(255),
    status ENUM('published', 'draft') DEFAULT 'published', -- US-25
    published_at DATETIME, -- US-22 Planification
    updated_at DATETIME, -- US-07 Historique modif
    is_deleted BOOLEAN DEFAULT FALSE, -- US-36 Suppression logique
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===========================================
-- TABLE PRODUCTS - Boutique (partitions et instruments)
-- US-03, US-04, US-05, US-10, US-32, US-34
-- type: partition_virtuelle/partition_physique/instrument
-- status: pending/approved/rejected (modération)
-- ===========================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(50), -- ex: Piano, Guitare, Accessoire
    type ENUM('partition_virtuelle', 'partition_physique', 'instrument') NOT NULL,
    image_url VARCHAR(255), -- Image principale
    file_url VARCHAR(255), -- US-05/US-32 Fichier à télécharger (PDF/MP3)
    seller_id INT, -- Pour les musiciens pro et visiteurs (US-04)
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved', -- US-10 Modération (pending pour visiteurs)
    is_deleted BOOLEAN DEFAULT FALSE, -- US-34 Suppression logique
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des images supplémentaires produits (US-04 - Limite 5 photos)
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ===========================================
-- TABLE ORDERS - Commandes clients
-- US-03, US-08, US-19, US-33
-- status: pending/paid/shipped/cancelled
-- ===========================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Détail des commandes
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Prix au moment de l'achat
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Table des favoris (US-16)
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    item_type ENUM('course', 'article') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des configurations (US-09)
CREATE TABLE IF NOT EXISTS configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des données de configuration par défaut
INSERT INTO configurations (setting_key, setting_value) VALUES 
('site_title', 'SAE 301 OmniMusique'),
('maintenance_mode', '0'),
('contact_email', 'admin@example.com'),
('payment_mode', 'test'), -- US-38 : Mode Test/Live
('stripe_enabled', '0'), -- US-38 : Activation Stripe
('stripe_key', ''), -- US-38 : Clé publique Stripe
('stripe_secret', ''), -- US-38 : Clé secrète Stripe
('paypal_enabled', '0'), -- US-38 : Activation PayPal
('paypal_key', ''), -- US-38 : Client ID PayPal
('paypal_secret', '') -- US-38 : Client Secret PayPal
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);


-- Table des commentaires articles (US-24, US-35)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- Modération
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des avis produits (US-21)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des notifications admin (US-29)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Destinataire (souvent admin)
    message TEXT NOT NULL,
    link VARCHAR(255), -- Lien vers l'élément concerné
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des tags (US-28)
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Table de liaison Articles-Tags (US-28)
CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Table des demandes de rôle (US-39)
CREATE TABLE IF NOT EXISTS role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('redacteur', 'musicien') NOT NULL,
    motivation TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME,
    processed_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Table des demandes de modération produits (US-10 adaptation)
CREATE TABLE IF NOT EXISTS moderation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    message TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    processed_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des permissions par rôle (US-30)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    permission VARCHAR(100) NOT NULL,
    is_allowed BOOLEAN DEFAULT TRUE,
    UNIQUE KEY unique_role_permission (role, permission)
);

-- Insertion des permissions par défaut (US-30)
INSERT INTO role_permissions (role, permission, is_allowed) VALUES
('admin', 'manage_users', TRUE),
('admin', 'manage_articles', TRUE),
('admin', 'manage_courses', TRUE),
('admin', 'manage_products', TRUE),
('admin', 'manage_orders', TRUE),
('admin', 'manage_configurations', TRUE),
('admin', 'moderate_content', TRUE),
('admin', 'view_reports', TRUE),
('admin', 'export_data', TRUE),
('admin', 'manage_annonces', TRUE),
('redacteur', 'manage_users', FALSE),
('redacteur', 'manage_articles', TRUE),
('redacteur', 'manage_courses', FALSE),
('redacteur', 'manage_products', FALSE),
('redacteur', 'manage_orders', FALSE),
('redacteur', 'manage_configurations', FALSE),
('redacteur', 'moderate_content', TRUE),
('redacteur', 'view_reports', FALSE),
('redacteur', 'export_data', FALSE),
('redacteur', 'manage_annonces', FALSE),
('musicien', 'manage_users', FALSE),
('musicien', 'manage_articles', FALSE),
('musicien', 'manage_courses', FALSE),
('musicien', 'manage_products', TRUE),
('musicien', 'manage_orders', FALSE),
('musicien', 'manage_configurations', FALSE),
('musicien', 'moderate_content', FALSE),
('musicien', 'view_reports', FALSE),
('musicien', 'export_data', FALSE),
('responsable_annonce', 'manage_users', FALSE),
('responsable_annonce', 'manage_articles', FALSE),
('responsable_annonce', 'manage_courses', FALSE),
('responsable_annonce', 'manage_products', FALSE),
('responsable_annonce', 'manage_orders', FALSE),
('responsable_annonce', 'manage_configurations', FALSE),
('responsable_annonce', 'moderate_content', FALSE),
('responsable_annonce', 'view_reports', FALSE),
('responsable_annonce', 'export_data', FALSE),
('responsable_annonce', 'manage_annonces', TRUE)
ON DUPLICATE KEY UPDATE is_allowed = VALUES(is_allowed);

-- ==========================================
-- DONNÉES DE TEST
-- ==========================================

-- Utilisateurs (Mots de passe à hacher en prod)
-- 1: Admin, 2: Rédacteur, 3: Musicien, 4: Visiteur 5. Responsable Annonce
INSERT INTO users (username, email, password, role) VALUES
('Admin', 'admin@omnimusique.com', '$2y$10$lGU5t5heQdxSVp53/lBROuyJqizS1y4LJfSE.gOog.a3RwlnUqvY6', 'admin'),
('Redacteur', 'redac@omnimusique.com', '$2y$10$dplxSldV1YJyQBYzL8G7K.ifDJQNxBzTete.yLtMjAM4oWAJpWYcS', 'redacteur'),
('Mozart', 'mozart@omnimusique.com', '$2y$10$ApTf.IUGiBprYAm.N2gQg.7ev3GbugmDNXIRZ9F7CaZfBe5lXqPrW', 'musicien'),
('RespAnnonce', 'resp.annonce@omnimusique.com', '$2y$10$rO48L3DzrF86ElwQ1UN/2..QLl3cVIpyk3ZT/1W9bXaGf4TTaaRHi', 'responsable_annonce'),
('Visiteur', 'visiteur@omnimusique.com', '$2y$10$tU3KqyKpi7nQx2T3agbEh.lrq1h5SIgXbvneDsX0wG6ZOxc3Cj.ae', 'visiteur');

-- Cours
INSERT INTO courses (title, description, author_id, level, instrument, category) VALUES
('Introduction au Piano', 'Apprenez les bases du piano et la position des mains.', 3, 'debutant', 'Piano', 'Classique'),
('Accords de Guitare', 'Les principaux accords majeurs et mineurs pour débuter.', 3, 'debutant', 'Guitare', 'Folk'),
('Improvisation Jazz', 'Techniques avancées d\'improvisation et gammes pentatoniques.', 3, 'avance', 'Saxophone', 'Jazz'),
('Batterie Rythmes Rock', 'Apprendre les rythmes binaires de base.', 3, 'intermediaire', 'Batterie', 'Rock');

-- Articles
INSERT INTO articles (title, content, author_id, category, image_url, status, published_at) VALUES
('Le retour du Vinyle', 'Pourquoi le vinyle revient à la mode ? Analyse du marché...', 2, 'Actualité', 'vinyle.jpg', 'published', NOW()),
('Comment changer ses cordes', 'Tutoriel complet pour changer ses cordes de guitare folk sans casser le sillet.', 2, 'Technique', 'cordes.jpg', 'published', NOW()),
('Interview de Hans Zimmer', 'Rencontre avec le compositeur de légende.', 2, 'Interviews', 'hans.jpg', 'published', NOW());

-- Produits
INSERT INTO products (name, description, price, stock, category, type, seller_id, status) VALUES
('Partition - La Lettre à Elise', 'Partition PDF complète pour piano solo.', 5.00, 100, 'Piano', 'partition_virtuelle', 3, 'approved'),
('Guitare Fender Stratocaster', 'Guitare électrique occasion bon état, année 2010.', 450.00, 1, 'Guitare', 'instrument', 3, 'approved'),
('Méthode de Violon Vol.1', 'Livre physique pour apprendre le violon.', 25.00, 10, 'Violon', 'partition_physique', 3, 'approved');

-- Tags (US-28)
INSERT INTO tags (name) VALUES
('Classique'),
('Jazz'),
('Rock'),
('Pop'),
('Débutant'),
('Intermédiaire'),
('Avancé'),
('Piano'),
('Guitare'),
('Batterie'),
('Théorie'),
('Pratique');

-- Association articles-tags (US-28)
INSERT INTO article_tags (article_id, tag_id) VALUES
(1, 1), -- Le retour du Vinyle -> Classique
(1, 4), -- Le retour du Vinyle -> Pop
(2, 9), -- Comment changer ses cordes -> Guitare
(2, 12), -- Comment changer ses cordes -> Pratique
(3, 1), -- Interview Hans Zimmer -> Classique
(3, 2); -- Interview Hans Zimmer -> Jazz