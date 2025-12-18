<?php
/**
 * ===========================================
 * Modèle Permission - Matrice des permissions
 * ===========================================
 * 
 * User Stories concernées :
 * - US-30 : Paramétrage des rôles et permissions
 * 
 * Hiérarchie par défaut : Admin > Rédacteur > Musicien > Visiteur
 * Matrice éditable permettant de personnaliser les droits.
 */
class Permission {
    private $conn;
    private $table_name = "role_permissions";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Récupérer toutes les permissions
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY role, permission";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les permissions par rôle
     */
    public function getByRole($role) {
        $query = "SELECT permission, is_allowed FROM " . $this->table_name . " WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[$row['permission']] = (bool) $row['is_allowed'];
        }
        return $permissions;
    }

    /**
     * Récupérer la matrice complète des permissions (pour l'admin)
     */
    public function getPermissionMatrix() {
        $query = "SELECT DISTINCT permission FROM " . $this->table_name . " ORDER BY permission";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $roles = ['admin', 'redacteur', 'musicien', 'responsable_annonce'];
        $matrix = [];

        foreach ($roles as $role) {
            $matrix[$role] = $this->getByRole($role);
        }

        return [
            'permissions' => $permissions,
            'roles' => $roles,
            'matrix' => $matrix
        ];
    }

    /**
     * Mettre à jour une permission
     */
    public function update($role, $permission, $is_allowed) {
        $query = "INSERT INTO " . $this->table_name . " (role, permission, is_allowed) 
                  VALUES (:role, :permission, :is_allowed)
                  ON DUPLICATE KEY UPDATE is_allowed = :is_allowed2";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':permission', $permission);
        $stmt->bindParam(':is_allowed', $is_allowed, PDO::PARAM_BOOL);
        $stmt->bindParam(':is_allowed2', $is_allowed, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    /**
     * Mettre à jour toutes les permissions d'un coup (depuis le formulaire)
     */
    public function updateFromMatrix($data) {
        $roles = ['admin', 'redacteur', 'musicien', 'responsable_annonce'];
        
        // Récupérer toutes les permissions existantes
        $allPerms = $this->getAll();
        $permissions = array_unique(array_column($allPerms, 'permission'));

        foreach ($roles as $role) {
            foreach ($permissions as $permission) {
                $key = $role . '_' . $permission;
                $is_allowed = isset($data[$key]) ? 1 : 0;
                $this->update($role, $permission, $is_allowed);
            }
        }
        return true;
    }

    /**
     * Vérifier si un rôle a une permission spécifique
     */
    public function hasPermission($role, $permission) {
        $query = "SELECT is_allowed FROM " . $this->table_name . " 
                  WHERE role = :role AND permission = :permission";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':permission', $permission);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (bool) $row['is_allowed'] : false;
    }

    /**
     * Liste des permissions disponibles avec descriptions
     */
    public static function getPermissionLabels() {
        return [
            'manage_users' => 'Gérer les utilisateurs',
            'manage_articles' => 'Gérer les articles',
            'manage_courses' => 'Gérer les cours',
            'manage_products' => 'Gérer les produits',
            'manage_orders' => 'Gérer les commandes',
            'manage_configurations' => 'Gérer les configurations',
            'moderate_content' => 'Modérer le contenu',
            'view_reports' => 'Voir les rapports',
            'export_data' => 'Exporter les données',
            'manage_annonces' => 'Gérer les annonces'
        ];
    }
}
?>
