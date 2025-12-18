# SAE 301 - OmniMusique

## Description du projet
Site de partage et d'échange sur la musique proposant :
- Des cours de musique gratuits (classés par catégories et niveaux)
- Un blog avec actualités musicales
- Une boutique de partitions (physiques et PDF)
- Une marketplace d'instruments d'occasion

## Auteur
**SOARES Daniels**

---

## Installation

### Prérequis
- Serveur Apache avec PHP 7.4+
- MySQL 5.7+ / MariaDB
- Extension PDO activée
- **Twig 3.x** (inclus dans le dossier `vendor/`)

### Configuration
1. Importer le fichier `bdd/script_creation_bdd.sql` dans phpMyAdmin
    - Avant d'importer, vérifier que le script contient les entrées pour le rôle `visiteur` (tous `is_allowed = FALSE`) ou ajouter-les manuellement si nécessaire.
2. Modifier les identifiants de connexion dans `modeles/Database.php` si nécessaire :

```php
    private $host = "localhost:3306";
    private $db_name = "soares_sae301"; 
    private $username = "daniels_soares_sae301";
    private $password = "p9_82ve5J"; 
```

3. S'assurer que les dossiers `assets/` et `img/` existent et sont accessibles en écriture pour les uploads

---

## Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| **Administrateur** | admin@omnimusique.com | `admin123` |
| **Rédacteur** | redac@omnimusique.com | `redac123` |
| **Musicien Pro** | mozart@omnimusique.com | `mozart123` |
| **Responsable Annonce** | resp.annonce@omnimusique.com | `respannonce123` |
| **Visiteur** | visiteur@omnimusique.com | `visiteur123` |

---

## Structure du projet

Voici l'arborescence telle qu'elle existe dans le dépôt :

```
SAE-301/
├── index.php
├── README.md
├── composer.json
├── composer.lock
├── bdd/
│   └── script_creation_bdd.sql
├── controllers/
│   ├── admin/
│   │   └── index.php
│   └── visiteur/
│       └── index.php
├── include/
│   ├── twig.php
│   └── authorization.php
├── modeles/
│   ├── Article.php
│   ├── Avis.php
│   ├── Commentaire.php
│   ├── Configuration.php
│   ├── Cours.php
│   ├── Database.php
│   ├── Favori.php
│   ├── FileUpload.php
│   ├── ModerationRequest.php
│   ├── Notification.php
│   ├── Order.php
│   ├── Permission.php
│   ├── Produit.php
│   ├── Rapport.php
│   ├── RoleRequest.php
│   ├── Tag.php
│   ├── User.php
│   └── Validator.php
├── templates/
│   ├── base.twig
│   ├── index.twig
│   ├── admin/
│   │   ├── dashboard.twig
│   │   ├── login.twig
│   │   ├── configurations.twig
│   │   ├── articles/
│   │   ├── cours/
│   │   ├── produits/
│   │   ├── users/
│   │   ├── comments/
│   │   ├── orders/
│   │   ├── tags/
│   │   ├── notifications/
│   │   ├── reports/
│   │   ├── permissions/
│   │   ├── role_requests/
│   │   └── moderation_requests/
│   └── visiteur/
│       ├── accueil.twig
│       ├── contact.twig
│       ├── mentions_legales.twig
│       ├── articles/
│       ├── cours/
│       ├── produits/       # templates produits (list.twig, details.twig)
│       ├── orders/
│       ├── auth/
│       └── favorites/
├── vendor/
├── tools/
├── css/
│   └── style.css
├── assets/
│   └── system/
└── img/
```

---

## Fonctionnalités implémentées

### Sprint 1 - User Stories

| US | Description | Statut |
|----|-------------|--------|
| US-01 | Recherche et filtrage des cours | ✅ |
| US-02 | Blog avec articles et partage réseaux sociaux | ✅ |
| US-03 | Achat partitions/instruments avec panier | ✅ |
| US-06 | Rédaction d'articles (back-office) | ✅ |
| US-08 | Dashboard admin avec statistiques | ✅ |
| US-11 | Page d'accueil avec nouveautés | ✅ |
| US-12 | Filtrage avancé des cours (niveau, instrument) | ✅ |
| US-13 | Recherche d'articles | ✅ |
| US-14 | Navigation par catégories | ✅ |
| US-15 | Fiche détail cours | ✅ |
| US-16 | Système de favoris (BDD) | ✅ |
| US-17 | Ajout au panier depuis liste/fiche | ✅ |
| US-18 | Modification du panier | ✅ |
| US-23 | Upload fichiers multimédia (5Mo max) | ✅ |
| US-25 | Système de brouillons articles | ✅ |
| US-31 | Consultation cours avec fil d'Ariane | ✅ |

### Sprint 2 - User Stories

| US | Description | Statut |
|----|-------------|--------|
| US-04 | Vente instruments d'occasion (modération) | ✅ |
| US-05 | Vente compositions (Musicien Pro) | ✅ |
| US-07 | Gestion ses propres articles (Rédacteur) | ✅ |
| US-09 | Configuration globale du site | ✅ |
| US-10 | Modération des annonces produits | ✅ |
| US-19 | Historique des commandes | ✅ |
| US-20 | Email de confirmation commande | ✅ |
| US-21 | Système de notation/avis (étoiles 1-5) | ✅ |
| US-22 | Planification publication articles | ✅ |
| US-24 | Gestion commentaires articles | ✅ |
| US-26 | Gestion utilisateurs (CRUD) | ✅ |
| US-27 | Rapports statistiques + Export CSV/PDF | ✅ |
| US-28 | Gestion catégories et tags | ✅ |
| US-29 | Centre de notifications admin | ✅ |
| US-30 | Matrice des permissions par rôle | ✅ |
| US-32 | Téléchargement sécurisé produits numériques | ✅ |
| US-33 | Statistiques ventes (Musicien) | ✅ |
| US-34 | Gestion produits Musicien (soft delete) | ✅ |
| US-35 | Commentaires visiteur (modération) | ✅ |
| US-36 | Suppression articles (soft delete) | ✅ |
| US-37 | Modération absolue admin | ✅ |
| US-38 | Configuration moyens de paiement | ✅ |
| US-39 | Demande/validation de rôle contributeur | ✅ |

---

## Rôles et Permissions
Les permissions sont stockées en base dans la table `role_permissions` et contrôlées depuis des helpers centralisés (`include/authorization.php`). Les permissions sont chargées en session (`$_SESSION['admin_permissions']`) pour optimiser l'affichage conditionnel dans les templates.

### Visiteur
- Consultation des cours, articles, boutique
- Achat et historique commandes
- Commentaires sur articles
- Avis sur produits achetés
- Vente d'instruments d'occasion (avec modération)
- Gestion des favoris
- Demande de promotion (Rédacteur/Musicien)

### Rédacteur
- Accès au back-office
- Création/modification de SES articles uniquement
- Modération des commentaires sur SES articles uniquement
- Système de brouillons et planification

### Musicien Professionnel
- Accès au back-office
- Gestion de SES produits (partitions numériques)
- Statistiques de ventes personnelles
- Chiffre d'affaires

> Remarque : les contrôles d'accès sont désormais fondés sur la matrice de permissions en base — évitez les checks basés sur `admin_role === '...'` dans le code.

### Administrateur
- Accès complet à toutes les fonctionnalités
- Gestion des utilisateurs et rôles
- Configuration du site
- Rapports et exports (CSV/PDF)
- Matrice des permissions
- Modération globale
- Centre de notifications

---

## Sécurité et Validation

### Validation des données
Le projet utilise une classe `Validator` centralisée (`modeles/Validator.php`) pour valider toutes les données utilisateur :

- **Validation email** : Format, longueur (max 255 caractères)
- **Validation mot de passe** : Longueur minimale configurable (3 pour login, 6 pour inscription), max 72 caractères
- **Validation username** : 3-50 caractères, alphanumériques + underscore uniquement
- **Vérification doublons** : Email et username uniques en base de données

- **Validation types produits** : le serveur vérifie que le type de produit soumis (partition/instrument) correspond aux permissions du rôle (ex. `manage_annonces` ne peut créer que `instrument`, `manage_products` gère les partitions).
- **Contrôle d'appartenance** : un `musicien` ne peut modifier que ses propres produits (vérification côté serveur).

### Double vérification
- **Côté serveur** : Validation PHP obligatoire via la classe `Validator` (sécurité principale)
- **Côté client** : Attributs HTML5 `required` pour améliorer l'expérience utilisateur

Les formulaires de login et d'inscription utilisent cette validation centralisée pour garantir la sécurité et la cohérence des données.

---

## Technologies utilisées

| Technologie | Utilisation |
|-------------|-------------|
| **PHP 7.4+** | Backend (POO) |
| **Twig 3.x** | Moteur de templates |
| **MySQL/MariaDB** | Base de données |
| **PDO** | Connexion sécurisée BDD |
| **HTML5/CSS3** | Frontend (Grid, Flexbox) |
| **Bootstrap 5** | Framework CSS |
| **Bootstrap Icons** | Icônes |
| **Poppins** | Police Google Fonts |

---

## Architecture Twig

Le projet utilise **Twig** comme moteur de templates pour séparer la logique PHP de l'affichage HTML.

### Principe de fonctionnement

```
Contrôleur (PHP)          →    Template (Twig)         →    Page HTML
controllers/visiteur/          templates/visiteur/          Rendu final
index.php                      accueil.twig
```

### Template de base (`templates/base.twig`)
Contient le header, footer et la structure HTML commune. Les pages héritent de ce template :

```twig
{% extends 'base.twig' %}

{% block title %}Ma Page{% endblock %}

{% block content %}
    <h1>Contenu de la page</h1>
    {{ ma_variable }}
{% endblock %}
```

### Fonctions Twig personnalisées
- `asset('chemin')` : Génère le chemin vers les assets
- `is_logged_in()` : Vérifie si un utilisateur est connecté
- `is_admin_logged_in()` : Vérifie si un admin est connecté
- `user_role()` / `admin_role()` : Retourne le rôle de l'utilisateur

### Variables globales disponibles
- `session` : Toutes les variables de session
- `user_logged_in`, `user_name`, `user_id`, `user_role`
- `admin_logged_in`, `admin_name`, `admin_id`, `admin_role`
- `current_action` : L'action GET actuelle
- `breadcrumbs` : Fil d'Ariane

---